-- ===================================
-- Kesicioğlu Portfolio Database
-- ===================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ===================================
-- Admin Login Attempts (brute-force protection)
-- ===================================
CREATE TABLE IF NOT EXISTS `admin_login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ip_time` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- Admin Users Table
-- ===================================
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user (username: id, password: Atilla2002?)
INSERT INTO `admin_users` (`username`, `email`, `password`) VALUES
('id', 'admin@kesicioglu.com', '$2y$10$rQVz1ELQPvZ7L9X5yF4b8eBzKGvN8FZhWqzN4pX5YcJ8hN6x5X5X2');

-- ===================================
-- Site Settings Table
-- ===================================
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('text','textarea','number','color','image','boolean') DEFAULT 'text',
  `category` varchar(50) DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `category`) VALUES
('site_title', 'Kesicioğlu', 'text', 'general'),
('site_subtitle', 'Bilgisayar Mühendisi', 'text', 'general'),
('hero_title', 'Merhaba, Ben Kesicioğlu', 'text', 'hero'),
('hero_subtitle', 'Bilgisayar Mühendisi', 'text', 'hero'),
('hero_description', 'Web geliştirme ve yazılım mühendisliği alanında uzmanlaşmış, modern teknolojilerle kullanıcı deneyimini ön planda tutan projeler geliştiriyorum.', 'textarea', 'hero'),
('hero_image', '', 'image', 'hero'),
('about_text_1', 'Bilgisayar mühendisliği mezunu olarak, teknolojiye olan tutkumla modern web uygulamaları ve yazılım çözümleri geliştiriyorum.', 'textarea', 'about'),
('about_text_2', 'Kodlama yaparken temiz kod prensiplerini ve en iyi pratikleri uygulamaya özen gösteriyor, sürekli öğrenme ve gelişim odaklı çalışıyorum.', 'textarea', 'about'),
('stat_projects', '15', 'number', 'about'),
('stat_experience', '3', 'number', 'about'),
('stat_clients', '20', 'number', 'about'),
('primary_color', '#667eea', 'color', 'design'),
('secondary_color', '#764ba2', 'color', 'design'),
('accent_color', '#f093fb', 'color', 'design'),
('contact_email', 'info@kesicioglu.com', 'text', 'contact'),
('contact_phone', '+90 555 555 55 55', 'text', 'contact'),
('contact_location', 'İstanbul, Türkiye', 'text', 'contact'),
('email_notifications', '1', 'boolean', 'email'),
('notification_email', '', 'text', 'email'),
('footer_text', 'Bilgisayar Mühendisi • Web Developer', 'text', 'footer');

-- ===================================
-- Social Media Links Table
-- ===================================
CREATE TABLE `social_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default social links
INSERT INTO `social_links` (`platform`, `icon`, `url`, `display_order`, `is_active`) VALUES
('GitHub', 'fab fa-github', '#', 1, 1),
('LinkedIn', 'fab fa-linkedin', '#', 2, 1),
('Twitter', 'fab fa-twitter', '#', 3, 1),
('Email', 'fas fa-envelope', '#', 4, 1);

-- ===================================
-- Navigation Menu Table
-- ===================================
CREATE TABLE `navigation_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_text` varchar(50) NOT NULL,
  `menu_link` varchar(100) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default navigation items
INSERT INTO `navigation_menu` (`menu_text`, `menu_link`, `display_order`, `is_active`) VALUES
('Ana Sayfa', '#home', 1, 1),
('Hakkımda', '#about', 2, 1),
('Projeler', '#projects', 3, 1),
('Yetenekler', '#skills', 4, 1),
('İletişim', '#contact', 5, 1);

-- ===================================
-- Skills Table
-- ===================================
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(100) NOT NULL,
  `tags` text,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default skills
INSERT INTO `skills` (`title`, `description`, `icon`, `tags`, `display_order`, `is_active`) VALUES
('Frontend Development', 'HTML5, CSS3, JavaScript, React, Vue.js ile modern ve responsive arayüzler.', 'fab fa-html5', 'HTML,CSS,JavaScript,React', 1, 1),
('Backend Development', 'PHP, Node.js, MySQL ile güçlü ve güvenli backend sistemleri.', 'fas fa-server', 'PHP,Node.js,MySQL,API', 2, 1),
('Responsive Design', 'Tüm cihazlarda mükemmel görünen, kullanıcı dostu arayüzler.', 'fas fa-mobile-alt', 'Mobile-First,Flexbox,Grid,Bootstrap', 3, 1),
('Database Management', 'Veritabanı tasarımı, optimizasyonu ve yönetimi.', 'fas fa-database', 'MySQL,PostgreSQL,MongoDB,Redis', 4, 1);

-- ===================================
-- Projects Table
-- ===================================
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `project_type` enum('webapp','blog') NOT NULL DEFAULT 'blog',
  `category` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tags` text,
  `demo_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `folder_path` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample projects
INSERT INTO `projects` (`title`, `description`, `project_type`, `category`, `image`, `tags`, `display_order`, `is_active`) VALUES
('E-Ticaret Platformu', 'Tam özellikli e-ticaret sitesi. Ürün yönetimi, sepet sistemi, ödeme entegrasyonu ve admin paneli.', 'blog', 'web', 'https://via.placeholder.com/600x400/667eea/ffffff?text=E-Ticaret+Platform', 'PHP,MySQL,JavaScript', 1, 1),
('Blog CMS Sistemi', 'Modern blog yönetim sistemi. Markdown desteği, kategori yönetimi ve SEO optimizasyonu.', 'blog', 'web', 'https://via.placeholder.com/600x400/764ba2/ffffff?text=Blog+CMS', 'React,Node.js,MongoDB', 2, 1),
('Görev Yönetimi Uygulaması', 'Kişisel ve ekip görev yönetimi için mobil uygulama. Gerçek zamanlı senkronizasyon özellikli.', 'blog', 'mobile', 'https://via.placeholder.com/600x400/f093fb/ffffff?text=Mobil+Uygulama', 'React Native,Firebase', 3, 1),
('Kurumsal Web Sitesi', 'Modern ve profesyonel kurumsal web sitesi. Çoklu dil desteği ve dinamik içerik yönetimi.', 'blog', 'web', 'https://via.placeholder.com/600x400/4facfe/ffffff?text=Kurumsal+Web', 'HTML,CSS,JavaScript', 4, 1),
('Modern UI Kit', 'Kapsamlı UI bileşenleri ve tasarım sistemi. Dark mode desteği ve tüm modern component''ler.', 'blog', 'design', 'https://via.placeholder.com/600x400/00f2fe/ffffff?text=UI+Kit', 'Figma,Design System', 5, 1),
('Admin Dashboard', 'Gelişmiş analitik dashboard. Gerçek zamanlı grafikler, raporlama ve veri görselleştirme.', 'blog', 'web', 'https://via.placeholder.com/600x400/43e97b/ffffff?text=Dashboard', 'Vue.js,Chart.js,API', 6, 1);

-- ===================================
-- Contact Messages Table
-- ===================================
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
