# 📱 SMS Gateway Quraşdırma Təlimatı

## Seçim 1: SMSC.ru (Tövsiyə olunur - Azərbaycan üçün)

### ✅ Üstünlükləri:
- Azərbaycan nömrələri ilə işləyir
- Sürətli və etibarlı
- Aşağı qiymət (0.02-0.05 USD/SMS)
- Asan quraşdırma

### 📝 Quraşdırma addımları:

1. **Qeydiyyat:**
   - Sayt: https://smsc.ru
   - Qeydiyyatdan keçin
   - Email təsdiqləyin

2. **Balans artır:**
   - Bank kartı ilə minimum 5-10 USD əlavə edin
   - Test üçün 1-2 USD kifayətdir

3. **Credentials əldə et:**
   - Login: sizin istifadəçi adınız
   - Parol: hesab parolu

4. **Kodu yenilə:**
   - `/Users/javad/Developer/public_html/api/send-otp.php` faylını aç
   - Sətir 85-86-da dəyişdir:
   ```php
   $login = 'SIZIN_SMSC_LOGIN';      // Buraya öz logininizi yazın
   $password = 'SIZIN_SMSC_PAROL';   // Buraya öz parolunuzu yazın
   ```

5. **Test et:**
   - Admin login səhifəsinə daxil ol
   - OTP sorğusu göndər
   - Mesaj telefonunuza gəlməlidir (30-60 saniyə)

### 💰 Qiymət:
- Azərbaycan: ~0.03 USD/SMS
- 100 SMS = ~3 USD

---

## Seçim 2: Twilio (Beynəlxalq)

### ✅ Üstünlükləri:
- Çox etibarlı
- Yaxşı sənədləşmə
- Dünya üzrə işləyir

### 📝 Quraşdırma addımları:

1. **Qeydiyyat:**
   - Sayt: https://www.twilio.com
   - Sign up (trial hesab $15 kredit verir)

2. **Phone Number al:**
   - Console-da "Phone Numbers" seç
   - Bir nömrə al (aylıq ~$1)

3. **Credentials tap:**
   - Account SID
   - Auth Token
   - Twilio Phone Number

4. **Kodu yenilə:**
   - `/Users/javad/Developer/public_html/api/send-otp.php` faylında:
   - Sətir 50-i comment et:
   ```php
   // $result = sendViaSMSC($phone, $otp);
   ```
   - Sətir 53-ü uncomment et:
   ```php
   $result = sendViaTwilio($phone, $otp);
   ```
   - Sətir 139-141-də dəyişdir:
   ```php
   $accountSid = 'ACxxxxxxxxxxxxxxx';     // Twilio Account SID
   $authToken = 'xxxxxxxxxxxxxxxx';       // Twilio Auth Token
   $twilioNumber = '+1234567890';         // Twilio phone number
   ```

### 💰 Qiymət:
- Phone number: $1/ay
- Azərbaycan SMS: ~$0.05/SMS
- Trial: $15 kredit pulsuz

---

## Seçim 3: AtaSMS (Azerbaijan Local)

### ✅ Üstünlükləri:
- Azərbaycan şirkəti
- Lokal dəstək
- Manat ilə ödəniş

### 📝 Quraşdırma addımları:

1. **Əlaqə:**
   - Sayt: https://atasms.az
   - və ya telefon: +994 12 xxx xx xx (saytdan tap)
   - Müqavilə bağla

2. **API Key al:**
   - Admin paneldən API key əldə et

3. **Kodu yenilə:**
   - `/Users/javad/Developer/public_html/api/send-otp.php` faylında:
   - Sətir 50-i comment et:
   ```php
   // $result = sendViaSMSC($phone, $otp);
   ```
   - Sətir 56-ı uncomment et:
   ```php
   $result = sendViaAtaSMS($phone, $otp);
   ```
   - Sətir 183-184-də dəyişdir:
   ```php
   $apiKey = 'SIZIN_ATASMS_API_KEY';
   $apiUrl = 'https://api.atasms.az/v1/send'; // Dəqiq URL-i AtaSMS-dən soruş
   ```

### 💰 Qiymət:
- Müqavilə əsasında
- Adətən 0.02-0.04 AZN/SMS

---

## 🔧 Sürətli Test

Konfiqurasiyanı test etmək üçün:

```bash
cd /Users/javad/Developer/public_html

# Test OTP göndər
curl -X POST http://localhost:8000/api/send-otp.php \
  -H "Content-Type: application/json" \
  -d '{"phone":"+994504122160","otp":"123456"}'
```

**Uğurlu cavab:**
```json
{
  "success": true,
  "message": "OTP sent successfully",
  "phone": "+994504122160",
  "method": "smsc.ru",
  "timestamp": "2025-11-30 00:30:00"
}
```

**Xəta (credentials yoxdur):**
```json
{
  "success": false,
  "message": "SMSC credentials not configured..."
}
```

---

## 🎯 Tövsiyə

**Azərbaycan üçün ən yaxşı variant: SMSC.ru**

Səbəblər:
1. ✅ Asan quraşdırma (5 dəqiqə)
2. ✅ Ucuz qiymət
3. ✅ Azərbaycan nömrələri ilə yaxşı işləyir
4. ✅ Dərhal başlaya bilərsiniz
5. ✅ API sadədir

---

## 📋 Hazırkı Status

- ✅ SMS API kodu hazırdır
- ⚠️ Credentials əlavə edilməlidir
- ⚠️ Test edilməlidir

---

## 🆘 Problem olsa

1. **SMS gəlmir:**
   - `/logs/otp-log.txt` faylına bax
   - OTP yazılıbmı yoxla
   - Balansınızı yoxlayın
   - Phone number formatını yoxlayın (+994504122160)

2. **API xətası:**
   - Browser console-a bax (F12)
   - Network tab-da API response-a bax
   - `/logs/otp-log.txt`-də xətalar varmı bax

3. **Credentials səhvdir:**
   - Login/password-u yenidən yoxlayın
   - API key-in aktiv olduğunu təsdiqləyin

---

**Ən sürətli başlama: SMSC.ru ilə 5 dəqiqədə!** 🚀
