-- ============================================================================
-- MiniWorkers — Microjob Marketplace
-- Full database schema (MySQL 8.0+ / MariaDB 10.6+)
-- Generated from Laravel migrations (database/migrations/*.php)
-- PHP 8.3 / 8.4 / 8.5 compatible
-- ============================================================================
--
-- This file is a REFERENCE schema. The recommended way to create the database
-- is to run the Laravel migrations during installation (php artisan migrate:fresh --seed).
-- You may import this file directly as a fallback if you prefer raw SQL.
--
-- Import:
--   mysql -u root -p your_database < database.sql
--
-- NOTE: If you run migrations AND import this file, drop all tables first to
-- avoid conflicts. Migrations create a `migrations` tracking table too.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- ----------------------------------------------------------------------------
-- users
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(32) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `country_code` VARCHAR(4) NULL,
  `password` VARCHAR(191) NOT NULL,
  `image` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `balance` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `total_earned` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `referrer_id` BIGINT UNSIGNED NULL,
  `referral_code` VARCHAR(20) NOT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `banned` TINYINT(1) NOT NULL DEFAULT 0,
  `email_verified_at` TIMESTAMP NULL,
  `activated_at` TIMESTAMP NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  KEY `users_referrer_id_is_active_index` (`referrer_id`, `is_active`),
  CONSTRAINT `users_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- password_reset_tokens
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- sessions
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- cache
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- cache_locks
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`, `owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- jobs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- failed_jobs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- admins
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `username` VARCHAR(60) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(191) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
  `image` VARCHAR(255) NULL,
  `last_login_at` TIMESTAMP NULL,
  `last_login_ip` VARCHAR(45) NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_username_unique` (`username`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- countries
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(4) NOT NULL,
  `phone_code` VARCHAR(8) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- currencies
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(6) NOT NULL,
  `name` VARCHAR(60) NOT NULL,
  `symbol` VARCHAR(6) NOT NULL,
  `usd_value` DECIMAL(14,6) NOT NULL DEFAULT 1.000000,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `paystack_supported` TINYINT(1) NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `position` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currencies_code_unique` (`code`),
  KEY `currencies_active_index` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- app_settings
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `app_settings`;
CREATE TABLE `app_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL DEFAULT 'MiniWorkers',
  `logotext` VARCHAR(60) NOT NULL DEFAULT 'MiniWorkers',
  `logo` VARCHAR(255) NULL,
  `favicon` VARCHAR(255) NULL,
  `url` VARCHAR(255) NULL,
  `default_currency_id` BIGINT UNSIGNED NULL,
  `need_verification` TINYINT(1) NOT NULL DEFAULT 1,
  `saas` TINYINT(1) NOT NULL DEFAULT 1,
  `manual_payment` TINYINT(1) NOT NULL DEFAULT 1,
  `withdraw_com` DECIMAL(8,2) NOT NULL DEFAULT 10.00,
  `task_com` DECIMAL(8,2) NOT NULL DEFAULT 15.00,
  `activation_fee` DECIMAL(10,2) NOT NULL DEFAULT 5.00,
  `affiliate_reward` DECIMAL(10,2) NOT NULL DEFAULT 1.50,
  `affiliate_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `ann_status` TINYINT(1) NOT NULL DEFAULT 0,
  `ann_text` TEXT NULL,
  `booking_limit` INT UNSIGNED NOT NULL DEFAULT 5,
  `ss_limit` INT UNSIGNED NOT NULL DEFAULT 3,
  `address` VARCHAR(255) NULL,
  `contact_email` VARCHAR(255) NULL,
  `phone` VARCHAR(255) NULL,
  `footer_text` TEXT NULL,
  `primary_color` VARCHAR(9) NOT NULL DEFAULT '#2563eb',
  `accent_color` VARCHAR(9) NOT NULL DEFAULT '#1e40af',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `app_settings_default_currency_id_foreign` (`default_currency_id`),
  CONSTRAINT `app_settings_default_currency_id_foreign` FOREIGN KEY (`default_currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- task_categories
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `task_categories`;
CREATE TABLE `task_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `min_amount` INT UNSIGNED NOT NULL DEFAULT 1,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `position` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_categories_slug_unique` (`slug`),
  KEY `task_categories_parent_id_index` (`parent_id`),
  CONSTRAINT `task_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `task_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- tasks
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL,
  `title` VARCHAR(191) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `action_url` VARCHAR(255) NULL,
  `details` TEXT NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `amount` INT UNSIGNED NOT NULL,
  `time` INT UNSIGNED NULL,
  `total_price` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `booked` INT UNSIGNED NOT NULL DEFAULT 0,
  `submitted` INT UNSIGNED NOT NULL DEFAULT 0,
  `completed` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 0,
  `reject_note` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tasks_code_unique` (`code`),
  KEY `tasks_status_date_index` (`status`, `date`),
  KEY `tasks_user_id_status_index` (`user_id`, `status`),
  CONSTRAINT `tasks_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `task_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- task_bookings
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `task_bookings`;
CREATE TABLE `task_bookings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `task_id` BIGINT UNSIGNED NOT NULL,
  `expire_in` DATETIME NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_bookings_user_id_task_id_unique` (`user_id`, `task_id`),
  KEY `task_bookings_expire_in_index` (`expire_in`),
  CONSTRAINT `task_bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_bookings_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- task_proofs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `task_proofs`;
CREATE TABLE `task_proofs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `task_id` BIGINT UNSIGNED NOT NULL,
  `comment` TEXT NOT NULL,
  `images` JSON NOT NULL,
  `reject_note` VARCHAR(255) NULL,
  `status` TINYINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_proofs_user_id_task_id_unique` (`user_id`, `task_id`),
  KEY `task_proofs_task_id_status_index` (`task_id`, `status`),
  CONSTRAINT `task_proofs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_proofs_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- deposit_methods
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `deposit_methods`;
CREATE TABLE `deposit_methods` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `logo` VARCHAR(255) NULL,
  `min_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `instructions` TEXT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `manual` TINYINT(1) NOT NULL DEFAULT 0,
  `position` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deposit_methods_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- deposits
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `deposits`;
CREATE TABLE `deposits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `method_id` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `amount_paid` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `type` VARCHAR(20) NOT NULL DEFAULT 'wallet',
  `reference` VARCHAR(60) NULL,
  `manual` TINYINT(1) NOT NULL DEFAULT 0,
  `note` VARCHAR(255) NULL,
  `reject_note` VARCHAR(255) NULL,
  `status` TINYINT NOT NULL DEFAULT 0,
  `date` DATE NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deposits_reference_unique` (`reference`),
  KEY `deposits_user_id_status_index` (`user_id`, `status`),
  KEY `deposits_type_index` (`type`),
  CONSTRAINT `deposits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deposits_method_id_foreign` FOREIGN KEY (`method_id`) REFERENCES `deposit_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- withdrawal_methods
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `withdrawal_methods`;
CREATE TABLE `withdrawal_methods` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `logo` VARCHAR(255) NULL,
  `min_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `gift_card` TINYINT(1) NOT NULL DEFAULT 0,
  `position` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `withdrawal_methods_slug_unique` (`slug`),
  KEY `withdrawal_methods_parent_id_index` (`parent_id`),
  CONSTRAINT `withdrawal_methods_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `withdrawal_methods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- withdrawals
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `withdrawals`;
CREATE TABLE `withdrawals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `method_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `fee` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `paid` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `details` TEXT NOT NULL,
  `status` TINYINT NOT NULL DEFAULT 0,
  `reject_note` VARCHAR(255) NULL,
  `date` DATE NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawals_user_id_status_index` (`user_id`, `status`),
  CONSTRAINT `withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `withdrawals_method_id_foreign` FOREIGN KEY (`method_id`) REFERENCES `withdrawal_methods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- complaints
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `complaints`;
CREATE TABLE `complaints` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `proof_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `user2_id` BIGINT UNSIGNED NOT NULL,
  `details` TEXT NOT NULL,
  `reply` TEXT NULL,
  `status` TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `complaints_status_index` (`status`),
  CONSTRAINT `complaints_proof_id_foreign` FOREIGN KEY (`proof_id`) REFERENCES `task_proofs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `complaints_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `complaints_user2_id_foreign` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- messages
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `from_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `seen` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `messages_user_id_seen_index` (`user_id`, `seen`),
  CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- faqs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  `position` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- verifications
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `verifications`;
CREATE TABLE `verifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(6) NOT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'email',
  `expire_in` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `verifications_user_id_type_index` (`user_id`, `type`),
  CONSTRAINT `verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- transactions
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(30) NOT NULL,
  `reference` VARCHAR(80) NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `balance_after` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(6) NOT NULL DEFAULT 'USD',
  `status` VARCHAR(20) NOT NULL DEFAULT 'completed',
  `description` TEXT NULL,
  `related_id` BIGINT UNSIGNED NULL,
  `related_type` VARCHAR(60) NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_user_id_type_index` (`user_id`, `type`),
  KEY `transactions_reference_index` (`reference`),
  KEY `transactions_related_type_related_id_index` (`related_type`, `related_id`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- affiliate_referrals
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `affiliate_referrals`;
CREATE TABLE `affiliate_referrals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` BIGINT UNSIGNED NOT NULL,
  `referee_id` BIGINT UNSIGNED NOT NULL,
  `reward_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `activation_deposit_id` BIGINT UNSIGNED NULL,
  `reward_transaction_id` BIGINT UNSIGNED NULL,
  `rewarded_at` TIMESTAMP NULL,
  `revoke_reason` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliate_referrals_referee_id_unique` (`referee_id`),
  KEY `affiliate_referrals_referrer_id_status_index` (`referrer_id`, `status`),
  CONSTRAINT `affiliate_referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliate_referrals_referee_id_foreign` FOREIGN KEY (`referee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliate_referrals_activation_deposit_id_foreign` FOREIGN KEY (`activation_deposit_id`) REFERENCES `deposits` (`id`) ON DELETE SET NULL,
  CONSTRAINT `affiliate_referrals_reward_transaction_id_foreign` FOREIGN KEY (`reward_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Currencies (USD is default; NGN/GHS/ZAR/KES are Paystack-supported)
INSERT INTO `currencies` (`code`, `name`, `symbol`, `usd_value`, `is_default`, `paystack_supported`, `active`, `position`, `created_at`, `updated_at`) VALUES
('USD', 'US Dollar',         '$',   1.000000,    1, 0, 1, 0, NOW(), NOW()),
('NGN', 'Nigerian Naira',     '₦',   1500.000000, 0, 1, 1, 1, NOW(), NOW()),
('GHS', 'Ghanaian Cedi',      '₵',   15.000000,   0, 1, 1, 2, NOW(), NOW()),
('ZAR', 'South African Rand', 'R',   18.000000,   0, 1, 1, 3, NOW(), NOW()),
('KES', 'Kenyan Shilling',    'KSh', 130.000000,  0, 1, 1, 4, NOW(), NOW()),
('EUR', 'Euro',               '€',   0.920000,    0, 0, 1, 5, NOW(), NOW()),
('GBP', 'British Pound',      '£',   0.790000,    0, 0, 1, 6, NOW(), NOW()),
('INR', 'Indian Rupee',       '₹',   83.000000,   0, 0, 1, 7, NOW(), NOW()),
('PKR', 'Pakistani Rupee',    '₨',   278.000000,  0, 0, 1, 8, NOW(), NOW()),
('BDT', 'Bangladeshi Taka',   '৳',   110.000000,  0, 0, 1, 9, NOW(), NOW()),
('PHP', 'Philippine Peso',    '₱',   56.000000,   0, 0, 1, 10, NOW(), NOW()),
('BRL', 'Brazilian Real',     'R$',  5.100000,    0, 0, 1, 11, NOW(), NOW());

-- App settings (default row). default_currency_id = 1 (USD)
INSERT INTO `app_settings` (`id`, `name`, `logotext`, `url`, `default_currency_id`, `need_verification`, `saas`, `manual_payment`, `withdraw_com`, `task_com`, `activation_fee`, `affiliate_reward`, `affiliate_enabled`, `ann_status`, `ann_text`, `booking_limit`, `ss_limit`, `address`, `contact_email`, `phone`, `footer_text`, `primary_color`, `accent_color`, `created_at`, `updated_at`) VALUES
(1, 'MiniWorkers', 'MiniWorkers', NULL, 1, 1, 1, 1, 10.00, 15.00, 5.00, 1.50, 1, 0, NULL, 5, 3, NULL, 'support@example.com', NULL, 'MiniWorkers — Earn by completing micro tasks.', '#2563eb', '#1e40af', NOW(), NOW());

-- Task categories (parents)
INSERT INTO `task_categories` (`id`, `parent_id`, `name`, `slug`, `price`, `min_amount`, `active`, `position`, `created_at`, `updated_at`) VALUES
(1,  NULL, 'Social Media',        'social-media',     0.50, 1, 1, 0, NOW(), NOW()),
(2,  NULL, 'Content & Writing',   'content-writing',  0.80, 1, 1, 1, NOW(), NOW()),
(3,  NULL, 'Design & Creative',   'design-creative',  1.00, 1, 1, 2, NOW(), NOW()),
(4,  NULL, 'Web & Tech',          'web-tech',         1.50, 1, 1, 3, NOW(), NOW()),
(5,  NULL, 'Marketing & SEO',     'marketing-seo',    1.20, 1, 1, 4, NOW(), NOW()),
(6,  NULL, 'Surveys & Reviews',   'surveys-reviews',  0.40, 1, 1, 5, NOW(), NOW()),
(7,  NULL, 'App Install & Test',  'app-install-test', 0.60, 1, 1, 6, NOW(), NOW()),
(8,  NULL, 'Video & Audio',       'video-audio',      1.10, 1, 1, 7, NOW(), NOW());

-- Task categories (children)
INSERT INTO `task_categories` (`parent_id`, `name`, `slug`, `price`, `min_amount`, `active`, `position`, `created_at`, `updated_at`) VALUES
(1, 'Facebook Likes',        'facebook-likes-social-media',         0.50, 1, 1, 0, NOW(), NOW()),
(1, 'Twitter/X Retweets',    'twitter-x-retweets-social-media',     0.50, 1, 1, 1, NOW(), NOW()),
(1, 'Instagram Followers',   'instagram-followers-social-media',    0.50, 1, 1, 2, NOW(), NOW()),
(1, 'YouTube Subscribers',   'youtube-subscribers-social-media',    0.50, 1, 1, 3, NOW(), NOW()),
(1, 'TikTok Engagement',     'tiktok-engagement-social-media',      0.50, 1, 1, 4, NOW(), NOW()),
(2, 'Blog Comments',         'blog-comments-content-writing',       0.80, 1, 1, 0, NOW(), NOW()),
(2, 'Article Writing',       'article-writing-content-writing',     0.80, 1, 1, 1, NOW(), NOW()),
(2, 'Product Reviews',       'product-reviews-content-writing',     0.80, 1, 1, 2, NOW(), NOW()),
(2, 'Forum Posts',           'forum-posts-content-writing',         0.80, 1, 1, 3, NOW(), NOW()),
(3, 'Logo Design',           'logo-design-design-creative',         1.00, 1, 1, 0, NOW(), NOW()),
(3, 'Banner Design',         'banner-design-design-creative',       1.00, 1, 1, 1, NOW(), NOW()),
(3, 'Photo Editing',         'photo-editing-design-creative',       1.00, 1, 1, 2, NOW(), NOW()),
(3, 'Illustration',          'illustration-design-creative',        1.00, 1, 1, 3, NOW(), NOW()),
(4, 'Website Testing',       'website-testing-web-tech',            1.50, 1, 1, 0, NOW(), NOW()),
(4, 'Bug Reporting',         'bug-reporting-web-tech',              1.50, 1, 1, 1, NOW(), NOW()),
(4, 'Code Review',           'code-review-web-tech',                1.50, 1, 1, 2, NOW(), NOW()),
(4, 'Data Entry',            'data-entry-web-tech',                 1.50, 1, 1, 3, NOW(), NOW()),
(5, 'Backlinks',             'backlinks-marketing-seo',             1.20, 1, 1, 0, NOW(), NOW()),
(5, 'Directory Submission',  'directory-submission-marketing-seo',  1.20, 1, 1, 1, NOW(), NOW()),
(5, 'Keyword Research',      'keyword-research-marketing-seo',      1.20, 1, 1, 2, NOW(), NOW()),
(5, 'Email Marketing',       'email-marketing-marketing-seo',       1.20, 1, 1, 3, NOW(), NOW()),
(6, 'Survey Completion',     'survey-completion-surveys-reviews',   0.40, 1, 1, 0, NOW(), NOW()),
(6, 'Product Feedback',      'product-feedback-surveys-reviews',    0.40, 1, 1, 1, NOW(), NOW()),
(6, 'App Reviews',           'app-reviews-surveys-reviews',         0.40, 1, 1, 2, NOW(), NOW()),
(6, 'Service Reviews',       'service-reviews-surveys-reviews',     0.40, 1, 1, 3, NOW(), NOW()),
(7, 'Android App Install',   'android-app-install-app-install-test',0.60, 1, 1, 0, NOW(), NOW()),
(7, 'iOS App Install',       'ios-app-install-app-install-test',    0.60, 1, 1, 1, NOW(), NOW()),
(7, 'App Testing',           'app-testing-app-install-test',        0.60, 1, 1, 2, NOW(), NOW()),
(7, 'Sign-up Tasks',         'sign-up-tasks-app-install-test',      0.60, 1, 1, 3, NOW(), NOW()),
(8, 'Video Testimonials',    'video-testimonials-video-audio',      1.10, 1, 1, 0, NOW(), NOW()),
(8, 'Voice Over',            'voice-over-video-audio',              1.10, 1, 1, 1, NOW(), NOW()),
(8, 'Video Editing',         'video-editing-video-audio',           1.10, 1, 1, 2, NOW(), NOW()),
(8, 'Subtitling',            'subtitling-video-audio',              1.10, 1, 1, 3, NOW(), NOW());

-- Default FAQs
INSERT INTO `faqs` (`question`, `answer`, `position`, `created_at`, `updated_at`) VALUES
('How do I start earning on MiniWorkers?', 'Create a free account, verify your email, then pay the one-time $5 account activation fee. Once activated, you can browse available tasks, book the ones you want, complete them, submit proof, and get paid to your wallet.', 0, NOW(), NOW()),
('Why is there a $5 activation fee?', 'The activation fee keeps the platform free of spam and fake accounts. It is a one-time payment that unlocks your ability to perform tasks and withdraw earnings. It is not a subscription.', 1, NOW(), NOW()),
('How and when do I get paid?', 'When your submitted proof is approved by the task owner, the task price is credited to your MiniWorkers wallet instantly. You can then request a withdrawal through any available withdrawal method.', 2, NOW(), NOW()),
('What is the affiliate program?', 'Every user gets a unique referral link. When someone signs up through your link and pays the $5 activation fee, you earn a $1.50 reward credited directly to your wallet. Track everything in your Affiliate dashboard.', 3, NOW(), NOW()),
('Which payment methods are supported?', 'Deposits and the activation fee are processed securely through Paystack. Withdrawals are handled through the methods configured by the admin (bank transfer, mobile money, gift cards, etc.).', 4, NOW(), NOW()),
('Can I create my own tasks?', 'Yes. As an activated user you can post tasks (offers) for others to complete. Your wallet is charged the total task cost (price × amount + platform commission) upfront, and it is held until proofs are approved.', 5, NOW(), NOW());

-- Countries (selection — full list seeded by CountrySeeder on migration)
INSERT INTO `countries` (`name`, `code`, `phone_code`, `created_at`, `updated_at`) VALUES
('United States', 'US', '+1', NOW(), NOW()),
('United Kingdom', 'GB', '+44', NOW(), NOW()),
('Nigeria', 'NG', '+234', NOW(), NOW()),
('Ghana', 'GH', '+233', NOW(), NOW()),
('Kenya', 'KE', '+254', NOW(), NOW()),
('South Africa', 'ZA', '+27', NOW(), NOW()),
('India', 'IN', '+91', NOW(), NOW()),
('Pakistan', 'PK', '+92', NOW(), NOW()),
('Bangladesh', 'BD', '+880', NOW(), NOW()),
('Philippines', 'PH', '+63', NOW(), NOW()),
('Canada', 'CA', '+1', NOW(), NOW()),
('Australia', 'AU', '+61', NOW(), NOW()),
('Germany', 'DE', '+49', NOW(), NOW()),
('France', 'FR', '+33', NOW(), NOW()),
('Brazil', 'BR', '+55', NOW(), NOW()),
('United Arab Emirates', 'AE', '+971', NOW(), NOW()),
('Saudi Arabia', 'SA', '+966', NOW(), NOW()),
('Egypt', 'EG', '+20', NOW(), NOW()),
('Indonesia', 'ID', '+62', NOW(), NOW()),
('Vietnam', 'VN', '+84', NOW(), NOW());

-- ============================================================================
-- NOTE: No admin or user accounts are seeded here. The web installer creates
-- the first super admin during installation. Do NOT use default/demo creds.
-- ============================================================================
