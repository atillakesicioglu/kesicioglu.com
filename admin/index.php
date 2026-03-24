<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();
$pageTitle = 'Dashboard';

// İstatistikleri çek
$stats = [
    'projects' => $pdo->query("SELECT COUNT(*) FROM projects WHERE is_active = 1")->fetchColumn(),
    'skills' => $pdo->query("SELECT COUNT(*) FROM skills WHERE is_active = 1")->fetchColumn(),
    'messages' => $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn(),
    'unread_messages' => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn(),
];

// Son mesajlar
$recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Hoş geldin, <?php echo $_SESSION['admin_username']; ?>! İşte sitenin genel durumu.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-folder"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $stats['projects']; ?></h3>
            <p>Aktif Proje</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $stats['skills']; ?></h3>
            <p>Yetenek</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $stats['messages']; ?></h3>
            <p>Toplam Mesaj</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-bell"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $stats['unread_messages']; ?></h3>
            <p>Okunmamış Mesaj</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-envelope"></i> Son Mesajlar</h3>
            <a href="messages.php" class="btn btn-sm btn-primary">Tümünü Gör</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentMessages)): ?>
                <p class="empty-state">Henüz mesaj yok.</p>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach ($recentMessages as $message): ?>
                    <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                        <div class="message-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                <span class="message-time">
                                    <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                                </span>
                            </div>
                            <p class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></p>
                            <p class="message-preview"><?php echo mb_substr(htmlspecialchars($message['message']), 0, 100); ?>...</p>
                        </div>
                        <?php if (!$message['is_read']): ?>
                        <span class="unread-badge">Yeni</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Hızlı İşlemler</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="projects.php?action=add" class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Yeni Proje Ekle</span>
                </a>
                <a href="skills.php?action=add" class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Yeni Yetenek Ekle</span>
                </a>
                <a href="settings.php" class="quick-action-btn">
                    <i class="fas fa-cog"></i>
                    <span>Site Ayarları</span>
                </a>
                <a href="social.php" class="quick-action-btn">
                    <i class="fas fa-share-alt"></i>
                    <span>Sosyal Medya</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
