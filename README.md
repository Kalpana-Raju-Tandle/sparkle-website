# Sparkle Constructions — website

PHP 8+ (PDO SQLite). No database server, no Composer, no build step.

## Run locally
```bash
php -d upload_max_filesize=16M -d post_max_size=64M -S localhost:8000 router.php
```
Open http://localhost:8000 — the database (`data/sparkle.sqlite`) and the starter content are created automatically on first visit.

## Deploy (cPanel / shared hosting)
1. Upload the whole `website/` folder contents to `public_html/` (or a sub-folder).
2. Needs PHP 8.0+ with `pdo_sqlite`, `fileinfo` (and `gd` recommended — resizes uploads automatically).
3. Make `data/` and `uploads/` writable (chmod 755/775).
4. `.htaccess` blocks `data/` and `includes/` and stops PHP running inside `uploads/`. On Nginx, add equivalent `deny` rules.

## Admin panel — `/admin/`
Starter login: **admin / Sparkle@2026** → change it immediately in *Settings → Change admin password*.
- **Photos & Videos** — drag & drop photos or videos (MP4/MOV/WebM, sent in pieces so large clips work), choose Construction-journey stage / Completed / 3D Designs; live instantly.
- **Projects** — titles, type, featured flag (no locations shown).
- **Enquiries** — contact-form and estimator leads (also emailed if an email is set).
- **Settings** — phone, WhatsApp, email, **Pandavapura primary address**, package rates (₹1,999 start), founder & designer profiles.

## Structure
`index, about, services, projects, gallery, pricing, contact .php` · `includes/` (config, db, helpers, header/footer) · `admin/` · `assets/` · `uploads/`
