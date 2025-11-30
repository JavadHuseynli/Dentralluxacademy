# 🦷 Dental Academy Admin Panel

<div align="center">

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)
![Security](https://img.shields.io/badge/security-MFA%20%7C%20CSRF%20%7C%20Rate%20Limiting-success)

**Professional admin panel for managing dental courses, instructors, registrations, and certifications**

[Features](#-features) •
[Quick Start](#-quick-start) •
[Security](#-security) •
[Docker](#-docker-deployment) •
[API Docs](#-api-documentation)

</div>

---

## 🎯 Overview

Dental Academy Admin Panel is a comprehensive management system for dental education institutions. Built with modern security practices, it provides Multi-Factor Authentication, CSRF protection, rate limiting, and a clean RESTful API.

### Key Highlights

- ✅ **Multi-Factor Authentication** - TOTP (Google Authenticator) + Backup Codes
- ✅ **CSRF Protection** - Token-based request validation
- ✅ **Rate Limiting** - Brute force protection (5 attempts → 15min lockout)
- ✅ **Docker Ready** - One-command deployment with Nginx + PHP-FPM
- ✅ **Multi-Language** - Azerbaijani, English, Russian
- ✅ **RESTful API** - Clean, documented endpoints
- ✅ **QR Certificates** - Digital badges with verification

---

## ✨ Features

### 🔐 Security
- Google Authenticator (TOTP - RFC 6238)
- 10 backup recovery codes (single-use)
- CSRF token validation
- Rate limiting (IP + browser fingerprint)
- 8-hour session auto-expire
- Security event logging

### 📚 Course Management
- Multi-language course creation
- Multiple instructors per course
- Auto-hide expired courses
- Seat availability tracking
- Image upload with optimization

### 👨‍⚕️ Instructor Management
- RESTful API (GET, POST, PUT, DELETE)
- Profile management with photos
- Specialty categorization

### 🎓 Certificates
- QR code generation
- Printable badges
- PNG download
- Unique verification IDs

### 📧 Mailbox Integration
- IMAP email viewing (Hostinger)
- Inbox, Sent, Drafts, Spam folders
- HTML and plain text support
- Unread email count
- Real-time email refresh

---

## 🚀 Quick Start

### Local Development

```bash
# Clone repository
git clone https://github.com/yourusername/dental-academy.git
cd dental-academy

# Create directories
mkdir -p logs data uploads

# Set permissions
chmod 777 logs data uploads

# Start PHP server
php -S localhost:8000

# Open browser
http://localhost:8000
```

### Default Credentials

```
URL: http://localhost:8000/admin-login.html
Username: admin
Password: dentalux2025!
2FA: Google Authenticator (setup on first login)
```

### Mailbox Setup

```bash
# Copy mail config template
cp api/mail-config.example.php api/mail-config.php

# Edit with your Hostinger credentials
nano api/mail-config.php

# Update these values:
# - username: your-email@yourdomain.com
# - password: your-mail-password

# Access mailbox
http://localhost:8000/admin-mailbox.html
```

**Note**: PHP IMAP extension required. Install with:
- **Mac**: `brew install php-imap` or `pecl install imap`
- **Ubuntu**: `sudo apt install php-imap`
- **Windows**: Uncomment `;extension=imap` in `php.ini`

---

## 🐳 Docker Deployment

### Using Docker Compose (Recommended)

```bash
# Start containers
docker-compose up -d

# Access application
http://localhost:8080

# View logs
docker-compose logs -f web

# Stop containers
docker-compose down
```

### Manual Docker

```bash
# Build image
docker build -t dental-academy:2.0.0 .

# Run container
docker run -d \
  -p 8080:80 \
  -v $(pwd)/logs:/var/www/html/logs \
  --name dental-web \
  dental-academy:2.0.0
```

---

## 🔒 Security

### Authentication Flow

```
1. Username + Password
2. Rate limiting check (max 5 attempts)
3. TOTP prompt (Google Authenticator)
4. 6-digit code validation (30-sec window)
5. Session creation (8-hour expiry)
6. CSRF token for all requests
```

### Setup Google Authenticator

1. Admin Dashboard → Settings → Security
2. Click "Enable" on Google Authenticator
3. Scan QR with mobile app
4. Enter 6-digit code to verify
5. Generate backup codes

### CSRF Usage

```javascript
// Get token
const res = await fetch('/api/csrf.php');
const {token} = await res.json();

// Use in requests
await fetch('/api/endpoint.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
    },
    body: JSON.stringify({csrf_token: token, ...data})
});
```

---

## 📡 API Documentation

### TOTP (Google Authenticator)

```bash
# Generate secret
GET /api/totp.php?action=generate

# Verify code
POST /api/totp.php?action=verify
{"secret": "JBSWY3DPEHPK3PXP", "code": "123456"}
```

### Backup Codes

```bash
# Generate codes
POST /api/backup-codes.php?action=generate
{"username": "admin"}

# Verify code
POST /api/backup-codes.php?action=verify
{"username": "admin", "code": "1234-5678-9012-3456"}
```

### Rate Limiter

```bash
# Check status
GET /api/rate-limiter.php?action=check

# Record attempt
POST /api/rate-limiter.php?action=record
{"success": false}
```

### CSRF

```bash
# Get token
GET /api/csrf.php
```

### Instructors

```bash
# List all
GET /api/instructors.php

# Get single
GET /api/instructors.php?id=1

# Create
POST /api/instructors.php
{"name": "Dr. John", "specialty": "Orthodontics"}

# Update
PUT /api/instructors.php?id=1

# Delete
DELETE /api/instructors.php?id=1
```

### Emails (IMAP)

```bash
# List emails from folder
GET /api/fetch-emails.php?action=list&folder=INBOX&limit=50

# Response:
{
  "success": true,
  "total": 125,
  "folder": "INBOX",
  "emails": [
    {
      "id": 1,
      "subject": "Welcome",
      "from": {"name": "John", "email": "john@example.com"},
      "date": "2025-01-15 10:30",
      "isSeen": false,
      "preview": "..."
    }
  ]
}

# Read single email
GET /api/fetch-emails.php?action=read&id=1&folder=INBOX

# Response:
{
  "success": true,
  "emails": {
    "id": 1,
    "subject": "Welcome",
    "body": "Full email content...",
    "htmlBody": "<html>...</html>",
    "isHtml": true
  }
}
```

---

## 📁 Project Structure

```
dental-academy/
├── api/                    # Backend APIs
│   ├── totp.php            # Google Authenticator
│   ├── backup-codes.php    # Backup codes
│   ├── rate-limiter.php    # Brute force protection
│   ├── csrf.php            # CSRF tokens
│   ├── instructors.php     # Instructor CRUD
│   ├── fetch-emails.php    # IMAP email fetching
│   ├── mail-config.php     # Mail credentials (gitignored)
│   ├── mail-config.example.php  # Mail config template
│   └── upload.php          # Image upload
├── docker/                 # Docker configs
│   ├── nginx/              # Nginx config
│   ├── php/                # PHP-FPM config
│   └── supervisor/         # Process manager
├── data/                   # JSON storage
├── logs/                   # Application logs
├── admin-login.html        # Login (2FA)
├── admin-dashboard.html    # Main dashboard
├── admin-mailbox.html      # Mailbox viewer (IMAP)
├── admin-security-settings.html  # Security config
├── badge-generator.html    # Certificate generator
├── Dockerfile              # Docker image
├── docker-compose.yml      # Docker Compose
└── README.md               # This file
```

---

## ⚙️ Configuration

### Environment

Create `.env` (optional):

```env
APP_ENV=production
DB_HOST=localhost
SESSION_LIFETIME=28800
CSRF_TOKEN_EXPIRY=3600
RATE_LIMIT_ATTEMPTS=5
```

### PHP

Adjust `docker/php/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 10M
max_execution_time = 300
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing`)
5. Open Pull Request

---

## 📝 License

MIT License - see [LICENSE](LICENSE) file

---

## 📞 Support

- **Documentation**: [SECURITY_GUIDE.md](SECURITY_GUIDE.md)
- **Issues**: [GitHub Issues](https://github.com/yourusername/dental-academy/issues)
- **Email**: support@dentalacademy.az

---

<div align="center">

**Built with ❤️ for Dental Education**

⭐ Star us on GitHub!

</div>
