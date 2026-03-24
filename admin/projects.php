<?php
require_once __DIR__ . '/bootstrap.php';
admin_guard();

app_require_csrf_post();
$pageTitle = 'Projeler Yönetimi';

$message = '';
$messageType = '';

// Silme işlemi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $project = $stmt->fetch();
    
    if ($project) {
        // Web app ise klasörünü sil
        if ($project['project_type'] === 'webapp' && !empty($project['folder_path'])) {
            $folderPath = '../' . $project['folder_path'];
            if (is_dir($folderPath)) {
                deleteDirectory($folderPath);
            }
        }
        
        // Resmi sil
        if (!empty($project['image']) && file_exists('../' . $project['image'])) {
            unlink('../' . $project['image']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        if ($stmt->execute([$_GET['id']])) {
            $message = 'Proje silindi!';
            $messageType = 'success';
        }
    }
}

// Durum değiştirme
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    app_require_csrf_get();
    $stmt = $pdo->prepare("UPDATE projects SET is_active = NOT is_active WHERE id = ?");
    if ($stmt->execute([$_GET['id']])) {
        $message = 'Durum güncellendi!';
        $messageType = 'success';
    }
}

// Yeni ekleme veya güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_project'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $project_type = sanitize($_POST['project_type']);
    $category = sanitize($_POST['category']);
    $tags = sanitize($_POST['tags']);
    $display_order = intval($_POST['display_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image = '';
    $folder_path = '';
    $demo_url = '';
    $github_url = '';
    
    try {
        // Resim yükleme (her iki tip için)
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === 0) {
            $uploadDir = '../uploads/projects/';
            if (!file_exists($uploadDir)) {
                app_ensure_dir($uploadDir, 0755);
            }

            $origName = $_FILES['project_image']['name'] ?? '';
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!app_is_allowed_image_ext($ext)) {
                throw new RuntimeException('Geçersiz görsel formatı.');
            }

            $fileName = time() . '_' . app_safe_filename($origName);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['project_image']['tmp_name'], $targetPath)) {
                $image = 'uploads/projects/' . $fileName;
            }
        }
        
        // Web App için
        if ($project_type === 'webapp') {
            $external_url = sanitize($_POST['external_url'] ?? '');
            $folder_name = sanitize($_POST['folder_name'] ?? '');
            
            // Klasör adını temizle (sadece küçük harf, rakam ve tire)
            $folder_name = strtolower(preg_replace('/[^a-z0-9-]+/', '', $folder_name));
            
            // Harici URL varsa onu kullan
            if (!empty($external_url)) {
                $demo_url = $external_url;
            }
            
            // ZIP dosyası yükleniyorsa
            if (isset($_FILES['webapp_files']) && $_FILES['webapp_files']['error'] === 0 && !empty($folder_name)) {
                $appsDir = '../apps/';
                if (!file_exists($appsDir)) {
                    app_ensure_dir($appsDir, 0755);
                }

                $projectDir = $appsDir . $folder_name . '/';
                
                // Klasör varsa sil ve yeniden oluştur (güncelleme için)
                if (file_exists($projectDir)) {
                    deleteDirectory($projectDir);
                }
                
                app_ensure_dir($projectDir, 0755);

                // ZIP dosyasını güvenli şekilde aç
                app_extract_zip_safely($_FILES['webapp_files']['tmp_name'], $projectDir);
                
                // Tek bir klasör mü çıktı kontrol et ve taşı
                $files = scandir($projectDir);
                $files = array_diff($files, ['.', '..']);
                if (count($files) === 1 && is_dir($projectDir . '/' . reset($files))) {
                    $singleDir = $projectDir . '/' . reset($files);
                    foreach (scandir($singleDir) as $item) {
                        if ($item === '.' || $item === '..') continue;
                        rename($singleDir . '/' . $item, $projectDir . '/' . $item);
                    }
                    rmdir($singleDir);
                }
                
                $folder_path = 'apps/' . $folder_name;
            } elseif (!empty($folder_name) && isset($_POST['id'])) {
                // Güncelleme ama ZIP yok - mevcut folder_path'i koru
                $folder_path = 'apps/' . $folder_name;
            }
        } else {
            // Blog tipi için
            $demo_url = sanitize($_POST['demo_url'] ?? '');
            $github_url = sanitize($_POST['github_url'] ?? '');
        }
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Güncelleme
            $updateFields = "title = ?, description = ?, project_type = ?, category = ?, tags = ?, display_order = ?, is_active = ?";
            $params = [$title, $description, $project_type, $category, $tags, $display_order, $is_active];
            
            if ($image) {
                $updateFields .= ", image = ?";
                $params[] = $image;
            }
            if ($folder_path) {
                $updateFields .= ", folder_path = ?";
                $params[] = $folder_path;
            }
            if ($demo_url) {
                $updateFields .= ", demo_url = ?";
                $params[] = $demo_url;
            }
            if ($github_url) {
                $updateFields .= ", github_url = ?";
                $params[] = $github_url;
            }
            
            $params[] = $_POST['id'];
            
            $stmt = $pdo->prepare("UPDATE projects SET $updateFields WHERE id = ?");
            $stmt->execute($params);
            $message = 'Proje güncellendi!';
        } else {
            // Yeni ekleme
            $stmt = $pdo->prepare("INSERT INTO projects (title, description, project_type, category, image, tags, demo_url, github_url, folder_path, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $project_type, $category, $image, $tags, $demo_url, $github_url, $folder_path, $display_order, $is_active]);
            $message = 'Yeni proje eklendi!';
        }
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Bir hata oluştu: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Düzenleme için veri çek
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editData = $stmt->fetch();
}

