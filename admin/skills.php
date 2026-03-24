<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();

app_require_csrf_post();
$pageTitle = 'Yetenekler Yönetimi';

$message = '';
$messageType = '';

// Silme işlemi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Yetenek silindi!';
        $messageType = 'success';
    }
}

// Durum değiştirme
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("UPDATE skills SET is_active = NOT is_active WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Durum güncellendi!';
        $messageType = 'success';
    }
}

// Yeni ekleme veya güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $icon = sanitize($_POST['icon']);
    $tags = sanitize($_POST['tags']);
    $display_order = intval($_POST['display_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Güncelleme
        $stmt = $pdo->prepare("UPDATE skills SET title = ?, description = ?, icon = ?, tags = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$title, $description, $icon, $tags, $display_order, $is_active, $_POST['id']]);
        $message = 'Yetenek güncellendi!';
    } else {
        // Yeni ekleme
        $stmt = $pdo->prepare("INSERT INTO skills (title, description, icon, tags, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon, $tags, $display_order, $is_active]);
        $message = 'Yeni yetenek eklendi!';
    }
    $messageType = 'success';
}

// Düzenleme için veri çek
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
}

// Tüm yetenekleri çek
$skills = $pdo->query("SELECT * FROM skills ORDER BY display_order ASC, id ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Yetenekler Yönetimi</h1>
    <p>Yeteneklerini buradan yönetebilirsin.</p>
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
                    <label>Başlık</label>
                    <input type="text" name="title" class="form-control" placeholder="Frontend Development" value="<?php echo htmlspecialchars($editData['title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="HTML5, CSS3, JavaScript ile modern arayüzler..." required><?php echo htmlspecialchars($editData['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>İkon (Font Awesome)</label>
                    <input type="text" name="icon" class="form-control" placeholder="fas fa-code" value="<?php echo htmlspecialchars($editData['icon'] ?? ''); ?>" required>
                    <small style="color: #6b7280; font-size: 12px;">
                        <a href="https://fontawesome.com/icons" target="_blank" style="color: #667eea;">Font Awesome</a> sitesinden ikon sınıfını kopyala
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Etiketler (virgülle ayır)</label>
                    <input type="text" name="tags" class="form-control" placeholder="HTML,CSS,JavaScript,React" value="<?php echo htmlspecialchars($editData['tags'] ?? ''); ?>">
                    <small style="color: #6b7280; font-size: 12px;">Örnek: HTML,CSS,JavaScript,React</small>
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
                <a href="skills.php" class="btn btn-outline" style="width: 100%; margin-top: 8px;">
                    <i class="fas fa-times"></i> İptal
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Liste -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Yetenekler (<?php echo count($skills); ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($skills)): ?>
            <p class="empty-state">Henüz yetenek eklenmedi.</p>
            <?php else: ?>
            <div style="display: grid; gap: 16px;">
                <?php foreach ($skills as $skill): ?>
                <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 12px; display: flex; gap: 16px; align-items: start;">
                    <div style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="<?php echo htmlspecialchars($skill['icon']); ?>" style="font-size: 28px; color: white;"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <h4 style="font-size: 18px; margin-bottom: 4px;"><?php echo htmlspecialchars($skill['title']); ?></h4>
                            <div style="display: flex; gap: 4px;">
                                <a href="?action=edit&id=<?php echo $skill['id']; ?>" class="btn btn-sm btn-outline" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?action=toggle&id=<?php echo $skill['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-outline" title="Durumu Değiştir">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="?action=delete&id=<?php echo $skill['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-danger" title="Sil" onclick="return confirm('Bu yeteneği silmek istediğinize emin misiniz?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <p style="color: var(--text-secondary); margin-bottom: 12px; line-height: 1.6;">
                            <?php echo htmlspecialchars($skill['description']); ?>
                        </p>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                            <?php 
                            $tags = explode(',', $skill['tags']);
                            foreach ($tags as $tag): 
                                if (trim($tag)):
                            ?>
                            <span style="padding: 4px 12px; background: white; border-radius: 50px; font-size: 12px; font-weight: 500;">
                                <?php echo htmlspecialchars(trim($tag)); ?>
                            </span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        <div style="display: flex; gap: 16px; font-size: 13px; color: var(--text-secondary);">
                            <span><i class="fas fa-sort-numeric-up"></i> Sıra: <?php echo $skill['display_order']; ?></span>
                            <?php if ($skill['is_active']): ?>
                            <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Aktif</span>
                            <?php else: ?>
                            <span style="color: #ef4444;"><i class="fas fa-times-circle"></i> Pasif</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
