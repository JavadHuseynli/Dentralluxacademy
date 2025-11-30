#!/bin/bash

###############################################################################
#                    📱 TWILIO SMS QURAŞDIRMA SKRIPTI                        #
###############################################################################

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║          📱 REAL SMS GÖNDƏRMƏ QURAŞDIRMASI                     ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "Bu skript Twilio credentials-lərini api/send-otp.php-yə əlavə edəcək."
echo ""
echo "⚠️  Əvvəlcə Twilio hesabı yaradın:"
echo "   👉 https://www.twilio.com/try-twilio"
echo "   👉 Təlimat: TWILIO_SETUP_5MIN.md"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Get credentials
read -p "📝 Twilio Account SID: " ACCOUNT_SID
read -p "🔑 Twilio Auth Token: " AUTH_TOKEN
read -p "📱 Twilio Phone Number (məsələn: +12025551234): " TWILIO_NUMBER

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📋 Qəbul edildi:"
echo "   Account SID: ${ACCOUNT_SID:0:10}..."
echo "   Auth Token: ${AUTH_TOKEN:0:10}..."
echo "   Phone: $TWILIO_NUMBER"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

read -p "✅ Davam edək? (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "❌ Ləğv edildi."
    exit 1
fi

echo ""
echo "🔧 api/send-otp.php faylı yenilənir..."

# Backup original file
cp api/send-otp.php api/send-otp.php.backup
echo "✅ Backup yaradıldı: api/send-otp.php.backup"

# Update file
sed -i '' "s|// \$result = sendViaTwilio(\$phone, \$otp);|\$result = sendViaTwilio(\$phone, \$otp);|g" api/send-otp.php
sed -i '' "s|\$result = sendViaEmail(\$phone, \$otp);|// \$result = sendViaEmail(\$phone, \$otp);|g" api/send-otp.php
sed -i '' "s|'YOUR_TWILIO_ACCOUNT_SID'|'$ACCOUNT_SID'|g" api/send-otp.php
sed -i '' "s|'YOUR_TWILIO_AUTH_TOKEN'|'$AUTH_TOKEN'|g" api/send-otp.php
sed -i '' "s|'YOUR_TWILIO_PHONE_NUMBER'|'$TWILIO_NUMBER'|g" api/send-otp.php

echo "✅ Credentials əlavə edildi!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🧪 TEST ETMƏK:"
echo ""
echo "1. Browser-də aç:"
echo "   http://localhost:8000/admin-login.html"
echo ""
echo "2. Giriş et:"
echo "   Username: admin"
echo "   Password: dentalux2025!"
echo ""
echo "3. 30-60 saniyə ərzində +994504122160 nömrənə SMS gələcək! 📱"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "✅ QURAŞDIRMA TAMAMLANDI! 🎉"
echo ""
echo "💡 Əgər xəta varsa:"
echo "   - Twilio Console → Messaging → Logs bax"
echo "   - logs/otp-log.txt faylını yoxla"
echo ""
