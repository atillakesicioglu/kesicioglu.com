<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();

app_require_csrf_post();
$pageTitle = 'Menü Yönetimi';

$message = '';
$messageType = '';

// Silme işlemi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("DELETE FROM navigation_menu WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Menü öğesi silindi!';
        $messageType = 'success';
    }
}

// Durum değiştirme
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("UPDATE navigation_menu SET is_active = NOT is_active WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Durum güncellendi!';
        $messageType = 'success';
    }
}

// Yeni ekleme veya güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_text = sanitize($_POST['menu_text']);
    $menu_link = sanitize($_POST['menu_link']);
    $display_order = intval($_POST['display_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Güncelleme
        $stmt = $pdo->prepare("UPDATE navigation_menu SET menu_text = ?, menu_link = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$menu_text, $menu_link, $display_order, $is_active, $_POST['id']]);
        $message = 'Menü öğesi güncellendi!';
    } else {
        // Yeni ekleme
        $stmt = $pdo->prepare("INSERT INTO navigation_menu (menu_text, menu_link, display_order, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$menu_text, $menu_link, $display_order, $is_active]);
        $message = 'Yeni menü öğesi eklendi!';
    }
    $messageType = 'success';
}

// Düzenleme için veri çek
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM navigation_menu WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
}

// Tüm menü öğelerini çek
$menuItems = $pdo->query("SELECT * FROM navigation_menu ORDER BY display_order ASC, id ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Menü Yönetimi</h1>
    <p>Navigasyon menüsünü buradan yönetebilirsin.</p>
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
                    <label>Menü Metni</label>
                    <input type="text" name="menu_text" class="form-control" placeholder="Ana Sayfa" value="<?php echo htmlspecialchars($editData['menu_text'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Link</label>
                    <input type="text" name="menu_link" class="form-control" placeholder="#home" value="<?php echo htmlspecialchars($editData['menu_link'] ?? ''); ?>" required>
                    <small style="color: #6b7280; font-size: 12px;">
                        İç bağlantılar için: #home, #about<br>
                        Dış bağlantılar için: https://...
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Sıra</label>
                    <input type="number" name="display_order" class="form-control" value="<?php echo $editData['display_order'] ?? 0; ?>" min="0" required>
                    <small style="color: #6b7280; font-size: 12px;">Küçük sayı solda görünür</small>
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
                <a href="navigation.php" class="btn btn-outline" style="width: 100%; margin-top: 8px;">
                    <i class="fas fa-times"></i> İptal
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Liste -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Menü Öğeleri (<?php echo count($menuItems); ?>)</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($menuItems)): ?>
            <p class="empty-state">Henüz menü öğesi eklenmedi.</p>
            <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sıra</th>
                            <th>Menü Metni</th>
                            <th>Link</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menuItems as $item): ?>
                        <tr>
                            <td><?php echo $item['display_order']; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['menu_text']); ?></strong></td>
                            <td>
                                <code style="padding: 4px 8px; background: var(--bg-tertiary); border-radius: 4px; font-size: 12px;">
                                    <?php echo htmlspecialchars($item['menu_link']); ?>
                                </code>
                            </td>
                            <td>
                                <?php if ($item['is_active']): ?>
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
                                    <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?action=toggle&id=<?php echo $item['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-outline" title="Durumu Değiştir">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <a href="?action=delete&id=<?php echo $item['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-danger" title="Sil" onclick="return confirm('Bu menü öğesini silmek istediğinize emin misiniz?');">
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

<?php include 'includes/footer.php'; ?>
