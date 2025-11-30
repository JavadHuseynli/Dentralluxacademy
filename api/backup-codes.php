<?php
/**
 * Backup Codes System
 * Emergency access codes for 2FA
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

class BackupCodes {
    private $codesFile = __DIR__ . '/../data/backup-codes.json';

    public function __construct() {
        $dir = dirname($this->codesFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Generate backup codes
     */
    public function generateCodes($username, $count = 10) {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = $this->generateCode();
            $codes[] = [
                'code' => $code,
                'used' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        // Save to file
        $this->saveCodes($username, $codes);

        return array_column($codes, 'code');
    }

    /**
     * Generate single backup code
     */
    private function generateCode() {
        $segments = [];
        for ($i = 0; $i < 4; $i++) {
            $segments[] = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        }
        return implode('-', $segments);
    }

    /**
     * Verify backup code
     */
    public function verify($username, $code) {
        $allCodes = $this->loadCodes();

        if (!isset($allCodes[$username])) {
            return false;
        }

        $userCodes = &$allCodes[$username];

        foreach ($userCodes as $key => &$codeData) {
            if ($codeData['code'] === $code && !$codeData['used']) {
                // Mark as used
                $codeData['used'] = true;
                $codeData['used_at'] = date('Y-m-d H:i:s');

                // Save
                file_put_contents($this->codesFile, json_encode($allCodes, JSON_PRETTY_PRINT));

                return true;
            }
        }

        return false;
    }

    /**
     * Get remaining codes count
     */
    public function getRemainingCount($username) {
        $allCodes = $this->loadCodes();

        if (!isset($allCodes[$username])) {
            return 0;
        }

        $unused = array_filter($allCodes[$username], function($code) {
            return !$code['used'];
        });

        return count($unused);
    }

    /**
     * Save codes to file
     */
    private function saveCodes($username, $codes) {
        $allCodes = $this->loadCodes();
        $allCodes[$username] = $codes;

        file_put_contents($this->codesFile, json_encode($allCodes, JSON_PRETTY_PRINT));
    }

    /**
     * Load codes from file
     */
    private function loadCodes() {
        if (!file_exists($this->codesFile)) {
            return [];
        }

        $content = file_get_contents($this->codesFile);
        return json_decode($content, true) ?? [];
    }
}

// API Endpoints
$action = $_GET['action'] ?? '';

try {
    $backupCodes = new BackupCodes();

    switch ($action) {
        case 'generate':
            // Generate new backup codes
            $input = json_decode(file_get_contents('php://input'), true);
            $username = $input['username'] ?? 'admin';

            $codes = $backupCodes->generateCodes($username);

            echo json_encode([
                'success' => true,
                'codes' => $codes,
                'count' => count($codes),
                'message' => 'Save these codes in a safe place. Each can only be used once.'
            ]);
            break;

        case 'verify':
            // Verify backup code
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['username']) || !isset($input['code'])) {
                throw new Exception('Username and code are required');
            }

            $valid = $backupCodes->verify($input['username'], $input['code']);

            if ($valid) {
                $remaining = $backupCodes->getRemainingCount($input['username']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Code is valid',
                    'remaining' => $remaining
                ]);
            } else {
                throw new Exception('Invalid or already used code');
            }
            break;

        case 'remaining':
            // Get remaining codes count
            $username = $_GET['username'] ?? 'admin';
            $count = $backupCodes->getRemainingCount($username);

            echo json_encode([
                'success' => true,
                'remaining' => $count,
                'username' => $username
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
