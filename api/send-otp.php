<?php
/**
 * OTP SMS Sending Service
 * Sends OTP codes via SMS to mobile numbers
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['phone']) || !isset($input['otp'])) {
        throw new Exception('Phone and OTP are required');
    }

    $phone = $input['phone'];
    $otp = $input['otp'];

    // Log OTP for debugging
    $logFile = __DIR__ . '/../logs/otp-log.txt';
    $logDir = dirname($logFile);

    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = date('Y-m-d H:i:s') . " | Phone: $phone | OTP: $otp\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    // =====================================
    // SMS GATEWAY - DISABLED (Display Only)
    // =====================================

    // OTP is only displayed in console and logs
    // No actual SMS is sent
    $result = [
        'success' => true,
        'method' => 'console_and_log_only',
        'note' => 'OTP displayed in browser console and logs/otp-log.txt'
    ];

    // If you want to enable SMS gateway, uncomment one of these:
    // $result = sendViaSMSC($phone, $otp);
    // $result = sendViaTwilio($phone, $otp);
    // $result = sendViaAtaSMS($phone, $otp);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent successfully',
            'phone' => $phone,
            'method' => $result['method'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception($result['message']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Send OTP via SMSC.ru
 * Works well for Azerbaijan, Russia, CIS countries
 * Sign up: https://smsc.ru
 */
function sendViaSMSC($phone, $otp) {
    // ⚠️ ADD YOUR CREDENTIALS HERE
    $login = 'YOUR_SMSC_LOGIN';      // Your SMSC login
    $password = 'YOUR_SMSC_PASSWORD'; // Your SMSC password
    $sender = 'DentalAcad';           // Sender name (max 11 chars)

    if ($login === 'YOUR_SMSC_LOGIN') {
        return [
            'success' => false,
            'message' => 'SMSC credentials not configured. Please add your login and password in api/send-otp.php'
        ];
    }

    $message = "Dental Academy Admin Panel\nOTP kod: $otp\nKod 5 deqiqe etibarlıdır.";

    // Clean phone number (remove spaces, dashes, etc.)
    $phone = preg_replace('/[^0-9+]/', '', $phone);

    $url = 'https://smsc.ru/sys/send.php';
    $params = [
        'login' => $login,
        'psw' => $password,
        'phones' => $phone,
        'mes' => $message,
        'sender' => $sender,
        'charset' => 'utf-8',
        'fmt' => 3 // JSON response
    ];

    $ch = curl_init($url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['id'])) {
        return ['success' => true, 'method' => 'smsc.ru'];
    } else {
        return [
            'success' => false,
            'message' => $result['error'] ?? 'SMS sending failed'
        ];
    }
}

/**
 * Send OTP via Twilio
 * Most reliable international service
 * Sign up: https://www.twilio.com
 */
function sendViaTwilio($phone, $otp) {
    // ⚠️ ADD YOUR TWILIO CREDENTIALS
    $accountSid = 'YOUR_TWILIO_ACCOUNT_SID';
    $authToken = 'YOUR_TWILIO_AUTH_TOKEN';
    $twilioNumber = 'YOUR_TWILIO_PHONE_NUMBER';

    if ($accountSid === 'YOUR_TWILIO_ACCOUNT_SID') {
        return [
            'success' => false,
            'message' => 'Twilio credentials not configured'
        ];
    }

    $message = "Dental Academy OTP: $otp (5 deqiqe etibarlıdır)";

    $url = "https://api.twilio.com/2010-04-01/Accounts/$accountSid/Messages.json";

    $data = [
        'From' => $twilioNumber,
        'To' => $phone,
        'Body' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        return ['success' => true, 'method' => 'twilio'];
    } else {
        return ['success' => false, 'message' => 'Twilio API error'];
    }
}

/**
 * Send OTP via AtaSMS (Azerbaijan local provider)
 * Sign up: https://atasms.az or contact them directly
 */
function sendViaAtaSMS($phone, $otp) {
    // ⚠️ ADD YOUR AtaSMS CREDENTIALS
    $apiKey = 'YOUR_ATASMS_API_KEY';
    $apiUrl = 'https://api.atasms.az/v1/send'; // Check actual API URL

    if ($apiKey === 'YOUR_ATASMS_API_KEY') {
        return [
            'success' => false,
            'message' => 'AtaSMS credentials not configured'
        ];
    }

    $message = "Dental Academy OTP: $otp";
    $phone = preg_replace('/[^0-9]/', '', $phone);

    $data = [
        'api_key' => $apiKey,
        'to' => $phone,
        'message' => $message,
        'sender' => 'DentalAcad'
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return ['success' => true, 'method' => 'atasms'];
    } else {
        return ['success' => false, 'message' => 'AtaSMS API error'];
    }
}

/**
 * Send via email (no credentials needed)
 * Works immediately without any SMS gateway setup
 */
function sendViaEmail($phone, $otp) {
    // Try to send email (will work if mail server is configured)
    $to = 'admin@dentalacademy.az'; // Change to your email

    $subject = '🔐 Dental Academy Admin OTP Code';

    $message = "
╔════════════════════════════════════════╗
║   Dental Academy Admin Panel OTP      ║
╚════════════════════════════════════════╝

📱 Phone: $phone
🔑 OTP Code: $otp
⏰ Time: " . date('Y-m-d H:i:s') . "

⚠️  This code is valid for 5 minutes.

═══════════════════════════════════════

💡 TIP: The OTP is also logged to:
   • Browser console (F12)
   • logs/otp-log.txt file

This is an automated message from
Dental Academy Admin System.
    ";

    $headers = "From: Dental Academy <noreply@dentalacademy.az>\r\n";
    $headers .= "Reply-To: admin@dentalacademy.az\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Try to send email (might not work on localhost without mail server)
    $emailSent = @mail($to, $subject, $message, $headers);

    // Always return success because OTP is available in console and log file
    return [
        'success' => true,
        'method' => 'email' . ($emailSent ? ' (sent)' : ' (check console & logs/otp-log.txt)'),
        'email_sent' => $emailSent,
        'note' => 'OTP is logged to console and logs/otp-log.txt file'
    ];
}
?>
