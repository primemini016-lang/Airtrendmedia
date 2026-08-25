# Airtrendmedia — All-in-One Platform Build Plan

## Phase 0: Environment & Base Verification
- [x] Explore uploaded zip + GitHub repo
- [x] Install PHP 8.3 + extensions + Composer
- [x] Set up working dir from extracted (most complete) version
- [x] Fix AppServiceProvider syntax error + boot the app
- [x] Configure SQLite test DB + run migrations + seeders
- [x] Verify base platform boots (installer / routes load)
- [x] Create extended-features migration + 14 models (KYC, Verification, Story, Reactions, SponsoredAds, Push, AntiCheat, etc.)

## Phase 1: Core Missing Features (Backend controllers + services)
- [x] KYC controller: user submit + admin approve (auto/manual) + view docs/images
- [x] Verification badge controller: gov ID + $5 monthly (30-day), auto-expire, admin manage + approve
- [x] Advertiser/Freelancer account-type switch controller + service
- [x] Anti-cheat service: one account per IP + per ID, view manipulation prevention
- [x] Story controller: post + view stories (Facebook style)
- [x] Chat typing indicator endpoint + chat reactions (unlimited emojis)
- [x] Sponsored ads controller: advertiser purchase ($0.02/click) + admin review + stats + feed display
- [x] Push/notifications service (Firebase/OneSignal) + admin settings save
- [ ] PWA: manifest + service worker + offline screen + admin settings (controller done, need files)
- [x] System update manager: GitHub auto-update logic (already exists in AdminController)
- [x] Monetization requirements service: 500 paid followers + 1000 views + auto-enable + admin disable-with-reason
- [ ] Action sounds: JS player + reference sound files
- [ ] Task category seeder update with exact social media interaction icons
- [x] Register all new routes in routes/web.php

## Phase 2: Frontend & Views
- [ ] Homepage with feature/benefit/use descriptions + tabs connecting systems
- [ ] Monetization dashboard + statistics + progress
- [ ] "What's on your mind?" full Facebook publishing
- [ ] Like/comment/share/view counts, reactions
- [ ] Full-screen natural blog pages
- [ ] KYC submission page + verification badge page
- [ ] Stories bar + viewer
- [ ] Sponsored ad display in feed
- [ ] Account-type switch UI
- [ ] Verify all social views render

## Phase 3: Admin "God-mode" Management
- [ ] Admin KYC management (view docs, approve/deny, auto/manual toggle)
- [ ] Admin verification badge management (approve/deny, auto/manual toggle)
- [ ] Admin monetization management (disable with reason, view earnings)
- [ ] Admin sponsored ads management (review, approve/deny, extend CPC, stats)
- [ ] Admin view all user messages (chats)
- [ ] Admin push/Firebase settings page
- [ ] Admin PWA settings page
- [ ] Admin system update page
- [ ] Admin all auto/manual approval toggles

## Phase 4: Testing & Deployment
- [ ] Run php artisan route:list + syntax check all controllers
- [ ] Test install/registration/admin/social/microjob/switch
- [ ] Add .env + .env.example
- [ ] Push everything to GitHub
- [ ] Produce downloadable zip
