<?php
if (!function_exists('app_csrf_token')) {
    require_once __DIR__ . '/../../../lib/security.php';
}
if (!isLoggedIn()) {
    redirect('../login.php');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(app_csrf_token()); ?>">
    <title><?php echo $pageTitle ?? 'Jarvis Control'; ?> - Kesicioğlu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-dark-mode">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Kesicioğlu</h2>
                <span class="admin-badge">Jarvis Edition</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="index.php" class="nav-item active" style="background: linear-gradient(135deg, #22d3ee 0%, #3b82f6 100%);">
                    <i class="fas fa-microchip"></i>
                    <span>Jarvis Control</span>
                </a>
                <a href="../settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Site Ayarları</span>
                </a>
                <a href="../navigation.php" class="nav-item">
                    <i class="fas fa-bars"></i>
                    <span>Menü Yönetimi</span>
                </a>
                <a href="../social.php" class="nav-item">
                    <i class="fas fa-share-alt"></i>
                    <span>Sosyal Medya</span>
                </a>
                <a href="../skills.php" class="nav-item">
                    <i class="fas fa-lightbulb"></i>
                    <span>Yetenekler</span>
                </a>
                <a href="../projects.php" class="nav-item">
                    <i class="fas fa-folder"></i>
                    <span>Projeler</span>
                </a>
                <a href="../messages.php" class="nav-item">
                    <i class="fas fa-envelope"></i>
                    <span>Mesajlar</span>
                </a>
                <a href="../email-settings.php" class="nav-item">
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
                <a href="../logout.php" class="btn-logout">
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
                    <a href="../index.php" target="_blank" class="btn btn-sm btn-outline">
                        <i class="fas fa-external-link-alt"></i>
                        Siteyi Görüntüle
                    </a>
                </div>
            </header>
            
            <div class="content-wrapper">
