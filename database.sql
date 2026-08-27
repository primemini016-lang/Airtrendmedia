-- Airtrendmedia schema generated from the canonical Laravel migrations.
-- Import this file only for manual database provisioning; the web installer remains the supported installation path.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- Laravel migration ledger for manual provisioning.
-- The web installer still runs the migrations itself and does not rely on this ledger.
create table `migrations` (`id` int unsigned not null auto_increment primary key, `migration` varchar(255) not null, `batch` int not null);

insert into `migrations` (`migration`, `batch`) values ('0001_01_01_000000_create_users_table', 1);
insert into `migrations` (`migration`, `batch`) values ('0001_01_01_000001_create_cache_and_sessions_tables', 1);
insert into `migrations` (`migration`, `batch`) values ('0001_01_01_000002_create_jobs_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000010_create_admins_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000020_create_countries_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000030_create_currencies_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000040_create_app_settings_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000050_create_task_categories_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000060_create_tasks_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000070_create_task_bookings_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000080_create_task_proofs_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000090_create_deposit_methods_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000100_create_deposits_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000110_create_withdrawal_methods_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000120_create_withdrawals_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000130_create_complaints_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000140_create_messages_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000150_create_faqs_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000160_create_verifications_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000170_create_transactions_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000180_create_affiliate_referrals_table', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000200_create_new_features_tables', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000300_create_blog_tables', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000400_create_social_tables', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000500_add_trial_and_social_fields_to_users', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000600_create_airtrendmedia_extended_features', 1);
insert into `migrations` (`migration`, `batch`) values ('2024_01_01_000700_upgrade_blog_for_creator_system', 1);

create table `users` (`id` bigint unsigned not null auto_increment primary key, `username` varchar(32) not null, `name` varchar(120) not null, `email` varchar(191) not null, `phone` varchar(30) null, `country_code` varchar(4) null, `password` varchar(191) not null, `image` varchar(255) null, `bio` text null, `balance` decimal(14, 2) not null default '0', `total_earned` decimal(14, 2) not null default '0', `referrer_id` bigint unsigned null, `referral_code` varchar(20) not null, `is_verified` tinyint(1) not null default '0', `is_active` tinyint(1) not null default '0', `banned` tinyint(1) not null default '0', `email_verified_at` timestamp null, `activated_at` timestamp null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `users` add constraint `users_referrer_id_foreign` foreign key (`referrer_id`) references `users` (`id`) on delete set null;

alter table `users` add index `users_referrer_id_is_active_index`(`referrer_id`, `is_active`);

alter table `users` add unique `users_username_unique`(`username`);

alter table `users` add unique `users_email_unique`(`email`);

alter table `users` add unique `users_referral_code_unique`(`referral_code`);

create table `password_reset_tokens` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null, primary key (`email`));

create table `sessions` (`id` varchar(255) not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` text null, `payload` longtext not null, `last_activity` int not null, primary key (`id`));

alter table `sessions` add index `sessions_user_id_index`(`user_id`);

alter table `sessions` add index `sessions_last_activity_index`(`last_activity`);

create table `cache` (`key` varchar(255) not null, `value` mediumtext not null, `expiration` int not null, primary key (`key`));

create table `cache_locks` (`key` varchar(255) not null, `owner` varchar(255) not null, `expiration` int not null, primary key (`key`, `owner`));

create table `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(255) not null, `payload` longtext not null, `attempts` tinyint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null);

alter table `jobs` add index `jobs_queue_index`(`queue`);

create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` text not null, `queue` text not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP);

alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`);

create table `admins` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(120) not null, `username` varchar(60) not null, `email` varchar(191) not null, `password` varchar(191) not null, `role` varchar(20) not null default 'admin', `image` varchar(255) null, `last_login_at` timestamp null, `last_login_ip` varchar(45) null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `admins` add unique `admins_username_unique`(`username`);

alter table `admins` add unique `admins_email_unique`(`email`);

create table `countries` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `code` varchar(4) not null, `phone_code` varchar(8) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `countries` add unique `countries_code_unique`(`code`);

create table `currencies` (`id` bigint unsigned not null auto_increment primary key, `code` varchar(6) not null, `name` varchar(60) not null, `symbol` varchar(6) not null, `usd_value` decimal(14, 6) not null default '1', `is_default` tinyint(1) not null default '0', `paystack_supported` tinyint(1) not null default '0', `active` tinyint(1) not null default '1', `position` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `currencies` add index `currencies_active_index`(`active`);

alter table `currencies` add unique `currencies_code_unique`(`code`);

create table `app_settings` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(120) not null default 'Airtrendmedia', `logotext` varchar(60) not null default 'Airtrendmedia', `logo` varchar(255) null, `favicon` varchar(255) null, `url` varchar(255) null, `default_currency_id` bigint unsigned null, `need_verification` tinyint(1) not null default '0', `saas` tinyint(1) not null default '1', `manual_payment` tinyint(1) not null default '1', `withdraw_com` decimal(8, 2) not null default '10', `task_com` decimal(8, 2) not null default '15', `activation_fee` decimal(10, 2) not null default '5', `affiliate_reward` decimal(10, 2) not null default '1.5', `affiliate_enabled` tinyint(1) not null default '1', `ann_status` tinyint(1) not null default '0', `ann_text` text null, `booking_limit` int unsigned not null default '5', `ss_limit` int unsigned not null default '3', `address` varchar(255) null, `contact_email` varchar(255) null, `phone` varchar(255) null, `footer_text` text null, `primary_color` varchar(9) not null default '#2563eb', `accent_color` varchar(9) not null default '#1e40af', `created_at` timestamp null, `updated_at` timestamp null);

alter table `app_settings` add constraint `app_settings_default_currency_id_foreign` foreign key (`default_currency_id`) references `currencies` (`id`) on delete set null;