// Tüm projeleri çek
$projects = $pdo->query("SELECT * FROM projects ORDER BY display_order ASC, id DESC")->fetchAll();

// Klasör silme fonksiyonu
function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Projeler Yönetimi</h1>
    <p>Projelerini buradan yönetebilirsin.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo $message; ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')): ?>
<!-- Proje Ekle/Düzenle Formu -->
<div class="dashboard-card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> <?php echo $editData ? 'Projeyi Düzenle' : 'Yeni Proje Ekle'; ?></h3>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?php echo app_csrf_field(); ?>
            <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Proje Tipi *</label>
                <select name="project_type" id="project_type" class="form-control" required onchange="toggleProjectTypeFields()">
                    <option value="">Seçiniz</option>
                    <option value="blog" <?php echo ($editData && $editData['project_type'] === 'blog') ? 'selected' : ''; ?>>Blog/Portfolio (Görsel + Açıklama)</option>
                    <option value="webapp" <?php echo ($editData && $editData['project_type'] === 'webapp') ? 'selected' : ''; ?>>Web App (Dosya Yükleme)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Proje Başlığı *</label>
                <input type="text" name="title" class="form-control" placeholder="E-Ticaret Platformu" value="<?php echo htmlspecialchars($editData['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Açıklama *</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Proje hakkında detaylı açıklama..." required><?php echo htmlspecialchars($editData['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Kategori</label>
                <select name="category" class="form-control">
                    <option value="">Seçiniz</option>
                    <option value="web" <?php echo ($editData && $editData['category'] === 'web') ? 'selected' : ''; ?>>Web</option>
                    <option value="mobile" <?php echo ($editData && $editData['category'] === 'mobile') ? 'selected' : ''; ?>>Mobil</option>
                    <option value="design" <?php echo ($editData && $editData['category'] === 'design') ? 'selected' : ''; ?>>Tasarım</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Proje Görseli *</label>
                <input type="file" name="project_image" class="form-control" accept="image/*" <?php echo $editData ? '' : 'required'; ?>>
                <?php if ($editData && !empty($editData['image'])): ?>
                <img src="../<?php echo $editData['image']; ?>" alt="Project Image" style="max-width: 300px; margin-top: 12px; border-radius: 12px;">
                <?php endif; ?>
            </div>
            
            <!-- Web App Alanları -->
            <div id="webapp_fields" style="display: none;">
                <div class="form-group">
                    <label>Klasör Adı (URL için) *</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <span style="color: #6b7280; white-space: nowrap;"><?php echo SITE_URL; ?>/apps/</span>
                        <input type="text" name="folder_name" class="form-control" placeholder="ornek-proje" value="<?php echo $editData && !empty($editData['folder_path']) ? str_replace('apps/', '', $editData['folder_path']) : ''; ?>" pattern="[a-z0-9-]+" style="flex: 1;">
                    </div>
                    <small style="color: #6b7280; font-size: 12px;">
                        Sadece küçük harf, rakam ve tire (-) kullan. Örnek: url-testi, proje-1
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Web App Dosyaları (ZIP)</label>
                    <input type="file" name="webapp_files" class="form-control" accept=".zip">
                    <small style="color: #6b7280; font-size: 12px;">
                        Tüm web app dosyalarını ZIP olarak yükle.
                    </small>
                    <?php if ($editData && $editData['project_type'] === 'webapp' && !empty($editData['folder_path'])): ?>
                    <div style="margin-top: 8px; padding: 12px; background: #eff6ff; border-radius: 8px; font-size: 13px;">
                        <strong>Mevcut Klasör:</strong> <?php echo $editData['folder_path']; ?><br>
                        <strong>URL:</strong> <a href="../<?php echo $editData['folder_path']; ?>/" target="_blank"><?php echo SITE_URL . '/' . $editData['folder_path']; ?>/</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div style="padding: 12px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; margin-top: 16px;">
                    <strong style="color: #92400e;">Veya Harici URL:</strong>
                    <input type="url" name="external_url" class="form-control" placeholder="https://baska-site.com/proje" value="<?php echo htmlspecialchars($editData['demo_url'] ?? ''); ?>" style="margin-top: 8px;">
                    <small style="color: #92400e; font-size: 12px;">
                        Proje başka bir sitede host ediliyorsa buraya gir. Harici URL varsa ZIP yüklemene gerek yok.
                    </small>
                </div>
            </div>
            
            <!-- Blog Alanları -->
            <div id="blog_fields" style="display: none;">
                <div class="form-group">
                    <label>Demo URL (Opsiyonel)</label>
                    <input type="url" name="demo_url" class="form-control" placeholder="https://..." value="<?php echo htmlspecialchars($editData['demo_url'] ?? ''); ?>">
                    <small style="color: #6b7280; font-size: 12px;">Projenin canlı demo'su varsa buraya gir</small>
                </div>
                
                <div class="form-group">
                    <label>GitHub URL (Opsiyonel)</label>
                    <input type="url" name="github_url" class="form-control" placeholder="https://github.com/..." value="<?php echo htmlspecialchars($editData['github_url'] ?? ''); ?>">
                    <small style="color: #6b7280; font-size: 12px;">Projenin GitHub repo'su varsa buraya gir</small>
                </div>
            </div>
            
            <div class="form-group">
                <label>Etiketler (virgülle ayır)</label>
                <input type="text" name="tags" class="form-control" placeholder="PHP,MySQL,JavaScript,React" value="<?php echo htmlspecialchars($editData['tags'] ?? ''); ?>">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Sıra</label>
                    <input type="number" name="display_order" class="form-control" value="<?php echo $editData['display_order'] ?? 0; ?>" min="0">
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding-top: 8px;">
                        <input type="checkbox" name="is_active" <?php echo (!$editData || $editData['is_active']) ? 'checked' : ''; ?>>
                        Aktif
                    </label>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button type="submit" name="submit_project" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $editData ? 'Güncelle' : 'Ekle'; ?>
                </button>
                <a href="projects.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleProjectTypeFields() {
    const projectType = document.getElementById('project_type').value;
    const webappFields = document.getElementById('webapp_fields');
    const blogFields = document.getElementById('blog_fields');
    const folderNameInput = document.querySelector('[name="folder_name"]');
    
    if (projectType === 'webapp') {
        webappFields.style.display = 'block';
        blogFields.style.display = 'none';
        if (folderNameInput) {
            folderNameInput.setAttribute('required', 'required');
        }
    } else if (projectType === 'blog') {
        webappFields.style.display = 'none';
        blogFields.style.display = 'block';
        if (folderNameInput) {
            folderNameInput.removeAttribute('required');
        }
    } else {
        webappFields.style.display = 'none';
        blogFields.style.display = 'none';
    }
}

// Sayfa yüklendiğinde çalıştır
document.addEventListener('DOMContentLoaded', function() {
    toggleProjectTypeFields();
});
</script>

<?php else: ?>
<!-- Projeler Listesi -->
<div style="margin-bottom: 24px;">
    <a href="?action=add" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Yeni Proje Ekle
    </a>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-folder"></i> Tüm Projeler (<?php echo count($projects); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($projects)): ?>
        <p class="empty-state">Henüz proje eklenmedi.</p>
        <?php else: ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($projects as $project): ?>
            <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 12px; display: flex; gap: 20px; align-items: start;">
                <div style="flex-shrink: 0;">
                    <?php if (!empty($project['image'])): ?>
                    <img src="../<?php echo $project['image']; ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" style="width: 200px; height: 120px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                    <div style="width: 200px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div>
                            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
                                <h4 style="font-size: 18px;"><?php echo htmlspecialchars($project['title']); ?></h4>
                                <?php if ($project['project_type'] === 'webapp'): ?>
                                <span style="padding: 4px 12px; background: #dbeafe; color: #1e40af; border-radius: 50px; font-size: 11px; font-weight: 600;">
                                    <i class="fas fa-code"></i> WEB APP
                                </span>
                                <?php else: ?>
                                <span style="padding: 4px 12px; background: #fef3c7; color: #92400e; border-radius: 50px; font-size: 11px; font-weight: 600;">
                                    <i class="fas fa-blog"></i> BLOG
                                </span>
                                <?php endif; ?>
                                <?php if ($project['category']): ?>
                                <span style="padding: 4px 12px; background: #e0e7ff; color: #3730a3; border-radius: 50px; font-size: 11px; font-weight: 600;">
                                    <?php echo strtoupper($project['category']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 4px;">
                            <a href="?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=toggle&id=<?php echo $project['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-outline" title="Durumu Değiştir">
                                <i class="fas fa-power-off"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $project['id']; ?>&_csrf=<?php echo app_csrf_token(); ?>" class="btn btn-sm btn-danger" title="Sil" onclick="return confirm('Bu projeyi silmek istediğinize emin misiniz?<?php echo $project['project_type'] === 'webapp' ? ' Proje dosyaları da silinecek!' : ''; ?>');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <p style="color: var(--text-secondary); margin-bottom: 12px; line-height: 1.6;">
                        <?php echo htmlspecialchars($project['description']); ?>
                    </p>
                    
                    <?php if (!empty($project['tags'])): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                        <?php 
                        $tags = explode(',', $project['tags']);
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
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 16px; font-size: 13px; color: var(--text-secondary); flex-wrap: wrap;">
                        <span><i class="fas fa-sort-numeric-up"></i> Sıra: <?php echo $project['display_order']; ?></span>
                        <?php if ($project['project_type'] === 'webapp'): ?>
                            <?php if (!empty($project['demo_url'])): ?>
                            <a href="<?php echo $project['demo_url']; ?>" target="_blank" style="color: #667eea;">
                                <i class="fas fa-external-link-alt"></i> Harici URL
                            </a>
                            <?php elseif (!empty($project['folder_path'])): ?>
                            <?php 
                            $folderName = str_replace('apps/', '', $project['folder_path']);
                            ?>
                            <a href="../webapp-wrapper.php?project=<?php echo urlencode($folderName); ?>" target="_blank" style="color: #667eea;">
                                <i class="fas fa-rocket"></i> Web App'i Görüntüle
                            </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (!empty($project['demo_url'])): ?>
                            <a href="<?php echo $project['demo_url']; ?>" target="_blank" style="color: #667eea;">
                                <i class="fas fa-eye"></i> Demo
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($project['github_url'])): ?>
                        <a href="<?php echo $project['github_url']; ?>" target="_blank" style="color: #667eea;">
                            <i class="fab fa-github"></i> GitHub
                        </a>
                        <?php endif; ?>
                        <?php if ($project['is_active']): ?>
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
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
