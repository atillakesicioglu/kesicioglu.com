<?php
require_once 'config.php';

// Tüm ayarları çek
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Sosyal medya linklerini çek
$socialLinks = $pdo->query("SELECT * FROM social_links WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

// Menü öğelerini çek
$menuItems = $pdo->query("SELECT * FROM navigation_menu WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

// Yetenekleri çek
$skills = $pdo->query("SELECT * FROM skills WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

// Projeleri çek
$projects = $pdo->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $siteTitle = $settings['site_title'] ?? 'Kesicioğlu';
        $siteSubtitle = $settings['site_subtitle'] ?? 'Computer Engineer';
        $fullTitle = $siteTitle . ' - ' . $siteSubtitle;
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        $canonical = $siteUrl ? ($siteUrl . '/') : '';
        $ogImage = !empty($settings['hero_image']) ? ($siteUrl ? $siteUrl . '/' . ltrim($settings['hero_image'], '/') : $settings['hero_image']) : '';
        $metaDesc = $settings['hero_description'] ?? 'Computer Engineer - Portfolio & Projects';
    ?>
    <meta name="description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <title><?php echo htmlspecialchars($fullTitle); ?></title>
    <?php if ($canonical): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <?php endif; ?>

    <!-- OpenGraph / Twitter -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($fullTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <?php if ($canonical): ?><meta property="og:url" content="<?php echo htmlspecialchars($canonical); ?>"><?php endif; ?>
    <?php if ($ogImage): ?><meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>"><?php endif; ?>

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($fullTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <?php if ($ogImage): ?><meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>"><?php endif; ?>

    <!-- JSON-LD (Person) -->
    <script type="application/ld+json"><?php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $siteTitle,
        'jobTitle' => $siteSubtitle,
        'url' => $canonical ?: null,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (!empty($settings['primary_color'])): ?>
    <style>
        :root {
            --primary: <?php echo $settings['primary_color']; ?> !important;
            --primary-dark: <?php echo $settings['primary_color']; ?> !important;
            --secondary: <?php echo $settings['secondary_color']; ?> !important;
            --accent: <?php echo $settings['accent_color']; ?> !important;
            --gradient-primary: linear-gradient(135deg, <?php echo $settings['primary_color']; ?> 0%, <?php echo $settings['secondary_color']; ?> 100%) !important;
            --gradient-secondary: linear-gradient(135deg, <?php echo $settings['accent_color']; ?> 0%, <?php echo $settings['secondary_color']; ?> 100%) !important;
        }
    </style>
    <?php endif; ?>
    <?php if (!empty($settings['head_analytics_code'])): ?>
    <?php echo $settings['head_analytics_code']; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <a href="#" class="logo"><?php echo $settings['site_title'] ?? 'Kesicioğlu'; ?><span class="dot">.</span></a>
                <ul class="nav-menu" id="nav-menu">
                    <?php foreach ($menuItems as $item): ?>
                    <li><a href="<?php echo htmlspecialchars($item['menu_link']); ?>" class="nav-link"><?php echo htmlspecialchars($item['menu_text']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <div class="nav-icons">
                    <button class="theme-toggle" id="theme-toggle" aria-label="Toggle Theme">
                        <i class="fas fa-moon"></i>
                    </button>
                    <button class="mobile-toggle" id="mobile-toggle" aria-label="Toggle Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        <?php echo nl2br(htmlspecialchars($settings['hero_title'] ?? 'Hello, I am Kesicioğlu')); ?>
                    </h1>
                    <h2 class="hero-subtitle"><?php echo htmlspecialchars($settings['hero_subtitle'] ?? 'Computer Engineer'); ?></h2>
                    <p class="hero-description">
                        <?php echo nl2br(htmlspecialchars($settings['hero_description'] ?? '')); ?>
                    </p>
                    <div class="hero-buttons">
                        <a href="#projects" class="btn btn-primary">View Projects</a>
                        <a href="#contact" class="btn btn-secondary">Get in Touch</a>
                    </div>
                    <div class="social-links">
                        <?php foreach ($socialLinks as $link): ?>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" aria-label="<?php echo htmlspecialchars($link['platform']); ?>" target="_blank">
                            <i class="<?php echo htmlspecialchars($link['icon']); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="image-wrapper">
                        <?php if (!empty($settings['hero_image'])): ?>
                        <img src="<?php echo $settings['hero_image']; ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 24px;">
                        <?php else: ?>
                        <div class="image-placeholder">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Discover Who I Am</span>
                <h2 class="section-title">About Me</h2>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <p><?php echo nl2br(htmlspecialchars($settings['about_text_1'] ?? '')); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($settings['about_text_2'] ?? '')); ?></p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <h3 class="stat-number" data-target="<?php echo $settings['stat_projects'] ?? 15; ?>">0</h3>
                            <p class="stat-label">Completed Projects</p>
                        </div>
                        <div class="stat-item">
                            <h3 class="stat-number" data-target="<?php echo $settings['stat_experience'] ?? 3; ?>">0</h3>
                            <p class="stat-label">Years of Experience</p>
                        </div>
                        <div class="stat-item">
                            <h3 class="stat-number" data-target="<?php echo $settings['stat_clients'] ?? 20; ?>">0</h3>
                            <p class="stat-label">Happy Clients</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="skills">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Areas of Expertise</span>
                <h2 class="section-title">Skills</h2>
            </div>
            <div class="skills-grid">
                <?php foreach ($skills as $skill): ?>
                <div class="skill-card">
                    <div class="skill-icon">
                        <i class="<?php echo htmlspecialchars($skill['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($skill['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($skill['description'])); ?></p>
                    <div class="skill-tags">
                        <?php 
                        $tags = explode(',', $skill['tags']);
                        foreach ($tags as $tag): 
                            if (trim($tag)):
                        ?>
                        <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="projects">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">My Work</span>
                <h2 class="section-title">Projects</h2>
            </div>
            <div class="projects-filter">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="web">Web</button>
                <button class="filter-btn" data-filter="mobile">Mobile</button>
                <button class="filter-btn" data-filter="design">Design</button>
            </div>
            <div class="projects-grid" id="projects-grid">
                <?php foreach ($projects as $project): ?>
                <div class="project-card" data-category="<?php echo htmlspecialchars($project['category'] ?? 'web'); ?>">
                    <div class="project-image">
                        <?php if (!empty($project['image'])): ?>
                        <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/600x400/667eea/ffffff?text=<?php echo urlencode($project['title']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <?php endif; ?>
                        <div class="project-overlay">
                            <?php if ($project['project_type'] === 'webapp'): ?>
                                <?php if (!empty($project['demo_url'])): ?>
                                <!-- Harici URL -->
                                <a href="<?php echo htmlspecialchars($project['demo_url']); ?>" target="_blank" class="project-link" title="Open External Website"><i class="fas fa-external-link-alt"></i></a>
                                <?php elseif (!empty($project['folder_path'])): ?>
                                <!-- Lokal Web App - Wrapper ile aç -->
                                <?php 
                                $folderName = str_replace('apps/', '', $project['folder_path']);
                                ?>
                                <a href="webapp-wrapper.php?project=<?php echo urlencode($folderName); ?>" class="project-link" title="Open Web App"><i class="fas fa-rocket"></i></a>
                                <?php endif; ?>
                            <?php elseif (!empty($project['demo_url'])): ?>
                            <a href="<?php echo htmlspecialchars($project['demo_url']); ?>" target="_blank" class="project-link"><i class="fas fa-eye"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($project['github_url'])): ?>
                            <a href="<?php echo htmlspecialchars($project['github_url']); ?>" target="_blank" class="project-link"><i class="fab fa-github"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="project-info">
                        <div class="project-tags">
                            <?php 
                            $tags = explode(',', $project['tags']);
                            foreach ($tags as $tag): 
                                if (trim($tag)):
                            ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p class="project-description">
                            <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Get in Touch with Me</span>
                <h2 class="section-title">Contact</h2>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Let's Work Together</h3>
                    <p>
                        I am always open to new projects and collaboration opportunities.
                        Have a project in mind? Let's talk!
                    </p>
                    <div class="contact-items">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Email</h4>
                                <p><?php echo htmlspecialchars($settings['contact_email'] ?? 'info@kesicioglu.com'); ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Phone</h4>
                                <p><?php echo htmlspecialchars($settings['contact_phone'] ?? '+90 555 555 55 55'); ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Location</h4>
                                <p><?php echo htmlspecialchars($settings['contact_location'] ?? 'Istanbul, Turkey'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <form class="contact-form" id="contact-form">
                    <!-- Honeypot field (bots için gizli tuzak) -->
                    <input type="text" name="website" id="website" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" autocomplete="off">
                    
                    <!-- Form load time (spam koruması) -->
                    <input type="hidden" name="form_time" id="form_time" value="">
                    
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email Address" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="subject" placeholder="Subject" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <span>Send Message</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <a href="#" class="footer-logo"><?php echo $settings['site_title'] ?? 'Kesicioğlu'; ?><span class="dot">.</span></a>
                    <p><?php echo htmlspecialchars($settings['footer_text'] ?? 'Computer Engineer • Web Developer'); ?></p>
                </div>
                <div class="footer-links">
                    <?php foreach ($menuItems as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['menu_link']); ?>"><?php echo htmlspecialchars($item['menu_text']); ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="footer-social">
                    <?php foreach ($socialLinks as $link): ?>
                    <a href="<?php echo htmlspecialchars($link['url']); ?>" aria-label="<?php echo htmlspecialchars($link['platform']); ?>" target="_blank">
                        <i class="<?php echo htmlspecialchars($link['icon']); ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $settings['site_title'] ?? 'Kesicioğlu'; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="back-to-top" aria-label="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="js/main.js"></script>
</body>
</html>
