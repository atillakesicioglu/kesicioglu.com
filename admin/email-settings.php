<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();

app_require_csrf_post();
$pageTitle = 'E-posta Ayarları';

$message = '';
$messageType = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        updateSetting('email_notifications', isset($_POST['email_notifications']) ? '1' : '0');
        updateSetting('notification_email', sanitize($_POST['notification_email']));
        
        $message = 'E-posta ayarları kaydedildi!';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Bir hata oluştu: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Mevcut ayarları çek
$emailNotifications = getSetting('email_notifications', '1');
$notificationEmail = getSetting('notification_email', '');

include 'includes/header.php';
?>

<div class="page-header">
    <h1>E-posta Ayarları</h1>
    <p>İletişim formundan gelen mesajların e-posta bildirimi ayarları.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo $message; ?>
</div>
<?php endif; ?>

<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-cog"></i> E-posta Bildirimleri</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php echo app_csrf_field(); ?>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 16px; background: var(--bg-tertiary); border-radius: 12px;">
                    <input type="checkbox" name="email_notifications" <?php echo $emailNotifications == '1' ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                    <div>
                        <strong style="font-size: 16px;">E-posta Bildirimlerini Aç</strong>
                        <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-secondary); font-weight: 400;">
                            İletişim formundan mesaj geldiğinde e-posta ile bildirim al
                        </p>
                    </div>
                </label>
            </div>
            
            <div class="form-group">
                <label>Bildirim Gönderilecek E-posta Adresi</label>
                <input type="email" name="notification_email" class="form-control" placeholder="ornek@email.com" value="<?php echo htmlspecialchars($notificationEmail); ?>" required>
                <small style="color: #6b7280; font-size: 12px;">
                    İletişim formundan gelen mesajlar bu e-posta adresine gönderilecek.
                </small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Ayarları Kaydet
            </button>
        </form>
    </div>
</div>

<div class="dashboard-card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> E-posta Nasıl Çalışır?</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; gap: 16px;">
            <div style="padding: 16px; background: #dbeafe; border-left: 4px solid #3b82f6; border-radius: 8px;">
                <h4 style="font-size: 14px; margin-bottom: 8px; color: #1e40af;">
                    <i class="fas fa-check-circle"></i> 1. E-posta Bildirimlerini Aktif Et
                </h4>
                <p style="font-size: 13px; color: #1e3a8a; margin: 0;">
                    Yukarıdaki kutucuğu işaretleyerek e-posta bildirimlerini aktif et.
                </p>
            </div>
            
            <div style="padding: 16px; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 8px;">
                <h4 style="font-size: 14px; margin-bottom: 8px; color: #065f46;">
                    <i class="fas fa-envelope"></i> 2. E-posta Adresini Gir
                </h4>
                <p style="font-size: 13px; color: #064e3b; margin: 0;">
                    Mesaj bildirimlerinin gönderileceği e-posta adresini gir.
                </p>
            </div>
            
            <div style="padding: 16px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                <h4 style="font-size: 14px; margin-bottom: 8px; color: #92400e;">
                    <i class="fas fa-bell"></i> 3. Otomatik Bildirim Al
                </h4>
                <p style="font-size: 13px; color: #78350f; margin: 0;">
                    İletişim formundan her yeni mesaj geldiğinde otomatik olarak e-posta alacaksın.
                </p>
            </div>
        </div>
        
        <div class="alert alert-info" style="margin-top: 24px;">
            <i class="fas fa-lightbulb"></i>
            <div>
                <strong>Not:</strong> E-posta gönderimi için sunucunuzda PHP mail() fonksiyonu veya SMTP yapılandırması gereklidir.
                cPanel üzerinden e-posta hesabı oluşturarak SMTP ayarlarını yapabilirsiniz.
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
