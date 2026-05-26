# TubeYou

A YouTube-inspired video platform built with PHP (MVC), MariaDB, and vanilla JS.

---

## 🌐 Live Demo
[TubeYou](https://tubeyou-spvl.onrender.com)

## 🚀 Overview
TubeYou is a full-stack web application replicating core YouTube features, built from scratch without frameworks.  
The project focuses on clean architecture (MVC), performance, and understanding how real platforms work internally.

---

## ✨ Features
- Video upload & streaming (HTTP range support)
- Like / dislike system
- Subscriptions
- Advanced comment system:
  - replies  
  - likes  
  - pinning  
  - editing  
- User profiles (avatar, banner, bio)
- Email verification & password reset (Resend API)
- Dark mode
- Full-text search
- Algorithm-based feed scoring

---

## 🛠 Tech Stack
- PHP 8.x (no framework)
- MariaDB
- Vanilla JavaScript
- Resend API (email)
- vlucas/phpdotenv
- Docker + Docker Compose

---

## 📸 Screenshots

### Home
![Home](docs/screenshots/home.png)

### Video Page
![Video](docs/screenshots/video.png)

### Profile
![Profile](docs/screenshots/profile.png)

### Comments
![Comments](docs/screenshots/comments.png)

---

## ⚙️ Setup

### Install
```
composer install
```

```
cp .env.example .env
```

Fill in credentials, then:

```
php database/database.php
```

---

## 🌐 Local Development (XAMPP)

### .htaccess
```
Options -MultiViews
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

### Enable mod_rewrite
```
C:\xampp\apache\conf\httpd.conf
```

Uncomment:
```
LoadModule rewrite_module modules/mod_rewrite.so
```

### VirtualHost
```
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

```
<VirtualHost *:80>
    ServerName tubeyou.local
    DocumentRoot "PATH_TO_PROJECT\public"

    <Directory "PATH_TO_PROJECT\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Hosts file
```
C:\Windows\System32\drivers\etc\hosts
```

```
127.0.0.1 tubeyou.local
```

Restart Apache and open:
```
http://tubeyou.local
```

---

## 🌍 Access from other devices

### Option A — Router DNS (recommended)
```
tubeyou.local → YOUR_LOCAL_IP
```

### Option B — Hosts (per device)
```
YOUR_LOCAL_IP tubeyou.local
```

---

## 📂 Project Structure
```
/controllers   — HTTP handlers
/models        — Repository classes
/views         — PHP templates
/services      — MailService etc.
/helpers       — utilities (csrf, formatNumber, etc.)
/database      — schema, seed, reset
/public        — entry point + assets
```

---

## 🧠 Architecture
- MVC pattern (custom implementation)
- Stateless request handling
- PDO-based data layer
- Service layer for external integrations
- Helper-based utilities

---

## 🗺 Roadmap

### 🔥 Core Improvements
- [x] Video processing (thumbnails, compression)
- [x] Better recommendation algorithm
- [x] Watch history tracking
- [x] Notifications system

### 👤 User Features
- [x] Playlists
- [x] Watch later
- [x] Channel customization
- [x] User settings panel

### 💬 Social
- [ ] Comment mentions (@user)
- [x] Comment sorting (top/new)
- [ ] Community posts

### ⚡ Performance
- [x] File-based query cache (homepage feed 60s, suggested videos 5min)
- [ ] Lazy loading improvements
- [x] N+1 query fix in Studio (single JOIN replaces per-video queries)

### 🔐 Security
- [x] Rate limiting (file-based, per IP)
- [x] CSRF protection on all POST endpoints
- [x] Input sanitization (strip_tags + prepared statements)
- [x] XSS fix — HTML escaping in JS optimistic comment rendering
- [x] MIME validation via finfo (avatar, banner, video upload)
- [x] X-Forwarded-For spoofing protection in RateLimiter
- [x] Global exception handler (500 page + error_log)
- [x] Shared auth middleware (no more duplicated requireAuth)
- [x] Remove unused phpmailer dependency
- [x] Directory listing disabled on uploads (Options -Indexes)
- [x] Session fixation fix (session_regenerate_id on login)
- [x] Referrer-Policy security header

### 🌍 Deployment
- [x] Production deployment guide (Render + Railway)
- [x] Docker setup
- [x] LAN hosting guide (Windows + XAMPP, docs/instrukcje.md)
- [ ] CI/CD pipeline

---

## ⚠️ Notes
- Local domain requires hosts/DNS setup  
- Apache must allow external connections for LAN access  
- This project is for educational purposes  

---

## 📜 License
MIT License — see LICENSE file for details.
