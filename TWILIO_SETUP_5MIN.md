# 📱 5 DƏQİQƏDƏ REAL SMS - TWILIO TRIAL

## ✅ PULSUZ: $15 Kredit (100+ SMS)

---

## ADDIM 1: QEYDİYYAT (2 dəqiqə)

1. **Sayta daxil ol:**
   👉 https://www.twilio.com/try-twilio

2. **Sign Up et:**
   - Email daxil et
   - Parol yarat
   - "Start your free trial" kliklə

3. **Telefon nömrən təsdiqlə:**
   - +994504122160 daxil et
   - SMS gelecek (verification code)
   - Kodu daxil et

4. **Survey doldur:**
   - "Which Twilio product?" → **SMS**
   - "What do you plan to build?" → **2FA / OTP**
   - "How do you want to build?" → **With code**
   - "What's your preferred language?" → **PHP**

---

## ADDIM 2: PHONE NUMBER AL (1 dəqiqə)

1. **Dashboard açıldıqdan sonra:**
   - Sol menüden **"Phone Numbers"** seç
   - **"Buy a number"** klikə

2. **Number seç:**
   - Country: **United States** (ən ucuz)
   - Capabilities: **SMS** check et
   - **"Search"** basın

3. **İlk nömrəni seç:**
   - **"Buy"** düyməsinə bas
   - Təsdiqlə

🎉 İndi Twilio nömrən var!

---

## ADDIM 3: CREDENTIALS TAP (1 dəqiqə)

1. **Console-a qayıt:**
   👉 https://console.twilio.com/

2. **Aşağıdakıları kopyala:**

   ```
   Account SID:  ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   Auth Token:   [Show] ← klikləyib görün
   Phone Number: +1xxxxxxxxxx (aldığın nömrə)
   ```

3. **HƏR ÜÇÜNÜ KOPYALA - lazım olacaq!**

---

## ADDIM 4: KODA ƏLAVƏ ET (1 dəqiqə)

**Fayl:** `api/send-otp.php`

**Sətir 48-56 dəyiş:**

```php
// Option 1: Comment out email
// $result = sendViaEmail($phone, $otp);

// Option 2: Activate Twilio
$result = sendViaTwilio($phone, $otp);
```

**Sətir 139-141 dəyiş:**

```php
$accountSid = 'ACxxxxxxxx...';        // Twilio-dan kopyala
$authToken = 'xxxxxxxx...';           // Twilio-dan kopyala
$twilioNumber = '+1xxxxxxxxxx';       // Aldığın nömrə
```

**Nümunə (real credentials-inlə dəyiş):**
```php
$accountSid = 'AC1234567890abcdef1234567890abcdef';
$authToken = 'abcd1234efgh5678ijkl9012mnop3456';
$twilioNumber = '+12025551234';
```

---

## ADDIM 5: TEST ET! 🚀

```bash
# Terminal-da test:
curl -X POST http://localhost:8000/api/send-otp.php \
  -H "Content-Type: application/json" \
  -d '{"phone":"+994504122160","otp":"123456"}'
```

**Və ya browser-dən:**
1. http://localhost:8000/admin-login.html
2. admin / dentalux2025!
3. **30 saniyə ərzində +994504122160 nömrənə SMS gələcək!** 📱

---

## 💰 QİYMƏT

| Xidmət | Qiymət |
|--------|--------|
| Trial kredit | **$15 PULSUZ** 🎁 |
| Azerbaijan SMS | ~$0.05/SMS |
| 1 OTP | $0.05 |
| 100 OTP | $5.00 |
| 300 OTP | Trial kreditlə pulsuz! |

Trial bitəndə kredit əlavə edə bilərsən.

---

## ⚠️  VACIB QEYDLƏR

### Trial Məhdudiyyətləri:
- ✅ Sənin nömrənə göndərə bilər (+994504122160)
- ⚠️  Başqa nömrələrə göndərmək üçün onları **Verified Caller IDs**-ə əlavə et
- ✅ Upgrade edəndə hər nömrəyə göndərə bilərsən

### Verified Caller ID əlavə et:
1. Twilio Console → **Phone Numbers** → **Verified Caller IDs**
2. **Add** klikləyib nömrəni daxil et
3. SMS ilə təsdiqlə
4. İndi o nömrəyə də göndərə bilərsən

---

## 🎯 DƏRHAL İŞLƏYİR

1. ✅ 5 dəqiqə quraşdırma
2. ✅ Dərhal işləyir
3. ✅ 300+ test SMS pulsuz
4. ✅ Azərbaycan nömrələrinə çatır
5. ✅ 99.9% etibarlıdır

---

## 🆘 PROBLEM OLSA

### "Unable to create record: The number is unverified"
**Həll:** Twilio Console-da nömrəni Verified Caller IDs-ə əlavə et

### "Authentication failed"
**Həll:** Account SID və Auth Token yoxla, düzgün kopyalanıbmı?

### "Invalid phone number"
**Həll:** Format: `+994504122160` (+ işarəsi vacibdir)

### SMS gəlmir
**Həll:**
- Twilio Console → **Messaging** → **Logs** bax
- Xəta varmı yoxla
- Balansı yoxla

---

## 📞 TWILIO SUPPORT

- Docs: https://www.twilio.com/docs/sms
- Console: https://console.twilio.com
- Support: https://support.twilio.com

---

**HAZIR! İndi real SMS göndərə bilərsən! 🎉📱**