create table `task_categories` (`id` bigint unsigned not null auto_increment primary key, `parent_id` bigint unsigned null, `name` varchar(120) not null, `slug` varchar(255) not null, `icon` varchar(60) not null default 'briefcase', `color` varchar(20) not null default '#2563eb', `price` decimal(10, 2) not null default '0', `min_amount` int unsigned not null default '1', `active` tinyint(1) not null default '1', `position` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `task_categories` add constraint `task_categories_parent_id_foreign` foreign key (`parent_id`) references `task_categories` (`id`) on delete cascade;

alter table `task_categories` add index `task_categories_parent_id_index`(`parent_id`);

alter table `task_categories` add unique `task_categories_slug_unique`(`slug`);

create table `tasks` (`id` bigint unsigned not null auto_increment primary key, `code` varchar(32) not null, `title` varchar(191) not null, `price` decimal(10, 2) not null, `action_url` varchar(255) null, `details` text not null, `category_id` bigint unsigned not null, `user_id` bigint unsigned not null, `date` date not null, `amount` int unsigned not null, `time` int unsigned null, `total_price` decimal(14, 2) not null default '0', `booked` int unsigned not null default '0', `submitted` int unsigned not null default '0', `completed` int unsigned not null default '0', `status` tinyint not null default '0', `reject_note` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `tasks` add constraint `tasks_category_id_foreign` foreign key (`category_id`) references `task_categories` (`id`) on delete cascade;

alter table `tasks` add constraint `tasks_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `tasks` add index `tasks_status_date_index`(`status`, `date`);

alter table `tasks` add index `tasks_user_id_status_index`(`user_id`, `status`);

alter table `tasks` add unique `tasks_code_unique`(`code`);

create table `task_bookings` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `task_id` bigint unsigned not null, `expire_in` datetime null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `task_bookings` add constraint `task_bookings_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `task_bookings` add constraint `task_bookings_task_id_foreign` foreign key (`task_id`) references `tasks` (`id`) on delete cascade;

alter table `task_bookings` add unique `task_bookings_user_id_task_id_unique`(`user_id`, `task_id`);

alter table `task_bookings` add index `task_bookings_expire_in_index`(`expire_in`);

create table `task_proofs` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `task_id` bigint unsigned not null, `comment` text not null, `images` json not null, `reject_note` varchar(255) null, `status` tinyint not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `task_proofs` add constraint `task_proofs_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `task_proofs` add constraint `task_proofs_task_id_foreign` foreign key (`task_id`) references `tasks` (`id`) on delete cascade;

alter table `task_proofs` add unique `task_proofs_user_id_task_id_unique`(`user_id`, `task_id`);

alter table `task_proofs` add index `task_proofs_task_id_status_index`(`task_id`, `status`);

create table `deposit_methods` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(60) not null, `slug` varchar(255) not null, `logo` varchar(255) null, `min_amount` decimal(10, 2) not null default '0', `instructions` text null, `active` tinyint(1) not null default '1', `manual` tinyint(1) not null default '0', `position` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `deposit_methods` add unique `deposit_methods_slug_unique`(`slug`);

create table `deposits` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `method_id` bigint unsigned null, `amount` decimal(14, 2) not null, `amount_paid` decimal(14, 2) not null default '0', `type` varchar(20) not null default 'wallet', `reference` varchar(60) null, `manual` tinyint(1) not null default '0', `note` varchar(255) null, `reject_note` varchar(255) null, `status` tinyint not null default '0', `date` date not null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `deposits` add constraint `deposits_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `deposits` add constraint `deposits_method_id_foreign` foreign key (`method_id`) references `deposit_methods` (`id`) on delete set null;

alter table `deposits` add index `deposits_user_id_status_index`(`user_id`, `status`);

alter table `deposits` add index `deposits_type_index`(`type`);

alter table `deposits` add unique `deposits_reference_unique`(`reference`);

create table `withdrawal_methods` (`id` bigint unsigned not null auto_increment primary key, `parent_id` bigint unsigned null, `name` varchar(80) not null, `slug` varchar(255) not null, `logo` varchar(255) null, `min_amount` decimal(10, 2) not null default '0', `active` tinyint(1) not null default '1', `gift_card` tinyint(1) not null default '0', `position` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `withdrawal_methods` add constraint `withdrawal_methods_parent_id_foreign` foreign key (`parent_id`) references `withdrawal_methods` (`id`) on delete cascade;

alter table `withdrawal_methods` add index `withdrawal_methods_parent_id_index`(`parent_id`);

alter table `withdrawal_methods` add unique `withdrawal_methods_slug_unique`(`slug`);

create table `withdrawals` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `method_id` bigint unsigned not null, `amount` decimal(14, 2) not null, `fee` decimal(14, 2) not null default '0', `paid` decimal(14, 2) not null default '0', `details` text not null, `status` tinyint not null default '0', `reject_note` varchar(255) null, `date` date not null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `withdrawals` add constraint `withdrawals_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `withdrawals` add constraint `withdrawals_method_id_foreign` foreign key (`method_id`) references `withdrawal_methods` (`id`) on delete cascade;

alter table `withdrawals` add index `withdrawals_user_id_status_index`(`user_id`, `status`);

create table `complaints` (`id` bigint unsigned not null auto_increment primary key, `proof_id` bigint unsigned not null, `user_id` bigint unsigned not null, `user2_id` bigint unsigned not null, `details` text not null, `reply` text null, `status` tinyint not null default '1', `created_at` timestamp null, `updated_at` timestamp null);

alter table `complaints` add constraint `complaints_proof_id_foreign` foreign key (`proof_id`) references `task_proofs` (`id`) on delete cascade;

alter table `complaints` add constraint `complaints_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `complaints` add constraint `complaints_user2_id_foreign` foreign key (`user2_id`) references `users` (`id`) on delete cascade;

alter table `complaints` add index `complaints_status_index`(`status`);

create table `messages` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `message` text not null, `from_admin` tinyint(1) not null default '0', `seen` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `messages` add constraint `messages_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `messages` add index `messages_user_id_seen_index`(`user_id`, `seen`);

create table `faqs` (`id` bigint unsigned not null auto_increment primary key, `question` text not null, `answer` text not null, `position` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

create table `verifications` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `code` varchar(6) not null, `type` varchar(20) not null default 'email', `expire_in` datetime not null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `verifications` add constraint `verifications_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `verifications` add index `verifications_user_id_type_index`(`user_id`, `type`);

create table `transactions` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `type` varchar(30) not null, `reference` varchar(80) null, `amount` decimal(14, 2) not null, `balance_after` decimal(14, 2) not null default '0', `currency` varchar(6) not null default 'USD', `status` varchar(20) not null default 'completed', `description` text null, `related_id` bigint unsigned null, `related_type` varchar(60) null, `ip_address` varchar(45) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `transactions` add constraint `transactions_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `transactions` add index `transactions_user_id_type_index`(`user_id`, `type`);

alter table `transactions` add index `transactions_reference_index`(`reference`);

alter table `transactions` add index `transactions_related_type_related_id_index`(`related_type`, `related_id`);

create table `affiliate_referrals` (`id` bigint unsigned not null auto_increment primary key, `referrer_id` bigint unsigned not null, `referee_id` bigint unsigned not null, `reward_amount` decimal(10, 2) not null default '0', `status` varchar(20) not null default 'pending', `activation_deposit_id` bigint unsigned null, `reward_transaction_id` bigint unsigned null, `rewarded_at` timestamp null, `revoke_reason` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `affiliate_referrals` add constraint `affiliate_referrals_referrer_id_foreign` foreign key (`referrer_id`) references `users` (`id`) on delete cascade;

alter table `affiliate_referrals` add constraint `affiliate_referrals_referee_id_foreign` foreign key (`referee_id`) references `users` (`id`) on delete cascade;

alter table `affiliate_referrals` add constraint `affiliate_referrals_activation_deposit_id_foreign` foreign key (`activation_deposit_id`) references `deposits` (`id`) on delete set null;

alter table `affiliate_referrals` add constraint `affiliate_referrals_reward_transaction_id_foreign` foreign key (`reward_transaction_id`) references `transactions` (`id`) on delete set null;

alter table `affiliate_referrals` add unique `affiliate_referrals_referee_id_unique`(`referee_id`);

alter table `affiliate_referrals` add index `affiliate_referrals_referrer_id_status_index`(`referrer_id`, `status`);

create table `notifications` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `type` varchar(50) not null, `title` varchar(191) not null, `body` text null, `url` varchar(255) null, `is_read` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `notifications` add constraint `notifications_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `notifications` add index `notifications_user_id_is_read_index`(`user_id`, `is_read`);

create table `ads` (`id` bigint unsigned not null auto_increment primary key, `title` varchar(120) not null, `position` varchar(60) not null default 'header', `type` enum('html', 'image', 'text') not null default 'html', `content` longtext null, `link_url` varchar(255) null, `image_path` varchar(255) null, `active` tinyint(1) not null default '1', `sort_order` int not null default '0', `starts_at` timestamp null, `ends_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

create table `gigs` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `category_id` bigint unsigned null, `title` varchar(191) not null, `description` text not null, `price` decimal(10, 2) not null default '0', `image` varchar(255) null, `gallery` json null, `social_platform` varchar(255) null, `social_url` varchar(255) null, `status` enum('pending', 'active', 'rejected', 'paused') not null default 'pending', `views` int unsigned not null default '0', `sales` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `gigs` add constraint `gigs_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `gigs` add constraint `gigs_category_id_foreign` foreign key (`category_id`) references `task_categories` (`id`) on delete set null;

alter table `gigs` add index `gigs_status_category_id_index`(`status`, `category_id`);

create table `gig_orders` (`id` bigint unsigned not null auto_increment primary key, `gig_id` bigint unsigned not null, `buyer_id` bigint unsigned not null, `requirements` text null, `status` enum('pending', 'in_progress', 'delivered', 'completed', 'cancelled', 'disputed') not null default 'pending', `proof_images` json null, `delivered_at` timestamp null, `completed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `gig_orders` add constraint `gig_orders_gig_id_foreign` foreign key (`gig_id`) references `gigs` (`id`) on delete cascade;

alter table `gig_orders` add constraint `gig_orders_buyer_id_foreign` foreign key (`buyer_id`) references `users` (`id`) on delete cascade;

create table `marketplace_listings` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `category_id` bigint unsigned null, `title` varchar(191) not null, `description` text not null, `price` decimal(10, 2) not null default '0', `listing_type` enum('sell', 'buy') not null, `image` varchar(255) null, `gallery` json null, `location` varchar(255) null, `status` enum('pending', 'active', 'sold', 'rejected', 'closed') not null default 'pending', `views` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `marketplace_listings` add constraint `marketplace_listings_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `marketplace_listings` add constraint `marketplace_listings_category_id_foreign` foreign key (`category_id`) references `task_categories` (`id`) on delete set null;

alter table `marketplace_listings` add index `marketplace_listings_status_listing_type_index`(`status`, `listing_type`);

create table `marketplace_inquiries` (`id` bigint unsigned not null auto_increment primary key, `listing_id` bigint unsigned not null, `buyer_id` bigint unsigned not null, `message` text not null, `offer_price` decimal(10, 2) null, `status` enum('pending', 'accepted', 'rejected', 'completed') not null default 'pending', `created_at` timestamp null, `updated_at` timestamp null);

alter table `marketplace_inquiries` add constraint `marketplace_inquiries_listing_id_foreign` foreign key (`listing_id`) references `marketplace_listings` (`id`) on delete cascade;

alter table `marketplace_inquiries` add constraint `marketplace_inquiries_buyer_id_foreign` foreign key (`buyer_id`) references `users` (`id`) on delete cascade;

create table `site_settings` (`id` bigint unsigned not null auto_increment primary key, `key` varchar(100) not null, `value` longtext null, `group` varchar(50) not null default 'general', `created_at` timestamp null, `updated_at` timestamp null);

alter table `site_settings` add unique `site_settings_key_unique`(`key`);

create table `blog_categories` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(120) not null, `slug` varchar(150) not null, `description` text null, `color` varchar(20) not null default '#2563eb', `is_active` tinyint(1) not null default '1', `sort_order` int not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `blog_categories` add unique `blog_categories_slug_unique`(`slug`);

create table `blog_posts` (`id` bigint unsigned not null auto_increment primary key, `author_id` bigint unsigned not null, `category_id` bigint unsigned null, `title` varchar(191) not null, `slug` varchar(220) not null, `content` longtext not null, `excerpt` text null, `featured_image` varchar(255) null, `gallery` json null, `meta_description` varchar(255) null, `meta_keywords` varchar(255) null, `status` enum('draft', 'published', 'scheduled', 'archived') not null default 'draft', `is_featured` tinyint(1) not null default '0', `published_at` timestamp null, `views_count` int unsigned not null default '0', `likes_count` int unsigned not null default '0', `comments_count` int unsigned not null default '0', `shares_count` int unsigned not null default '0', `reading_time` int unsigned not null default '1', `created_at` timestamp null, `updated_at` timestamp null);

alter table `blog_posts` add constraint `blog_posts_author_id_foreign` foreign key (`author_id`) references `users` (`id`) on delete cascade;

alter table `blog_posts` add constraint `blog_posts_category_id_foreign` foreign key (`category_id`) references `blog_categories` (`id`) on delete set null;

alter table `blog_posts` add index `blog_posts_status_published_at_index`(`status`, `published_at`);

alter table `blog_posts` add index `blog_posts_slug_index`(`slug`);

alter table `blog_posts` add unique `blog_posts_slug_unique`(`slug`);

create table `blog_comments` (`id` bigint unsigned not null auto_increment primary key, `post_id` bigint unsigned not null, `user_id` bigint unsigned not null, `parent_id` bigint unsigned null, `body` text not null, `is_approved` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null);

alter table `blog_comments` add constraint `blog_comments_post_id_foreign` foreign key (`post_id`) references `blog_posts` (`id`) on delete cascade;

alter table `blog_comments` add constraint `blog_comments_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `blog_comments` add constraint `blog_comments_parent_id_foreign` foreign key (`parent_id`) references `blog_comments` (`id`) on delete cascade;

alter table `blog_comments` add index `blog_comments_post_id_is_approved_index`(`post_id`, `is_approved`);

create table `blog_likes` (`id` bigint unsigned not null auto_increment primary key, `post_id` bigint unsigned not null, `user_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `blog_likes` add constraint `blog_likes_post_id_foreign` foreign key (`post_id`) references `blog_posts` (`id`) on delete cascade;

alter table `blog_likes` add constraint `blog_likes_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `blog_likes` add unique `blog_likes_post_id_user_id_unique`(`post_id`, `user_id`);

create table `blog_views` (`id` bigint unsigned not null auto_increment primary key, `post_id` bigint unsigned not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `blog_views` add constraint `blog_views_post_id_foreign` foreign key (`post_id`) references `blog_posts` (`id`) on delete cascade;

alter table `blog_views` add constraint `blog_views_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

alter table `blog_views` add index `blog_views_post_id_user_id_index`(`post_id`, `user_id`);

create table `blog_rates` (`id` bigint unsigned not null auto_increment primary key, `post_id` bigint unsigned not null, `user_id` bigint unsigned not null, `rating` tinyint unsigned not null default '5', `created_at` timestamp null, `updated_at` timestamp null);

alter table `blog_rates` add constraint `blog_rates_post_id_foreign` foreign key (`post_id`) references `blog_posts` (`id`) on delete cascade;

alter table `blog_rates` add constraint `blog_rates_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `blog_rates` add unique `blog_rates_post_id_user_id_unique`(`post_id`, `user_id`);

create table `blog_shares` (`id` bigint unsigned not null auto_increment primary key, `post_id` bigint unsigned not null, `user_id` bigint unsigned null, `platform` varchar(30) not null default 'copy', `created_at` timestamp null, `updated_at` timestamp null);

alter table `blog_shares` add constraint `blog_shares_post_id_foreign` foreign key (`post_id`) references `blog_posts` (`id`) on delete cascade;

alter table `blog_shares` add constraint `blog_shares_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

create table `follows` (`id` bigint unsigned not null auto_increment primary key, `follower_id` bigint unsigned not null, `following_id` bigint unsigned not null, `is_accepted` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null);

alter table `follows` add constraint `follows_follower_id_foreign` foreign key (`follower_id`) references `users` (`id`) on delete cascade;

alter table `follows` add constraint `follows_following_id_foreign` foreign key (`following_id`) references `users` (`id`) on delete cascade;

alter table `follows` add unique `follows_follower_id_following_id_unique`(`follower_id`, `following_id`);

alter table `follows` add index `follows_following_id_index`(`following_id`);

create table `social_posts` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `postable_type` varchar(255) not null, `postable_id` bigint unsigned not null, `content` longtext null, `media` json null, `link_url` varchar(255) null, `link_title` varchar(255) null, `link_description` varchar(255) null, `link_image` varchar(255) null, `visibility` enum('public', 'friends', 'private') not null default 'public', `feeling` enum('happy', 'sad', 'excited', 'loved', 'grateful', 'blessed', 'tired', 'motivated', 'celebrating') null, `location` varchar(255) null, `background_color` varchar(20) null, `likes_count` int unsigned not null default '0', `comments_count` int unsigned not null default '0', `shares_count` int unsigned not null default '0', `views_count` int unsigned not null default '0', `is_monetized` tinyint(1) not null default '0', `is_pinned` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_posts` add constraint `social_posts_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `social_posts` add index `social_posts_postable_type_postable_id_index`(`postable_type`, `postable_id`);

alter table `social_posts` add index `social_posts_postable_type_postable_id_created_at_index`(`postable_type`, `postable_id`, `created_at`);

alter table `social_posts` add index `social_posts_user_id_visibility_index`(`user_id`, `visibility`);

create table `social_comments` (`id` bigint unsigned not null auto_increment primary key, `post_id` bigint unsigned not null, `user_id` bigint unsigned not null, `parent_id` bigint unsigned null, `body` text not null, `media` json null, `likes_count` int unsigned not null default '0', `replies_count` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_comments` add constraint `social_comments_post_id_foreign` foreign key (`post_id`) references `social_posts` (`id`) on delete cascade;

alter table `social_comments` add constraint `social_comments_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `social_comments` add constraint `social_comments_parent_id_foreign` foreign key (`parent_id`) references `social_comments` (`id`) on delete cascade;

alter table `social_comments` add index `social_comments_post_id_parent_id_index`(`post_id`, `parent_id`);

create table `social_likes` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `likeable_type` varchar(255) not null, `likeable_id` bigint unsigned not null, `reaction` varchar(20) not null default 'like', `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_likes` add constraint `social_likes_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `social_likes` add index `social_likes_likeable_type_likeable_id_index`(`likeable_type`, `likeable_id`);

alter table `social_likes` add unique `social_likes_user_id_likeable_type_likeable_id_unique`(`user_id`, `likeable_type`, `likeable_id`);

create table `social_shares` (`id` bigint unsigned not null auto_increment primary key, `post_id` bigint unsigned not null, `user_id` bigint unsigned not null, `comment` text null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_shares` add constraint `social_shares_post_id_foreign` foreign key (`post_id`) references `social_posts` (`id`) on delete cascade;

alter table `social_shares` add constraint `social_shares_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `social_shares` add index `social_shares_post_id_index`(`post_id`);

create table `social_pages` (`id` bigint unsigned not null auto_increment primary key, `owner_id` bigint unsigned not null, `name` varchar(150) not null, `slug` varchar(180) not null, `description` text null, `category` varchar(100) null, `profile_image` varchar(255) null, `cover_image` varchar(255) null, `website` varchar(255) null, `location` varchar(255) null, `phone` varchar(30) null, `email` varchar(255) null, `verification_status` enum('unverified', 'pending', 'verified') not null default 'unverified', `followers_count` int unsigned not null default '0', `likes_count` int unsigned not null default '0', `is_monetized` tinyint(1) not null default '0', `is_published` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_pages` add constraint `social_pages_owner_id_foreign` foreign key (`owner_id`) references `users` (`id`) on delete cascade;

alter table `social_pages` add index `social_pages_slug_index`(`slug`);

alter table `social_pages` add unique `social_pages_slug_unique`(`slug`);

create table `social_page_members` (`id` bigint unsigned not null auto_increment primary key, `page_id` bigint unsigned not null, `user_id` bigint unsigned not null, `role` enum('owner', 'admin', 'editor', 'member') not null default 'member', `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_page_members` add constraint `social_page_members_page_id_foreign` foreign key (`page_id`) references `social_pages` (`id`) on delete cascade;

alter table `social_page_members` add constraint `social_page_members_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `social_page_members` add unique `social_page_members_page_id_user_id_unique`(`page_id`, `user_id`);

create table `social_groups` (`id` bigint unsigned not null auto_increment primary key, `owner_id` bigint unsigned not null, `name` varchar(150) not null, `slug` varchar(180) not null, `description` text null, `category` varchar(100) null, `profile_image` varchar(255) null, `cover_image` varchar(255) null, `privacy` enum('public', 'private', 'secret') not null default 'public', `requires_approval` tinyint(1) not null default '0', `members_count` int unsigned not null default '1', `posts_count` int unsigned not null default '0', `is_monetized` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_groups` add constraint `social_groups_owner_id_foreign` foreign key (`owner_id`) references `users` (`id`) on delete cascade;

alter table `social_groups` add index `social_groups_slug_index`(`slug`);

alter table `social_groups` add unique `social_groups_slug_unique`(`slug`);

create table `social_group_members` (`id` bigint unsigned not null auto_increment primary key, `group_id` bigint unsigned not null, `user_id` bigint unsigned not null, `role` enum('owner', 'admin', 'moderator', 'member') not null default 'member', `status` enum('pending', 'approved', 'banned') not null default 'approved', `created_at` timestamp null, `updated_at` timestamp null);

alter table `social_group_members` add constraint `social_group_members_group_id_foreign` foreign key (`group_id`) references `social_groups` (`id`) on delete cascade;

alter table `social_group_members` add constraint `social_group_members_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `social_group_members` add unique `social_group_members_group_id_user_id_unique`(`group_id`, `user_id`);

create table `conversations` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(150) null, `is_group` tinyint(1) not null default '0', `group_avatar` varchar(255) null, `created_by` bigint unsigned not null, `last_message_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `conversations` add constraint `conversations_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete cascade;

create table `conversation_participants` (`id` bigint unsigned not null auto_increment primary key, `conversation_id` bigint unsigned not null, `user_id` bigint unsigned not null, `role` enum('member', 'admin') not null default 'member', `last_read_at` timestamp null, `is_muted` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `conversation_participants` add constraint `conversation_participants_conversation_id_foreign` foreign key (`conversation_id`) references `conversations` (`id`) on delete cascade;

alter table `conversation_participants` add constraint `conversation_participants_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `conversation_participants` add unique `conversation_participants_conversation_id_user_id_unique`(`conversation_id`, `user_id`);

create table `chat_messages` (`id` bigint unsigned not null auto_increment primary key, `conversation_id` bigint unsigned not null, `sender_id` bigint unsigned not null, `body` longtext null, `attachments` json null, `message_type` varchar(30) not null default 'text', `reply_to_id` bigint unsigned null, `reactions_count` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `chat_messages` add constraint `chat_messages_conversation_id_foreign` foreign key (`conversation_id`) references `conversations` (`id`) on delete cascade;

alter table `chat_messages` add constraint `chat_messages_sender_id_foreign` foreign key (`sender_id`) references `users` (`id`) on delete cascade;

alter table `chat_messages` add constraint `chat_messages_reply_to_id_foreign` foreign key (`reply_to_id`) references `chat_messages` (`id`) on delete cascade;

alter table `chat_messages` add index `chat_messages_conversation_id_created_at_index`(`conversation_id`, `created_at`);

create table `monetization_eligibility` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `is_eligible` tinyint(1) not null default '0', `content_monetization` tinyint(1) not null default '0', `fan_subscriptions` tinyint(1) not null default '0', `stars_enabled` tinyint(1) not null default '0', `followers_count` int unsigned not null default '0', `content_count` int unsigned not null default '0', `engagement_score` int unsigned not null default '0', `country` varchar(60) null, `rejection_reason` text null, `approved_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `monetization_eligibility` add constraint `monetization_eligibility_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `monetization_eligibility` add index `monetization_eligibility_user_id_index`(`user_id`);

create table `monetization_earnings` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `earning_category` varchar(40) not null, `source_type` varchar(255) not null, `source_id` bigint unsigned not null, `amount` decimal(10, 2) not null default '0', `currency` varchar(10) not null default 'USD', `views_count` int unsigned not null default '0', `stars_count` int unsigned not null default '0', `metadata` text null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `monetization_earnings` add constraint `monetization_earnings_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `monetization_earnings` add index `monetization_earnings_source_type_source_id_index`(`source_type`, `source_id`);

alter table `monetization_earnings` add index `monetization_earnings_user_id_earning_category_index`(`user_id`, `earning_category`);

create table `content_subscriptions` (`id` bigint unsigned not null auto_increment primary key, `subscriber_id` bigint unsigned not null, `creator_id` bigint unsigned not null, `monthly_amount` decimal(10, 2) not null default '0', `status` enum('active', 'cancelled', 'expired', 'paused') not null default 'active', `started_at` timestamp null, `ends_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `content_subscriptions` add constraint `content_subscriptions_subscriber_id_foreign` foreign key (`subscriber_id`) references `users` (`id`) on delete cascade;

alter table `content_subscriptions` add constraint `content_subscriptions_creator_id_foreign` foreign key (`creator_id`) references `users` (`id`) on delete cascade;

alter table `content_subscriptions` add unique `content_subscriptions_subscriber_id_creator_id_unique`(`subscriber_id`, `creator_id`);

create table `content_stars` (`id` bigint unsigned not null auto_increment primary key, `sender_id` bigint unsigned not null, `receiver_id` bigint unsigned not null, `starable_type` varchar(255) not null, `starable_id` bigint unsigned not null, `stars_count` int unsigned not null default '1', `message` text null, `monetary_value` decimal(10, 4) not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `content_stars` add constraint `content_stars_sender_id_foreign` foreign key (`sender_id`) references `users` (`id`) on delete cascade;

alter table `content_stars` add constraint `content_stars_receiver_id_foreign` foreign key (`receiver_id`) references `users` (`id`) on delete cascade;

alter table `content_stars` add index `content_stars_starable_type_starable_id_index`(`starable_type`, `starable_id`);

alter table `content_stars` add index `content_stars_receiver_id_starable_type_index`(`receiver_id`, `starable_type`);

alter table `users` add `trial_started_at` timestamp null after `activated_at`;

alter table `users` add `trial_ends_at` timestamp null after `trial_started_at`;

alter table `users` add `cover_image` varchar(255) null after `image`;

alter table `users` add `followers_count` int unsigned not null default '0' after `total_earned`;

alter table `users` add `following_count` int unsigned not null default '0' after `followers_count`;

alter table `users` add `posts_count` int unsigned not null default '0' after `following_count`;

alter table `users` add `account_privacy` enum('public', 'private') not null default 'public' after `posts_count`;

alter table `users` add `monetization_enabled` tinyint(1) not null default '0' after `account_privacy`;

alter table `users` add index `users_trial_ends_at_index`(`trial_ends_at`);

create table `kyc_submissions` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `full_name` varchar(150) not null, `id_type` varchar(60) not null default 'national_id', `id_number` varchar(100) not null, `date_of_birth` date not null, `country` varchar(80) null, `address` varchar(255) null, `document_front` varchar(255) null, `document_back` varchar(255) null, `selfie` varchar(255) null, `status` enum('pending', 'approved', 'rejected') not null default 'pending', `rejection_reason` text null, `reviewed_by` bigint unsigned null, `reviewed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `kyc_submissions` add constraint `kyc_submissions_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `kyc_submissions` add constraint `kyc_submissions_reviewed_by_foreign` foreign key (`reviewed_by`) references `admins` (`id`) on delete set null;

alter table `kyc_submissions` add index `kyc_submissions_user_id_status_index`(`user_id`, `status`);

create table `verification_badges` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `verifiable_type` varchar(255) not null, `verifiable_id` bigint unsigned not null, `full_name` varchar(150) null, `government_id_path` varchar(255) null, `verification_code` varchar(255) null, `category` enum('individual', 'business', 'organization', 'public_figure') not null default 'individual', `monthly_fee` decimal(8, 2) not null default '5', `valid_from` timestamp null, `id_type` varchar(60) not null default 'national_id', `id_number` varchar(100) null, `document_path` varchar(255) null, `selfie_path` varchar(255) null, `status` enum('pending', 'verified', 'approved', 'rejected', 'expired', 'revoked') not null default 'pending', `rejection_reason` text null, `payment_reference` varchar(255) null, `amount_paid` decimal(10, 2) not null default '5', `paid_at` timestamp null, `valid_until` timestamp null, `expired_at` timestamp null, `reviewed_by` bigint unsigned null, `reviewed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `verification_badges` add constraint `verification_badges_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `verification_badges` add index `verification_badges_verifiable_type_verifiable_id_index`(`verifiable_type`, `verifiable_id`);

alter table `verification_badges` add constraint `verification_badges_reviewed_by_foreign` foreign key (`reviewed_by`) references `admins` (`id`) on delete set null;

alter table `verification_badges` add index `verification_badges_user_id_status_index`(`user_id`, `status`);

alter table `verification_badges` add index `verification_badges_valid_until_index`(`valid_until`);

create table `stories` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `media_type` enum('image', 'video', 'text') not null default 'image', `media_path` varchar(255) null, `caption` text null, `background_color` varchar(20) null, `views_count` int unsigned not null default '0', `expires_at` timestamp not null, `is_pinned` tinyint(1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null);

alter table `stories` add constraint `stories_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `stories` add index `stories_expires_at_index`(`expires_at`);

alter table `stories` add index `stories_user_id_index`(`user_id`);

create table `story_views` (`id` bigint unsigned not null auto_increment primary key, `story_id` bigint unsigned not null, `user_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `story_views` add constraint `story_views_story_id_foreign` foreign key (`story_id`) references `stories` (`id`) on delete cascade;

alter table `story_views` add constraint `story_views_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `story_views` add unique `story_views_story_id_user_id_unique`(`story_id`, `user_id`);

create table `chat_reactions` (`id` bigint unsigned not null auto_increment primary key, `chat_message_id` bigint unsigned not null, `user_id` bigint unsigned not null, `emoji` varchar(12) not null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `chat_reactions` add constraint `chat_reactions_chat_message_id_foreign` foreign key (`chat_message_id`) references `chat_messages` (`id`) on delete cascade;

alter table `chat_reactions` add constraint `chat_reactions_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `chat_reactions` add unique `chat_reactions_chat_message_id_user_id_emoji_unique`(`chat_message_id`, `user_id`, `emoji`);

create table `sponsored_ads` (`id` bigint unsigned not null auto_increment primary key, `advertiser_id` bigint unsigned not null, `title` varchar(191) not null, `description` text null, `link_url` varchar(255) null, `ad_type` enum('image', 'video', 'text', 'link') not null default 'image', `media_path` varchar(255) null, `cta_button` varchar(60) null, `cost_per_click` decimal(8, 4) not null default '0.02', `budget` decimal(12, 2) not null default '0', `amount_spent` decimal(12, 2) not null default '0', `impressions` int unsigned not null default '0', `clicks` int unsigned not null default '0', `views` int unsigned not null default '0', `status` enum('draft', 'pending_review', 'approved', 'rejected', 'paused', 'completed', 'expired', 'budget_exhausted') not null default 'pending_review', `rejection_reason` text null, `starts_at` timestamp null, `ends_at` timestamp null, `reviewed_by` bigint unsigned null, `reviewed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `sponsored_ads` add constraint `sponsored_ads_advertiser_id_foreign` foreign key (`advertiser_id`) references `users` (`id`) on delete cascade;

alter table `sponsored_ads` add constraint `sponsored_ads_reviewed_by_foreign` foreign key (`reviewed_by`) references `admins` (`id`) on delete set null;

alter table `sponsored_ads` add index `sponsored_ads_advertiser_id_status_index`(`advertiser_id`, `status`);

alter table `sponsored_ads` add index `sponsored_ads_status_index`(`status`);

create table `sponsored_ad_clicks` (`id` bigint unsigned not null auto_increment primary key, `sponsored_ad_id` bigint unsigned not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` varchar(255) null, `cost` decimal(8, 4) not null default '0.02', `created_at` timestamp null, `updated_at` timestamp null);

alter table `sponsored_ad_clicks` add constraint `sponsored_ad_clicks_sponsored_ad_id_foreign` foreign key (`sponsored_ad_id`) references `sponsored_ads` (`id`) on delete cascade;

alter table `sponsored_ad_clicks` add constraint `sponsored_ad_clicks_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

alter table `sponsored_ad_clicks` add index `sponsored_ad_clicks_sponsored_ad_id_ip_address_index`(`sponsored_ad_id`, `ip_address`);

create table `sponsored_ad_impressions` (`id` bigint unsigned not null auto_increment primary key, `sponsored_ad_id` bigint unsigned not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `sponsored_ad_impressions` add constraint `sponsored_ad_impressions_sponsored_ad_id_foreign` foreign key (`sponsored_ad_id`) references `sponsored_ads` (`id`) on delete cascade;

alter table `sponsored_ad_impressions` add constraint `sponsored_ad_impressions_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

alter table `sponsored_ad_impressions` add index `sponsored_ad_impressions_sponsored_ad_id_ip_address_index`(`sponsored_ad_id`, `ip_address`);

create table `push_device_tokens` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `token` varchar(255) not null, `device_id` varchar(100) null, `platform` enum('web', 'android', 'ios', 'pwa') not null default 'web', `provider` varchar(30) not null default 'firebase', `is_active` tinyint(1) not null default '1', `last_used_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `push_device_tokens` add constraint `push_device_tokens_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `push_device_tokens` add index `push_device_tokens_user_id_is_active_index`(`user_id`, `is_active`);

alter table `push_device_tokens` add index `push_device_tokens_token_index`(`token`);

create table `push_notifications_log` (`id` bigint unsigned not null auto_increment primary key, `type` varchar(60) not null default 'broadcast', `user_id` bigint unsigned null, `title` varchar(191) not null, `body` text null, `image_url` varchar(255) null, `slides` json null, `url` varchar(255) null, `icon` varchar(255) null, `badge` varchar(255) null, `data` text null, `provider` varchar(30) not null default 'firebase', `recipients` int unsigned not null default '0', `sent_count` int unsigned not null default '0', `failed_count` int unsigned not null default '0', `status` varchar(20) not null default 'sent', `error` text null, `provider_response` text null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `push_notifications_log` add constraint `push_notifications_log_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

create table `user_ip_logs` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned null, `ip_address` varchar(45) not null, `user_agent` varchar(255) null, `event` varchar(50) not null default 'login', `created_at` timestamp null, `updated_at` timestamp null);

alter table `user_ip_logs` add constraint `user_ip_logs_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

alter table `user_ip_logs` add index `user_ip_logs_ip_address_event_index`(`ip_address`, `event`);

alter table `user_ip_logs` add index `user_ip_logs_user_id_index`(`user_id`);

create table `anti_cheat_flags` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned null, `type` varchar(60) not null, `description` text null, `evidence` json null, `severity` varchar(20) not null default 'medium', `ip_address` varchar(45) null, `status` enum('open', 'reviewing', 'resolved', 'dismissed') not null default 'open', `resolved_by` bigint unsigned null, `resolved_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `anti_cheat_flags` add constraint `anti_cheat_flags_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

alter table `anti_cheat_flags` add constraint `anti_cheat_flags_resolved_by_foreign` foreign key (`resolved_by`) references `admins` (`id`) on delete set null;

alter table `anti_cheat_flags` add index `anti_cheat_flags_user_id_status_index`(`user_id`, `status`);

alter table `anti_cheat_flags` add index `anti_cheat_flags_type_index`(`type`);

create table `system_update_logs` (`id` bigint unsigned not null auto_increment primary key, `from_commit` varchar(60) null, `to_commit` varchar(60) null, `from_version` varchar(30) null, `to_version` varchar(30) null, `output` text null, `status` enum('started', 'success', 'failed') not null default 'started', `initiated_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null);

alter table `system_update_logs` add constraint `system_update_logs_initiated_by_foreign` foreign key (`initiated_by`) references `admins` (`id`) on delete set null;

create table `monetization_disable_logs` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `reason` text not null, `action` enum('disabled', 'enabled') not null default 'disabled', `admin_id` bigint unsigned null, `disabled_at` timestamp not null default CURRENT_TIMESTAMP, `created_at` timestamp null, `updated_at` timestamp null);

alter table `monetization_disable_logs` add constraint `monetization_disable_logs_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

alter table `monetization_disable_logs` add constraint `monetization_disable_logs_admin_id_foreign` foreign key (`admin_id`) references `admins` (`id`) on delete set null;

alter table `monetization_disable_logs` add index `monetization_disable_logs_user_id_index`(`user_id`);

alter table `users` add `account_type` enum('freelancer', 'advertiser', 'both') not null default 'freelancer' after `banned`;

alter table `users` add `kyc_status` enum('none', 'pending', 'approved', 'rejected') not null default 'none' after `account_type`;

alter table `users` add `kyc_approved_at` timestamp null after `kyc_status`;

alter table `users` add `verification_status` enum('none', 'pending', 'verified', 'expired', 'rejected') not null default 'none' after `kyc_approved_at`;

alter table `users` add `verification_valid_until` timestamp null after `verification_status`;

alter table `users` add `registration_ip` varchar(45) null after `verification_valid_until`;

alter table `users` add `monetization_disabled_reason` text null after `monetization_enabled`;

alter table `users` add `monetization_disabled_at` timestamp null after `monetization_disabled_reason`;

alter table `monetization_eligibility` add `paid_followers` int unsigned not null default '0' after `followers_count`;

alter table `monetization_eligibility` add `eligible_views` int unsigned not null default '0' after `paid_followers`;

alter table `monetization_eligibility` add `real_engagement` int unsigned not null default '0' after `eligible_views`;

alter table `monetization_eligibility` add `auto_enabled_at` timestamp null after `approved_at`;

alter table `task_categories` add `platform` varchar(60) null after `icon`;

alter table `app_settings` add `kyc_required_for_withdrawal` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `kyc_auto_approval` tinyint(1) not null default '0' after `accent_color`;

alter table `app_settings` add `verification_badge_fee` decimal(10, 2) not null default '5' after `accent_color`;

alter table `app_settings` add `verification_badge_duration_days` int not null default '30' after `accent_color`;

alter table `app_settings` add `verification_auto_approval` tinyint(1) not null default '0' after `accent_color`;

alter table `app_settings` add `ad_auto_approval` tinyint(1) not null default '0' after `accent_color`;

alter table `app_settings` add `ad_cost_per_click` decimal(8, 4) not null default '0.02' after `accent_color`;

alter table `app_settings` add `microjob_auto_approval` tinyint(1) not null default '0' after `accent_color`;

alter table `app_settings` add `withdrawal_auto_approval` tinyint(1) not null default '0' after `accent_color`;

alter table `app_settings` add `blog_auto_approval` tinyint(1) not null default '0' after `accent_color`;

alter table `app_settings` add `email_verification_mode` varchar(30) not null default 'auto' after `accent_color`;

alter table `app_settings` add `monetization_min_followers` int not null default '500' after `accent_color`;

alter table `app_settings` add `monetization_min_views` int not null default '1000' after `accent_color`;

alter table `app_settings` add `monetization_min_engagement` int not null default '1000' after `accent_color`;

alter table `app_settings` add `monetization_auto_enable` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `firebase_server_key` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_project_id` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_web_api_key` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_sender_id` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_auth_domain` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_storage_bucket` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_app_id` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_measurement_id` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_messaging_vapid_key` varchar(191) null after `accent_color`;

alter table `app_settings` add `firebase_config_json` text null after `accent_color`;

alter table `app_settings` add `onesignal_app_id` varchar(191) null after `accent_color`;

alter table `app_settings` add `onesignal_rest_api_key` varchar(191) null after `accent_color`;

alter table `app_settings` add `onesignal_safari_web_id` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_enabled` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `pwa_name` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_short_name` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_theme_color` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_background_color` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_display` varchar(191) not null default 'standalone' after `accent_color`;

alter table `app_settings` add `pwa_orientation` varchar(191) not null default 'any' after `accent_color`;

alter table `app_settings` add `pwa_icon_192` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_icon_512` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_apple_touch_icon` varchar(191) null after `accent_color`;

alter table `app_settings` add `pwa_custom_css` text null after `accent_color`;

alter table `app_settings` add `pwa_offline_enabled` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `pwa_start_url` varchar(191) not null default '/' after `accent_color`;

alter table `app_settings` add `sound_enabled` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `sound_like` varchar(191) null after `accent_color`;

alter table `app_settings` add `sound_comment` varchar(191) null after `accent_color`;

alter table `app_settings` add `sound_message` varchar(191) null after `accent_color`;

alter table `app_settings` add `sound_notification` varchar(191) null after `accent_color`;

alter table `app_settings` add `sound_send` varchar(191) null after `accent_color`;

alter table `app_settings` add `anti_cheat_enabled` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `one_account_per_ip` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `one_account_per_id` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `min_interaction_price` decimal(8, 4) not null default '0.01' after `accent_color`;

alter table `app_settings` add `github_repo` varchar(191) null after `accent_color`;

alter table `app_settings` add `github_branch` varchar(191) not null default 'main' after `accent_color`;

alter table `app_settings` add `auto_update_enabled` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `current_version` varchar(191) not null default '1.0.0' after `accent_color`;

alter table `app_settings` add `ai_features_enabled` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `content_moderation_ai` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `smart_feed_algorithm` tinyint(1) not null default '1' after `accent_color`;

alter table `app_settings` add `offling_page_enabled` tinyint(1) not null default '1' after `accent_color`;

alter table `blog_posts` modify `author_id` bigint unsigned null;

alter table `blog_posts` add `tags` varchar(500) null after `meta_keywords`;

alter table `blog_posts` add `meta_title` varchar(255) null after `meta_keywords`;

alter table `blog_posts` add `ratings_count` int unsigned not null default '0' after `shares_count`;

SET FOREIGN_KEY_CHECKS=1;
