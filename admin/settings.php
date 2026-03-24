<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();

app_require_csrf_post();
$pageTitle = 'Site Ayarları';

$message = '';
$messageType = '';

// Allowed setting keys that may be written via the settings form
const SETTINGS_ALLOWLIST = [
    'site_title', 'site_subtitle', 'footer_text',
    'hero_title', 'hero_subtitle', 'hero_description', 'hero_image',
    'about_text_1', 'about_text_2',
    'stat_projects', 'stat_experience', 'stat_clients',
    'primary_color', 'secondary_color', 'accent_color',
    'contact_email', 'contact_phone', 'contact_location',
    'email_notifications', 'notification_email',
];

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST as $key => $value) {
            if (in_array($key, SETTINGS_ALLOWLIST, true)) {
                updateSetting($key, $value);
            }
        }
        
        // Hero image upload
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
            $uploadDir = '../uploads/';
            if (!file_exists($uploadDir)) {
                app_ensure_dir($uploadDir, 0755);
            }

            $origName = $_FILES['hero_image']['name'] ?? '';
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!app_is_allowed_image_ext($ext)) {
                throw new RuntimeException('Geçersiz görsel formatı.');
            }

            $fileName = time() . '_' . app_safe_filename($origName);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetPath)) {
                updateSetting('hero_image', 'uploads/' . $fileName);
            }
        }
        
        $message = 'Ayarlar başarıyla kaydedildi!';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Bir hata oluştu: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Tüm ayarları çek
$settings = [];
$stmt = $pdo->query("SELECT * FROM site_settings ORDER BY category, setting_key");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row;
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Site Ayarları</h1>
    <p>Sitenin genel ayarlarını buradan düzenleyebilirsin.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo $message; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <?php echo app_csrf_field(); ?>
    <!-- Hero Section -->
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-home"></i> Ana Sayfa (Hero)</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Ana Başlık</label>
                <input type="text" name="hero_title" class="form-control" value="<?php echo htmlspecialchars($settings['hero_title']['setting_value'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Alt Başlık</label>
                <input type="text" name="hero_subtitle" class="form-control" value="<?php echo htmlspecialchars($settings['hero_subtitle']['setting_value'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Açıklama</label>
                <textarea name="hero_description" class="form-control" rows="4" required><?php echo htmlspecialchars($settings['hero_description']['setting_value'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Profil Resmi</label>
                <input type="file" name="hero_image" class="form-control" accept="image/*">
                <?php if (!empty($settings['hero_image']['setting_value'])): ?>
                <img src="../<?php echo $settings['hero_image']['setting_value']; ?>" alt="Hero Image" style="max-width: 200px; margin-top: 12px; border-radius: 12px;">
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- About Section -->
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Hakkımda</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Hakkımda Metni 1</label>
                <textarea name="about_text_1" class="form-control" rows="3" required><?php echo htmlspecialchars($settings['about_text_1']['setting_value'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Hakkımda Metni 2</label>
                <textarea name="about_text_2" class="form-control" rows="3" required><?php echo htmlspecialchars($settings['about_text_2']['setting_value'] ?? ''); ?></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label>Tamamlanan Proje Sayısı</label>
                    <input type="number" name="stat_projects" class="form-control" value="<?php echo $settings['stat_projects']['setting_value'] ?? 0; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Yıllık Deneyim</label>
                    <input type="number" name="stat_experience" class="form-control" value="<?php echo $settings['stat_experience']['setting_value'] ?? 0; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Mutlu Müşteri Sayısı</label>
                    <input type="number" name="stat_clients" class="form-control" value="<?php echo $settings['stat_clients']['setting_value'] ?? 0; ?>" min="0" required>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Design Colors -->
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-palette"></i> Renk Ayarları</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label>Birincil Renk (Primary)</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="color" name="primary_color" class="form-control" value="<?php echo $settings['primary_color']['setting_value'] ?? '#667eea'; ?>" required style="width: 80px; height: 50px; cursor: pointer; padding: 4px;">
                        <input type="text" value="<?php echo $settings['primary_color']['setting_value'] ?? '#667eea'; ?>" readonly class="form-control" style="flex: 1; background: <?php echo $settings['primary_color']['setting_value'] ?? '#667eea'; ?>; color: white; font-weight: 600;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>İkincil Renk (Secondary)</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="color" name="secondary_color" class="form-control" value="<?php echo $settings['secondary_color']['setting_value'] ?? '#764ba2'; ?>" required style="width: 80px; height: 50px; cursor: pointer; padding: 4px;">
                        <input type="text" value="<?php echo $settings['secondary_color']['setting_value'] ?? '#764ba2'; ?>" readonly class="form-control" style="flex: 1; background: <?php echo $settings['secondary_color']['setting_value'] ?? '#764ba2'; ?>; color: white; font-weight: 600;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Vurgu Rengi (Accent)</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="color" name="accent_color" class="form-control" value="<?php echo $settings['accent_color']['setting_value'] ?? '#f093fb'; ?>" required style="width: 80px; height: 50px; cursor: pointer; padding: 4px;">
                        <input type="text" value="<?php echo $settings['accent_color']['setting_value'] ?? '#f093fb'; ?>" readonly class="form-control" style="flex: 1; background: <?php echo $settings['accent_color']['setting_value'] ?? '#f093fb'; ?>; color: white; font-weight: 600;">
                    </div>
                </div>
            </div>
            
            <script>
            // Renk değişimini text inputa yansıt
            document.querySelectorAll('input[type="color"]').forEach(colorInput => {
                colorInput.addEventListener('input', function() {
                    const textInput = this.parentElement.querySelector('input[type="text"]');
                    textInput.value = this.value;
                    textInput.style.background = this.value;
                });
            });
            </script>
        </div>
    </div>
    
    <!-- Contact Info -->
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-address-card"></i> İletişim Bilgileri</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>E-posta</label>
                <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email']['setting_value'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Telefon</label>
                <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone']['setting_value'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Konum</label>
                <input type="text" name="contact_location" class="form-control" value="<?php echo htmlspecialchars($settings['contact_location']['setting_value'] ?? ''); ?>" required>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Footer</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Footer Metni</label>
                <input type="text" name="footer_text" class="form-control" value="<?php echo htmlspecialchars($settings['footer_text']['setting_value'] ?? ''); ?>" required>
            </div>
        </div>
    </div>
    
    <button type="submit" name="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Ayarları Kaydet
    </button>
</form>

<?php include 'includes/footer.php'; ?>
