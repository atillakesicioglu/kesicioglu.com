<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();

app_require_csrf_post();
$pageTitle = 'Mesajlar';

$message = '';
$messageType = '';

// Silme işlemi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Mesaj silindi!';
        $messageType = 'success';
    }
}

// Okundu işaretle
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Mesaj okundu olarak işaretlendi!';
        $messageType = 'success';
    }
}

// Tümünü okundu işaretle
if (isset($_GET['action']) && $_GET['action'] === 'read_all') {
    $pdo->exec("UPDATE contact_messages SET is_read = 1");
    $message = 'Tüm mesajlar okundu olarak işaretlendi!';
    $messageType = 'success';
}

// Tüm mesajları çek
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM contact_messages";
if ($filter === 'unread') {
    $query .= " WHERE is_read = 0";
} elseif ($filter === 'read') {
    $query .= " WHERE is_read = 1";
}
$query .= " ORDER BY created_at DESC";

$messages = $pdo->query($query)->fetchAll();
$unreadCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Mesajlar</h1>
    <p>İletişim formundan gelen mesajları görüntüle.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo $message; ?>
</div>
<?php endif; ?>

<div class="dashboard-card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <h3>
                <i class="fas fa-envelope"></i> Gelen Mesajlar (<?php echo count($messages); ?>)
                <?php if ($unreadCount > 0): ?>
                <span style="padding: 4px 12px; background: #ef4444; color: white; border-radius: 50px; font-size: 12px; margin-left: 8px;">
                    <?php echo $unreadCount; ?> yeni
                </span>
                <?php endif; ?>
            </h3>
            <div style="display: flex; gap: 12px; align-items: center;">
                <div style="display: flex; gap: 8px;">
                    <a href="?filter=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">
                        Tümü
                    </a>
                    <a href="?filter=unread" class="btn btn-sm <?php echo $filter === 'unread' ? 'btn-primary' : 'btn-outline'; ?>">
                        Okunmamış
                    </a>
                    <a href="?filter=read" class="btn btn-sm <?php echo $filter === 'read' ? 'btn-primary' : 'btn-outline'; ?>">
                        Okunmuş
                    </a>
                </div>
                <?php if ($unreadCount > 0): ?>
                <a href="?action=read_all" class="btn btn-sm btn-success" onclick="return confirm('Tüm mesajları okundu olarak işaretlemek istediğinize emin misiniz?');">
                    <i class="fas fa-check-double"></i> Tümünü Okundu İşaretle
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($messages)): ?>
        <p class="empty-state">
            <?php if ($filter === 'unread'): ?>
                Okunmamış mesaj yok.
            <?php elseif ($filter === 'read'): ?>
                Okunmuş mesaj yok.
            <?php else: ?>
                Henüz mesaj yok.
            <?php endif; ?>
        </p>
        <?php else: ?>
        <div style="display: flex; flex-direction: column;">
            <?php foreach ($messages as $msg): ?>
            <div style="padding: 24px; border-bottom: 1px solid var(--border-color); <?php echo !$msg['is_read'] ? 'background: #eff6ff;' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 16px; margin-bottom: 2px;">
                                    <?php echo htmlspecialchars($msg['name']); ?>
                                    <?php if (!$msg['is_read']): ?>
                                    <span style="padding: 2px 8px; background: #ef4444; color: white; border-radius: 50px; font-size: 10px; margin-left: 8px; font-weight: 600;">
                                        YENİ
                                    </span>
                                    <?php endif; ?>
                                </h4>
                                <p style="font-size: 13px; color: var(--text-secondary);">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($msg['email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 4px; flex-shrink: 0;">
                        <span style="font-size: 12px; color: var(--text-secondary); padding: 8px 12px; background: var(--bg-tertiary); border-radius: 8px;">
                            <i class="fas fa-clock"></i>
                            <?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?>
                        </span>
                        <?php if (!$msg['is_read']): ?>
                        <a href="?action=read&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline" title="Okundu İşaretle">
                            <i class="fas fa-check"></i>
                        </a>
                        <?php endif; ?>
                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="btn btn-sm btn-primary" title="Yanıtla">
                            <i class="fas fa-reply"></i>
                        </a>
                        <a href="?action=delete&id=<?php echo $msg['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-danger" title="Sil" onclick="return confirm('Bu mesajı silmek istediğinize emin misiniz?');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                
                <div style="padding-left: 60px;">
                    <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px; border-left: 4px solid #667eea;">
                        <h5 style="font-size: 14px; color: var(--text-primary); margin-bottom: 8px; font-weight: 600;">
                            Konu: <?php echo htmlspecialchars($msg['subject']); ?>
                        </h5>
                        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7; white-space: pre-wrap;">
                            <?php echo htmlspecialchars($msg['message']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
