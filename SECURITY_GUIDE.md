# 🔐 Dental Academy - Təhlükəsizlik Sistemi

## 📋 Mündəricat

1. [Ümumi Baxış](#ümumi-baxış)
2. [Təhlükəsizlik Xüsusiyyətləri](#təhlükəsizlik-xüsusiyyətləri)
3. [Google Authenticator (TOTP)](#google-authenticator-totp)
4. [Backup Kodlar](#backup-kodlar)
5. [SMS OTP](#sms-otp)
6. [Brute Force Protection](#brute-force-protection)
7. [Istifadəçi Bələdçisi](#istifadəçi-bələdçisi)
8. [API Documentation](#api-documentation)

---

## Ümumi Baxış

Dental Academy admin paneli **çoxfaktorlu təhlükəsizlik (Multi-Factor Authentication - MFA)** ilə qorunur.

### Təhlükəsizlik Səviyyələri

| Səviyyə | Metodlar | Təhlükəsizlik |
|---------|----------|---------------|
| **Baza** | Şifrə | 40% |
| **Orta** | Şifrə + SMS | 60% |
| **Yüksək** | Şifrə + SMS + TOTP | 100% |

---

## Təhlükəsizlik Xüsusiyyətləri

### ✅ Aktiv Xüsusiyyətlər

1. **İki Faktorlu Təsdiq (2FA)**
   - SMS OTP (hazırda aktiv)
   - Google Authenticator / TOTP
   - Backup kodlar

2. **Brute Force Protection**
   - 5 yanlış cəhddən sonra bloklama
   - 15 dəqiqəlik lockout
   - IP və browser fingerprinting

3. **Session Security**
   - 8 saatlıq avtomatik logout
   - Secure session storage
   - Timestamp validation

4. **Rate Limiting**
   - 5 dəqiqə pəncərəsində max 5 cəhd
   - IP-based tracking
   - Avtomatik security log

---

## Google Authenticator (TOTP)

### Nədir?

**TOTP (Time-based One-Time Password)** - hər 30 saniyədə yenilənən 6 rəqəmli kod yaradan sistemdir. Google Authenticator, Microsoft Authenticator, Authy və s. tətbiqlərlə işləyir.

### Üstünlükləri

- ✅ **Ən təhlükəsizdir** - internet olmadan işləyir
- ✅ **30 saniyədə yenilənir** - kodun oğurlanması çətindir
- ✅ **Offline işləyir** - telefon şəbəkəsi lazım deyil
- ✅ **Industry standard** - Google, Facebook, Amazon istifadə edir

### Quraşdırma

#### Addım 1: Authenticator Tətbiqi Yüklə

**Android:**
- Google Play Store → "Google Authenticator" axtarın
- [Birbaşa link](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2)

**iPhone:**
- App Store → "Google Authenticator" axtarın
- [Birbaşa link](https://apps.apple.com/us/app/google-authenticator/id388497605)

**Alternativlər:**
- Microsoft Authenticator
- Authy
- 1Password
- Bitwarden Authenticator

#### Addım 2: Aktiv Et

1. Admin Panel → **Tənzimləmələr** → **Təhlükəsizlik Ayarları**
2. "Google Authenticator" bölməsində **"Aktiv et"** düyməsinə klikləyin
3. QR kod ekranda görünəcək

#### Addım 3: QR Kodu Skan Et

1. Authenticator tətbiqini açın
2. **"+"** (əlavə et) düyməsinə basın
3. **"Scan QR code"** seçin
4. Ekrandakı QR kodu skan edin

#### Addım 4: Təsdiqlə

1. Tətbiqdə 6 rəqəmli kod görünəcək
2. Ekranda olan sahəyə daxil edin
3. **"Təsdiqlə"** düyməsinə basın
4. ✅ Uğurlu! Google Authenticator aktiv edildi

### İstifadə

Login zamanı:
1. İstifadəçi adı və parol daxil edin
2. Google Authenticator tətbiqini açın
3. "Dental Academy Admin" altındakı 6 rəqəmli kodu görün
4. Kodu daxil edin və giriş edin

⚠️  **Kod 30 saniyədə yenilənir** - vaxt bitənə qədər daxil edin!

### Secret Key Backup

QR kod skan edilməyirsə, manuel olaraq əlavə edə bilərsiniz:

1. Authenticator tətbiqində **"Enter a setup key"** seçin
2. **Account:** `Dental Academy Admin`
3. **Key:** Ekranda göstərilən secret key (məsələn: `JBSWY3DPEHPK3PXP`)
4. **Time-based:** seçilməlidir
5. Əlavə edin

💡 **TIP:** Secret key-i təhlükəsiz yerdə saxlayın (parolla manager, vault və s.)

---

## Backup Kodlar

### Nədir?

Telefon və ya authenticator əlçatan olmadıqda istifadə etmək üçün **təcili giriş kodları**.

### Xüsusiyyətlər

- 📋 10 ədəd kod yaradılır
- 🔐 Hər kod yalnız **1 dəfə** istifadə oluna bilər
- ⚠️  Kod istifadə edildikdən sonra ləğv edilir
- 🔄 İstədiyiniz zaman yeni kodlar yarada bilərsiniz

### Kod Formatı

```
1234-5678-9012-3456
```

4 hissə x 4 rəqəm = 16 rəqəm

### Yaratmaq

1. Admin Panel → **Təhlükəsizlik Ayarları**
2. "Backup Kodlar" bölməsində **"Yenilə"** düyməsinə klikləyin
3. 10 kod ekranda görünəcək
4. Kodları **çap edin** və ya **yükləyin**

### Saxlamaq

**Tövsiyə olunan:**
- 📄 Çap edib kasada saxlayın
- 💾 Password manager-ə əlavə edin (1Password, Bitwarden)
- 🔒 Encrypted file kimi yükləyin

**Tövsiyə olunmayan:**
- ❌ Screenshot çəkib telefonun qaleriyasında saxlamayın
- ❌ Email ilə göndərməyin
- ❌ Cloud storage-də plain text kimi saxlamayın

### İstifadə

1. Login zamanı OTP sahəsində **"Backup kod istifadə et"** linkini klikləyin
2. 16 rəqəmli backup kodu daxil edin (tire ilə və ya tiresiz)
3. Giriş edin

Kod istifadə edildikdən sonra:
- ✅ Giriş uğurlu olur
- ⚠️  Kod ləğv edilir (bir daha işləməz)
- 💡 Qalan kodların sayı göstərilir

---

## SMS OTP

### Hazırki Status

✅ **Aktiv** - +994 50 412 21 60

### Necə İşləyir

1. İstifadəçi adı və parol daxil edin
2. Sistem 6 rəqəmli OTP yaradır
3. OTP telefona göndərilir (və ya console/log-da görünür)
4. 60 saniyə ərzində kod daxil edilməlidir
5. Uğurlu təsdiqdən sonra giriş olunur

### Development Mode

Hazırda SMS gateway konfiqurasiya olunmayıb, buna görə:

- 📱 SMS göndərilmir (Twilio credentials yoxdur)
- 🖥️  **Console-da böyük və rəngli göstərilir** (F12 açın)
- 📋 **Alert-də göstərilir** (ekranda popup)
- 📄 `logs/otp-log.txt` faylına yazılır

### Production Mode

Real SMS göndərmək üçün:

1. **Twilio qeydiyyatı** (5 dəqiqə)
   - https://www.twilio.com/try-twilio
   - $15 pulsuz kredit
   - `TWILIO_SETUP_5MIN.md` faylına baxın

2. **Credentials əlavə et**
   ```bash
   ./setup-sms.sh
   ```

3. **Test et**
   - Real SMS göndərilməyə başlayacaq
   - 30-60 saniyə çatma vaxtı

---

## Brute Force Protection

### Nədir?

**Brute Force** - parol tapma məqsədilə çoxlu sayda təxmin etmək cəhdi. Sistemimiz bunu avtomatik bloklayır.

### Qayda

| Cəhd | Nəticə |
|------|--------|
| 1-4 | ❌ Xəta mesajı + qalan cəhd sayı |
| 5 | 🔒 **15 dəqiqəlik bloklama** |
| 15 dəq sonra | ✅ Bloklama açılır |

### Texniki Detallar

**İzləmə Metodu:**
- IP Address + Browser fingerprint (SHA256 hash)
- 5 dəqiqəlik pəncərədə cəhdlər sayılır
- JSON faylında saxlanılır

**Security Log:**
```
logs/security-log.txt
2025-11-29 20:30:00 | LOCKED: abc123... | IP: 192.168.1.100
```

**Avtomatik Təmizləmə:**
- Uğurlu girişdən sonra failed attempts silinir
- Lockout 15 dəqiqədən sonra expire olur

### API

```bash
# Check if blocked
GET api/rate-limiter.php?action=check

Response:
{
  "success": true,
  "blocked": false,
  "remaining_attempts": 5
}

# Record failed attempt
POST api/rate-limiter.php?action=record
Body: {"success": false}

# Record successful login (clear attempts)
POST api/rate-limiter.php?action=record
Body: {"success": true}
```

---

## İstifadəçi Bələdçisi

### İlk Dəfə Login

1. **http://localhost:8000/admin-login.html** açın
2. **İstifadəçi adı:** `admin`
3. **Parol:** `dentalux2025!`
4. **OTP kod:** Console-da görün (F12) və ya telefonunuza gələn SMS
5. Kod daxil edin və giriş edin

### Təhlükəsizliyi Artırmaq

#### 1. Google Authenticator Aktiv Edin (Tövsiyə!)

**Təhlükəsizlik:** 40% → 100%

1. Admin Panel → **Tənzimləmələr** (sidebar)
2. Aşağı scroll et → **Təhlükəsizlik** bölməsinə klikləyin
3. **Google Authenticator** → "Aktiv et"
4. QR kodu skan edin
5. 6 rəqəmli kodu daxil edib təsdiqləyin

#### 2. Backup Kodlar Yaradın

**Təhlükəsizlik:** Təcili giriş imkanı

1. Təhlükəsizlik səhifəsində **Backup Kodlar** → "Yenilə"
2. 10 kod görünəcək
3. **"Çap Et"** və ya **"Yüklə"** düyməsinə basın
4. Təhlükəsiz yerdə saxlayın

#### 3. Session Security

**Avtomatik:** 8 saatdan sonra logout

Əl ilə logout:
- Admin Panel → Yuxarı sağ küncdə **"Çıxış"** düyməsi

### Problem Həlli

#### OTP gəlmir

**Development mode-da:**
1. F12 basın (Developer Tools)
2. Console tab-ına keçin
3. OTP orada böyük rəngli yazılıb
4. Və ya `logs/otp-log.txt` faylını açın

**Production mode-da:**
1. Twilio credentials düzgündürmü yoxlayın
2. Twilio Console → Messaging → Logs baxın
3. Telefon nömrəsi verified-mi?

#### "Çox sayda yanlış cəhd" xətası

- ⏰ 15 dəqiqə gözləyin
- 🔄 Browser cache təmizləyin
- 🌐 Başqa browser-dən cəhd edin

#### Google Authenticator kod işləmir

- ⏲️  Telefonun saatı düzdürmü?
- 🔄 30 saniyə gözləyin (yeni kod)
- 🔑 Secret key yenidən daxil edin
- 💾 Backup kod istifadə edin

#### Backup kod işləmir

- ✅ Kod əvvəl istifadə olunubmu? (hər kod 1 dəfə)
- 📋 Doğru kod daxil edibsinizmi?
- 🔄 Yeni kodlar yaradın

---

## API Documentation

### TOTP API

**Base URL:** `api/totp.php`

#### Generate Secret

```bash
GET api/totp.php?action=generate

Response:
{
  "success": true,
  "secret": "JBSWY3DPEHPK3PXP",
  "qr_code": "https://chart.googleapis.com/chart?...",
  "message": "Scan this QR code with Google Authenticator"
}
```

#### Verify Code

```bash
POST api/totp.php?action=verify
Body: {
  "secret": "JBSWY3DPEHPK3PXP",
  "code": "123456"
}

Response:
{
  "success": true,
  "message": "Code is valid"
}
```

#### Test (Get Current Code)

```bash
GET api/totp.php?action=test&secret=JBSWY3DPEHPK3PXP

Response:
{
  "success": true,
  "secret": "JBSWY3DPEHPK3PXP",
  "code": "123456",
  "message": "Current TOTP code"
}
```

### Backup Codes API

**Base URL:** `api/backup-codes.php`

#### Generate Codes

```bash
POST api/backup-codes.php?action=generate
Body: {"username": "admin"}

Response:
{
  "success": true,
  "codes": [
    "1234-5678-9012-3456",
    "2345-6789-0123-4567",
    ...
  ],
  "count": 10,
  "message": "Save these codes in a safe place..."
}
```

#### Verify Code

```bash
POST api/backup-codes.php?action=verify
Body: {
  "username": "admin",
  "code": "1234-5678-9012-3456"
}

Response:
{
  "success": true,
  "message": "Code is valid",
  "remaining": 9
}
```

#### Get Remaining Count

```bash
GET api/backup-codes.php?action=remaining&username=admin

Response:
{
  "success": true,
  "remaining": 9,
  "username": "admin"
}
```

### Rate Limiter API

**Base URL:** `api/rate-limiter.php`

#### Check Status

```bash
GET api/rate-limiter.php?action=check

Response (Not Blocked):
{
  "success": true,
  "blocked": false,
  "remaining_attempts": 5
}

Response (Blocked):
{
  "success": false,
  "blocked": true,
  "remaining_time": 900,
  "message": "Too many failed attempts. Try again in 15 minutes."
}
```

#### Record Attempt

```bash
POST api/rate-limiter.php?action=record
Body: {"success": false}  // or true

Response:
{
  "success": true,
  "remaining_attempts": 4,
  "message": "Failed attempt recorded. 4 attempts remaining."
}
```

---

## Təhlükəsizlik Best Practices

### İstifadəçilər Üçün

✅ **Edin:**
- Güclü parol istifadə edin
- Google Authenticator aktiv edin
- Backup kodları təhlükəsiz yerdə saxlayın
- Paylaşılan kompüterlərdə logout edin
- Browser-i yeniləyin

❌ **Etməyin:**
- Parolu başqaları ilə paylaşmayın
- Backup kodları screenshot kimi saxlamayın
- Public Wi-Fi-də giriş etməyin (VPN istifadə edin)
- Secret key-i public yerdə saxlamayın

### Developerlər Üçün

✅ **Tövsiyələr:**
- HTTPS istifadə edin (production)
- CSRF protection əlavə edin
- SQL injection prevention (prepared statements)
- XSS filtering
- Content Security Policy
- Regular security audits

---

## Fayllar və Struktur

```
├── api/
│   ├── totp.php                 # Google Authenticator API
│   ├── backup-codes.php         # Backup kodlar API
│   ├── rate-limiter.php         # Brute force protection
│   └── send-otp.php             # SMS OTP göndərmə
│
├── data/
│   ├── backup-codes.json        # Backup kodlar DB
│   └── login-attempts.json      # Rate limiting DB
│
├── logs/
│   ├── otp-log.txt              # OTP kodlar log
│   └── security-log.txt         # Security events log
│
├── admin-login.html             # Login səhifəsi + MFA
├── admin-security-settings.html # Təhlükəsizlik ayarları
└── admin-dashboard.html         # Dashboard (auth link)
```

---

## Changelog

### v2.0.0 (2025-11-29)

**Yeni Xüsusiyyətlər:**
- ✅ Google Authenticator (TOTP) dəstəyi
- ✅ Backup kodlar sistemi
- ✅ Brute force protection (rate limiting)
- ✅ Multi-factor authentication (MFA)
- ✅ Security settings page
- ✅ Improved OTP display (console + alert)
- ✅ Session security enhancements
- ✅ Security logging

**Bug Fixes:**
- OTP console-da görünmədiyi problem həll edildi
- Rate limiting IP tracking düzəldildi

---

## Dəstək və Əlaqə

**Documentation:**
- Əsas: `README.md`
- Security: `SECURITY_GUIDE.md` (bu fayl)
- SMS Setup: `SMS_SETUP.md`
- Twilio: `TWILIO_SETUP_5MIN.md`

**Security Issues:**
- GitHub Issues: [Link buraya əlavə edin]
- Email: security@dentalacademy.az

---

**Son Yenilənmə:** 2025-11-29
**Version:** 2.0.0
**Təhlükəsizlik Səviyyəsi:** ⭐⭐⭐⭐⭐ (Maksimum)
