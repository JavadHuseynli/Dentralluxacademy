<?php
/**
 * CSRF (Cross-Site Request Forgery) Protection
 * Generates and validates CSRF tokens
 */

class CSRF {
    private static $sessionKey = 'csrf_token';
    private static $tokenExpiry = 3600; // 1 hour

    /**
     * Generate new CSRF token
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $timestamp = time();

        $_SESSION[self::$sessionKey] = [
            'token' => $token,
            'timestamp' => $timestamp
        ];

        return $token;
    }

    /**
     * Get current CSRF token
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::$sessionKey])) {
            return self::generateToken();
        }

        // Check if token expired
        $tokenData = $_SESSION[self::$sessionKey];
        if (time() - $tokenData['timestamp'] > self::$tokenExpiry) {
            return self::generateToken();
        }

        return $tokenData['token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::$sessionKey])) {
            return false;
        }

        $tokenData = $_SESSION[self::$sessionKey];

        // Check if expired
        if (time() - $tokenData['timestamp'] > self::$tokenExpiry) {
            unset($_SESSION[self::$sessionKey]);
            return false;
        }

        // Compare tokens (timing-safe)
        return hash_equals($tokenData['token'], $token);
    }

    /**
     * Verify request has valid CSRF token
     */
    public static function verify() {
        $token = null;

        // Check POST data
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        }
        // Check headers
        elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        // Check JSON body
        else {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['csrf_token'])) {
                $token = $input['csrf_token'];
            }
        }

        if (!$token) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'CSRF token missing'
            ]);
            exit;
        }

        if (!self::validateToken($token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ]);
            exit;
        }

        return true;
    }

    /**
     * Get token as HTML input field
     */
    public static function getInputField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Get token as meta tag
     */
    public static function getMetaTag() {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}

// API Endpoints
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'token' => CSRF::getToken(),
        'message' => 'CSRF token generated'
    ]);
    exit;
}

// For POST requests, verify CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    CSRF::verify();
    echo json_encode([
        'success' => true,
        'message' => 'CSRF token is valid'
    ]);
}
?>
