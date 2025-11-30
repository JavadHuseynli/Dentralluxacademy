<?php
/**
 * TOTP (Time-based One-Time Password) Authentication
 * Google Authenticator compatible
 */

class TOTP {
    private $secret;
    private $period = 30; // 30 seconds
    private $digits = 6;

    public function __construct($secret = null) {
        $this->secret = $secret ?? $this->generateSecret();
    }

    /**
     * Generate a random secret key (Base32 encoded)
     */
    public function generateSecret($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Get current TOTP code
     */
    public function getCode($timestamp = null) {
        $timestamp = $timestamp ?? time();
        $counter = floor($timestamp / $this->period);

        $hash = hash_hmac('sha1', pack('N*', 0, $counter), $this->base32Decode($this->secret), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0xf;

        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, $this->digits);

        return str_pad($code, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verify TOTP code
     */
    public function verify($code, $discrepancy = 1) {
        $timestamp = time();

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $time = $timestamp + ($i * $this->period);
            if ($this->getCode($time) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get QR code URL for Google Authenticator
     */
    public function getQRCodeUrl($account, $issuer = 'Dental Academy') {
        $url = 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($account);
        $url .= '?secret=' . $this->secret;
        $url .= '&issuer=' . rawurlencode($issuer);
        $url .= '&digits=' . $this->digits;
        $url .= '&period=' . $this->period;

        // Generate QR code via Google Charts API
        return 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($url);
    }

    /**
     * Get secret key
     */
    public function getSecret() {
        return $this->secret;
    }

    /**
     * Base32 decode
     */
    private function base32Decode($secret) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';

        for ($i = 0; $i < strlen($secret); $i++) {
            $binary .= str_pad(decbin(strpos($chars, $secret[$i])), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $decoded .= chr(bindec(substr($binary, $i, 8)));
        }

        return $decoded;
    }
}

// API Endpoints
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'generate':
            // Generate new TOTP secret
            $totp = new TOTP();
            $secret = $totp->getSecret();
            $qrCode = $totp->getQRCodeUrl('admin', 'Dental Academy Admin');

            echo json_encode([
                'success' => true,
                'secret' => $secret,
                'qr_code' => $qrCode,
                'message' => 'Scan this QR code with Google Authenticator'
            ]);
            break;

        case 'verify':
            // Verify TOTP code
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['secret']) || !isset($input['code'])) {
                throw new Exception('Secret and code are required');
            }

            $totp = new TOTP($input['secret']);
            $valid = $totp->verify($input['code']);

            if ($valid) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Code is valid'
                ]);
            } else {
                throw new Exception('Invalid code');
            }
            break;

        case 'test':
            // Test endpoint - generate code
            $secret = $_GET['secret'] ?? 'JBSWY3DPEHPK3PXP';
            $totp = new TOTP($secret);
            $code = $totp->getCode();

            echo json_encode([
                'success' => true,
                'secret' => $secret,
                'code' => $code,
                'message' => 'Current TOTP code'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
