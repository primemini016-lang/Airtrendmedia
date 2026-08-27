# Phase 2 & 3 — Views to Create

## Phase 2: User-facing views
- [x] user/kyc.blade.php
- [x] user/verification.blade.php
- [x] user/account-type.blade.php
- [x] user/sponsored-ads.blade.php
- [x] user/sponsored-ad-stats.blade.php
- [x] social/partials/stories-bar.blade.php (stories bar in feed)
- [x] social/partials/sponsored-feed.blade.php (sponsored ad in feed)
- [x] Update social/feed.blade.php (include stories bar + sponsored + publisher + sound)
- [x] Update social/monetization.blade.php (requirements progress)
- [x] Update social/chat.blade.php (typing + reactions)
- [x] public/js/action-sounds.js (sound player)
- [x] Action sound files (public/sounds/*.wav generated tones)
- [x] Homepage tabs connecting systems (welcome page update)
- [x] Full-screen blog pages (already full-screen hero design)

## Phase 3: Admin views
- [x] admin/kyc.blade.php
- [x] admin/kyc-show.blade.php
- [x] admin/verification.blade.php
- [x] admin/verification-show.blade.php
- [x] admin/sponsored-ads.blade.php
- [x] admin/push-settings.blade.php
- [x] admin/push-logs.blade.php
- [x] admin/monetization-management.blade.php
- [x] admin/anti-cheat.blade.php
- [x] admin/pwa-settings.blade.php
- [x] Update admin sidebar nav (add links to new admin pages)
- [x] Update admin/dashboard.blade.php (add pending counts for KYC/verification/ads)

## Phase 4: Testing & Deploy
- [x] php artisan route:list + view:compile check (373 routes, all compile)
- [x] php artisan migrate:fresh --seed --force (passes, 82 subcategories with social icons)
- [ ] Smoke test key pages (curl)
- [ ] .env + .env.example finalize
- [ ] Push to GitHub
- [ ] Downloadable zip
