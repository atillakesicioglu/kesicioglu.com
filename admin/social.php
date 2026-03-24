<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();

app_require_csrf_post();
$pageTitle = 'Sosyal Medya Yönetimi';

$message = '';
$messageType = '';

// Silme işlemi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("DELETE FROM social_links WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Sosyal medya hesabı silindi!';
        $messageType = 'success';
    }
}

// Durum değiştirme
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("UPDATE social_links SET is_active = NOT is_active WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Durum güncellendi!';
        $messageType = 'success';
    }
}

// Yeni ekleme veya güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platform = sanitize($_POST['platform']);
    $icon = sanitize($_POST['icon']);
    $url = sanitize($_POST['url']);
    $display_order = intval($_POST['display_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Güncelleme
        $stmt = $pdo->prepare("UPDATE social_links SET platform = ?, icon = ?, url = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$platform, $icon, $url, $display_order, $is_active, $_POST['id']]);
        $message = 'Sosyal medya hesabı güncellendi!';
    } else {
        // Yeni ekleme
        $stmt = $pdo->prepare("INSERT INTO social_links (platform, icon, url, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$platform, $icon, $url, $display_order, $is_active]);
        $message = 'Yeni sosyal medya hesabı eklendi!';
    }
    $messageType = 'success';
}

// Düzenleme için veri çek
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM social_links WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
}

// Tüm sosyal medya hesaplarını çek
$socialLinks = $pdo->query("SELECT * FROM social_links ORDER BY display_order ASC, id ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Sosyal Medya Yönetimi</h1>
    <p>Sosyal medya hesaplarını buradan yönetebilirsin.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo $message; ?>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle"></i> <?php echo $editData ? 'Düzenle' : 'Yeni Ekle'; ?></h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php echo app_csrf_field(); ?>
                <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Platform Adı</label>
                    <input type="text" name="platform" class="form-control" placeholder="Instagram" value="<?php echo htmlspecialchars($editData['platform'] ?? ''); ?>" required>
                    <small style="color: #6b7280; font-size: 12px;">Örnek: Instagram, GitHub, LinkedIn</small>
                </div>
                
                <div class="form-group">
                    <label>İkon (Font Awesome)</label>
                    <input type="text" name="icon" class="form-control" placeholder="fab fa-instagram" value="<?php echo htmlspecialchars($editData['icon'] ?? ''); ?>" required>
                    <small style="color: #6b7280; font-size: 12px;">
                        <a href="https://fontawesome.com/icons" target="_blank" style="color: #667eea;">Font Awesome</a> sitesinden ikon sınıfını kopyala
                    </small>
                </div>
                
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="url" class="form-control" placeholder="https://instagram.com/username" value="<?php echo htmlspecialchars($editData['url'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Sıra</label>
                    <input type="number" name="display_order" class="form-control" value="<?php echo $editData['display_order'] ?? 0; ?>" min="0" required>
                    <small style="color: #6b7280; font-size: 12px;">Küçük sayı üstte görünür</small>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_active" <?php echo (!$editData || $editData['is_active']) ? 'checked' : ''; ?>>
                        Aktif
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> <?php echo $editData ? 'Güncelle' : 'Ekle'; ?>
                </button>
                
                <?php if ($editData): ?>
                <a href="social.php" class="btn btn-outline" style="width: 100%; margin-top: 8px;">
                    <i class="fas fa-times"></i> İptal
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Liste -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Sosyal Medya Hesapları (<?php echo count($socialLinks); ?>)</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($socialLinks)): ?>
            <p class="empty-state">Henüz sosyal medya hesabı eklenmedi.</p>
            <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sıra</th>
                            <th>Platform</th>
                            <th>İkon</th>
                            <th>URL</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($socialLinks as $link): ?>
                        <tr>
                            <td><?php echo $link['display_order']; ?></td>
                            <td><strong><?php echo htmlspecialchars($link['platform']); ?></strong></td>
                            <td>
                                <i class="<?php echo htmlspecialchars($link['icon']); ?>" style="font-size: 20px; color: #667eea;"></i>
                            </td>
                            <td>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" style="color: #667eea; font-size: 13px;">
                                    <?php echo mb_substr($link['url'], 0, 40); ?>...
                                </a>
                            </td>
                            <td>
                                <?php if ($link['is_active']): ?>
                                <span style="padding: 4px 12px; background: #d1fae5; color: #065f46; border-radius: 50px; font-size: 12px; font-weight: 600;">
                                    Aktif
                                </span>
                                <?php else: ?>
                                <span style="padding: 4px 12px; background: #fee2e2; color: #991b1b; border-radius: 50px; font-size: 12px; font-weight: 600;">
                                    Pasif
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="?action=edit&id=<?php echo $link['id']; ?>" class="btn btn-sm btn-outline" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?action=toggle&id=<?php echo $link['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-outline" title="Durumu Değiştir">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <a href="?action=delete&id=<?php echo $link['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-danger" title="Sil" onclick="return confirm('Bu sosyal medya hesabını silmek istediğinize emin misiniz?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="alert alert-info" style="margin-top: 24px;">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>Popüler İkonlar:</strong><br>
        <div style="display: flex; gap: 16px; margin-top: 8px; flex-wrap: wrap;">
            <span><i class="fab fa-instagram"></i> fab fa-instagram</span>
            <span><i class="fab fa-github"></i> fab fa-github</span>
            <span><i class="fab fa-linkedin"></i> fab fa-linkedin</span>
            <span><i class="fab fa-twitter"></i> fab fa-twitter</span>
            <span><i class="fab fa-facebook"></i> fab fa-facebook</span>
            <span><i class="fab fa-youtube"></i> fab fa-youtube</span>
            <span><i class="fas fa-envelope"></i> fas fa-envelope</span>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
