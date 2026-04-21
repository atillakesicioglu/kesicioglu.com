<?php
if (!isLoggedIn()) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - Kesicioğlu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Kesicioğlu</h2>
                <span class="admin-badge">Admin</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="jarvis-panel/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'jarvis-panel') !== false ? 'active' : ''; ?>" style="border-left: 3px solid #38bdf8; background: rgba(56, 189, 248, 0.05);">
                    <i class="fas fa-microchip" style="color: #38bdf8;"></i>
                    <span style="color: #38bdf8; font-weight: bold;">Jarvis Control</span>
                </a>
                <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Site Ayarları</span>
                </a>
                <a href="migrations.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'migrations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i>
                    <span>DB Migration</span>
                </a>
                <a href="navigation.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'navigation.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bars"></i>
                    <span>Menü Yönetimi</span>
                </a>
                <a href="social.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'social.php' ? 'active' : ''; ?>">
                    <i class="fas fa-share-alt"></i>
                    <span>Sosyal Medya</span>
                </a>
                <a href="skills.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'skills.php' ? 'active' : ''; ?>">
                    <i class="fas fa-lightbulb"></i>
                    <span>Yetenekler</span>
                </a>
                <a href="projects.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>">
                    <i class="fas fa-folder"></i>
                    <span>Projeler</span>
                </a>
                <a href="messages.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Mesajlar</span>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
                    $unreadCount = $stmt->fetchColumn();
                    if ($unreadCount > 0):
                    ?>
                    <span class="badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="email-settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'email-settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-mail-bulk"></i>
                    <span>E-posta Ayarları</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <p class="user-name"><?php echo $_SESSION['admin_username']; ?></p>
                        <p class="user-role">Administrator</p>
                    </div>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <button class="mobile-toggle" id="mobile-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-right">
                    <button class="btn btn-sm btn-outline" id="admin-theme-toggle" style="margin-right: 12px;">
                        <i class="fas fa-moon"></i>
                    </button>
                    <a href="../index.php" target="_blank" class="btn btn-sm btn-outline">
                        <i class="fas fa-external-link-alt"></i>
                        Siteyi Görüntüle
                    </a>
                </div>
            </header>
            
            <div class="content-wrapper">
