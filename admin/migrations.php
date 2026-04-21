<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();
app_require_csrf_post();
$pageTitle = 'Veritabanı Migration';

$message = '';
$messageType = '';

/**
 * Execute SQL content as individual statements.
 * Keeps it simple for project-level migration files.
 */
function executeSqlStatements(PDO $pdo, string $sql): int
{
    $lines = preg_split("/\r\n|\n|\r/", $sql) ?: [];
    $cleanSql = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
            continue;
        }
        $cleanSql .= $line . "\n";
    }

    $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
    $executedCount = 0;

    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
        $executedCount++;
    }

    return $executedCount;
}

function ensureMigrationsTable(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schema_migrations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            migration_hash VARCHAR(64) NOT NULL,
            applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * @return array<int, array{name:string,path:string,hash:string}>
 */
function discoverMigrationFiles(string $projectRoot): array
{
    $found = [];
    $migrationsDir = $projectRoot . '/migrations';
    if (is_dir($migrationsDir)) {
        $files = glob($migrationsDir . '/*.sql') ?: [];
        sort($files);
        foreach ($files as $path) {
            $found[] = [
                'name' => 'migrations/' . basename($path),
                'path' => $path,
                'hash' => hash_file('sha256', $path) ?: '',
            ];
        }
    }

    $legacyUpdates = $projectRoot . '/database_updates.sql';
    if (is_file($legacyUpdates)) {
        $found[] = [
            'name' => 'database_updates.sql',
            'path' => $legacyUpdates,
            'hash' => hash_file('sha256', $legacyUpdates) ?: '',
        ];
    }

    return $found;
}

$projectRoot = dirname(__DIR__);
ensureMigrationsTable($pdo);
$migrationFiles = discoverMigrationFiles($projectRoot);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migrations'])) {
    try {
        $pdo->beginTransaction();

        $appliedStmt = $pdo->query("SELECT migration_name, migration_hash FROM schema_migrations");
        $appliedRows = $appliedStmt->fetchAll();
        $appliedByName = [];
        foreach ($appliedRows as $row) {
            $appliedByName[$row['migration_name']] = $row['migration_hash'];
        }

        $applyStmt = $pdo->prepare("INSERT INTO schema_migrations (migration_name, migration_hash) VALUES (?, ?)");
        $appliedCount = 0;

        foreach ($migrationFiles as $migration) {
            $name = $migration['name'];
            $hash = $migration['hash'];

            if (isset($appliedByName[$name])) {
                if ($appliedByName[$name] !== $hash) {
                    throw new RuntimeException('Migration dosyası değişmiş: ' . $name . '. Yeni bir migration dosyası oluştur.');
                }
                continue;
            }

            $content = file_get_contents($migration['path']);
            if ($content === false) {
                throw new RuntimeException('Migration dosyası okunamadı: ' . $name);
            }

            executeSqlStatements($pdo, $content);
            $applyStmt->execute([$name, $hash]);
            $appliedCount++;
        }

        $pdo->commit();
        $message = $appliedCount > 0
            ? $appliedCount . ' migration başarıyla uygulandı.'
            : 'Uygulanacak yeni migration bulunmuyor.';
        $messageType = 'success';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = 'Migration sırasında hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

$appliedStmt = $pdo->query("SELECT migration_name, migration_hash, applied_at FROM schema_migrations ORDER BY applied_at DESC");
$appliedMigrations = $appliedStmt->fetchAll();
$appliedMap = [];
foreach ($appliedMigrations as $item) {
    $appliedMap[$item['migration_name']] = $item;
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Veritabanı Migration</h1>
    <p>Tek tıkla bekleyen SQL migration dosyalarını uygular.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="dashboard-card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-database"></i> Migration Çalıştır</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php echo app_csrf_field(); ?>
            <button type="submit" name="run_migrations" class="btn btn-primary" onclick="return confirm('Bekleyen migration dosyaları uygulansın mı?');">
                <i class="fas fa-play"></i> Migration Güncelle
            </button>
        </form>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-list-check"></i> Migration Durumu</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($migrationFiles)): ?>
            <p class="empty-state">Migration dosyası bulunamadı.</p>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Dosya</th>
                        <th>Durum</th>
                        <th>Uygulanma Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($migrationFiles as $migration): ?>
                    <?php $applied = $appliedMap[$migration['name']] ?? null; ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($migration['name']); ?></code></td>
                        <td>
                            <?php if ($applied): ?>
                                <span style="padding: 4px 12px; background: #d1fae5; color: #065f46; border-radius: 50px; font-size: 12px; font-weight: 600;">Uygulandı</span>
                            <?php else: ?>
                                <span style="padding: 4px 12px; background: #fef3c7; color: #92400e; border-radius: 50px; font-size: 12px; font-weight: 600;">Bekliyor</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $applied ? htmlspecialchars($applied['applied_at']) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
