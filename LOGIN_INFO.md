# 🔐 Admin Login Məlumatları

## Giriş Məlumatları

### Admin Panel Girişi

**URL:** `http://localhost:8000/admin-login.html`

**İstifadəçi Adı:** `admin`

**Parol:** `dentalux2025!`

**OTP Nömrə:** `+994 50 412 21 60`

---

## Giriş Prosesi

### Addım 1: Login Ekranı
1. `admin-login.html` səhifəsini açın
2. İstifadəçi adı: **admin**
3. Parol: **dentalux2025!**
4. "Daxil Ol" düyməsinə klikləyin

### Addım 2: OTP Doğrulama
1. Sistem avtomatik 6 rəqəmli OTP kod yaradır
2. OTP kod **+994 50 412 21 60** nömrəsinə göndərilir
3. **Development mode-da** OTP kod:
   - Browser console-da göstərilir (F12 açın)
   - `logs/otp-log.txt` faylında yazılır

### Addım 3: OTP Daxil Et
1. 6 rəqəmli kodu daxil edin
2. "Təsdiqlə" düyməsinə klikləyin
3. Uğurlu olduqda admin panel-ə yönləndiriləcəksiniz

---

## OTP Sisteminin İşləməsi

### Development Mode (Hazırda)
- OTP console-da görünür
- `logs/otp-log.txt`-də yazılır
- ⚠️ **SMS göndərmək üçün credentials əlavə edilməlidir**

### Production Mode - SMS Gateway HAZIRDIR! 🚀
OTP göndərmək üçün 3 variant (kod hazırdır, sadəcə credentials lazımdır):

#### Variant 1: SMSC.ru (Tövsiyə - Azərbaycan üçün) ⭐
```php
// api/send-otp.php faylında (sətir 85-86)
$login = 'SIZIN_SMSC_LOGIN';
$password = 'SIZIN_SMSC_PAROL';
```
**Qeydiyyat:** https://smsc.ru
**Qiymət:** ~0.03 USD/SMS
**Quraşdırma:** 5 dəqiqə

#### Variant 2: Twilio (Beynəlxalq)
```php
// api/send-otp.php faylında (sətir 139-141)
$accountSid = 'YOUR_TWILIO_ACCOUNT_SID';
$authToken = 'YOUR_TWILIO_AUTH_TOKEN';
$twilioNumber = 'YOUR_TWILIO_PHONE_NUMBER';
```
**Qeydiyyat:** https://www.twilio.com
**Qiymət:** ~$0.05/SMS
**Trial:** $15 kredit pulsuz

#### Variant 3: AtaSMS (Azerbaijan Local)
```php
// api/send-otp.php faylında (sətir 183-184)
$apiKey = 'YOUR_ATASMS_API_KEY';
$apiUrl = 'https://api.atasms.az/v1/send';
```
**Əlaqə:** https://atasms.az
**Qiymət:** ~0.03 AZN/SMS

📖 **Tam təlimat:** `SMS_SETUP.md` faylında

---

## Session Məlumatları

### Session Müddəti
- **8 saat** aktiv qalır
- Sonra avtomatik logout olur

### Session Storage
- `sessionStorage.adminLoggedIn` = 'true'
- `sessionStorage.loginTime` = timestamp

### Logout
- "Çıxış" düyməsi ilə
- Session storage təmizlənir
- Login səhifəsinə yönləndirilir

---

## OTP Log Yoxlamaq

### Console-da görmək:
1. Browser açın (Chrome/Firefox)
2. F12 basın (Developer Tools)
3. Console tab-ına keçin
4. Login edin
5. OTP kod console-da görünəcək:
   ```
   Generated OTP: 123456
   ```

### Log faylında görmək:
```bash
cat logs/otp-log.txt
```

Nümunə:
```
2025-11-29 23:50:00 | Phone: +994504122160 | OTP: 123456
2025-11-29 23:51:30 | Phone: +994504122160 | OTP: 789012
```

---

## Təhlükəsizlik

### Session Security
- ✅ 8 saatlıq timeout
- ✅ SessionStorage istifadə
- ✅ Auto logout expired sessions

### OTP Security
- ✅ 6 rəqəmli random kod
- ✅ 5 dəqiqə validity (tətbiq olunmalı)
- ✅ 60 saniyə resend cooldown
- ⚠️ Production-da SMS gateway lazımdır

### Password Security
- ⚠️ Şifrə hazırda hardcoded-dır
- 🔒 Production-da database-də hash olmalı
- 🔒 Bcrypt/Argon2 istifadə edin

---

## Production Deployment

### SMS Gateway Quraşdırma

1. **Twilio üçün:**
   - Hesab açın: https://www.twilio.com
   - Phone number alın
   - API credentials əldə edin
   - `api/send-otp.php`-də konfiqurasiya edin

2. **Azerbaijan SMS Gateway:**
   - Local provider seçin (AtaSMS, SMSto.az)
   - API key əldə edin
   - `sendViaAzerbaijanGateway()` funksiyasını aktivləşdirin

### Database Migration
```sql
-- users table
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE
);

-- otp_codes table
CREATE TABLE otp_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    code VARCHAR(6),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES admin_users(id)
);
```

---

## Test Etmək

### Manuel Test:
1. `http://localhost:8000/admin-login.html` açın
2. Username: `admin`
3. Password: `dentalux2025!`
4. Console açın (F12)
5. Login edin
6. Console-da OTP görün
7. OTP daxil edin
8. Admin panel açılmalı

### Avtomatik Test:
```bash
# OTP log-a baxın
tail -f logs/otp-log.txt

# Başqa terminal-da login test edin
```

---

## Troubleshooting

### Problem: OTP göndərilmir
**Həll:**
- Development mode-da normal (SMS gateway yoxdur)
- Console və log-a baxın
- Production-da SMS API konfiqurasiya edin

### Problem: Session expire olmur
**Həll:**
- Browser cache təmizləyin
- SessionStorage yoxlayın: F12 → Application → Session Storage

### Problem: Admin panel-ə giriş yoxdur
**Həll:**
- `admin-dashboard.js`-də `checkAuth()` funksiyası işləyir
- SessionStorage-də `adminLoggedIn` olmalı
- Manuel set edə bilərsiniz (test üçün):
  ```javascript
  sessionStorage.setItem('adminLoggedIn', 'true');
  sessionStorage.setItem('loginTime', new Date().getTime());
  ```

---

## Faydalı Komandalar

```bash
# OTP log görmək
cat logs/otp-log.txt

# Log təmizləmək
> logs/otp-log.txt

# Log watch etmək
tail -f logs/otp-log.txt

# Session test
# Browser console-da:
sessionStorage.getItem('adminLoggedIn')
sessionStorage.getItem('loginTime')
```

---

**Son Yenilənmə:** 2025-11-29
**Status:** ✅ Tam İşlək (Development Mode)
**Production Ready:** ⚠️ SMS Gateway konfiqurasiyası lazımdır
