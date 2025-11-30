<?php
/**
 * Rate Limiter & Brute Force Protection
 * Prevents too many login attempts
 */

class RateLimiter {
    private $attemptsFile = __DIR__ . '/../data/login-attempts.json';
    private $maxAttempts = 5;           // Max failed attempts
    private $lockoutTime = 900;         // 15 minutes lockout
    private $attemptWindow = 300;       // 5 minutes window

    public function __construct() {
        $dir = dirname($this->attemptsFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Check if IP is blocked
     */
    public function isBlocked($identifier = null) {
        $identifier = $identifier ?? $this->getIdentifier();
        $attempts = $this->getAttempts($identifier);

        if (empty($attempts)) {
            return false;
        }

        // Check if locked out
        $lastAttempt = end($attempts);
        if (isset($lastAttempt['locked_until'])) {
            if (time() < $lastAttempt['locked_until']) {
                return [
                    'blocked' => true,
                    'until' => $lastAttempt['locked_until'],
                    'remaining' => $lastAttempt['locked_until'] - time()
                ];
            } else {
                // Lockout expired, clear attempts
                $this->clearAttempts($identifier);
                return false;
            }
        }

        // Count recent attempts
        $recentAttempts = $this->countRecentAttempts($attempts);

        if ($recentAttempts >= $this->maxAttempts) {
            // Lock the user
            $this->lockUser($identifier);
            return [
                'blocked' => true,
                'until' => time() + $this->lockoutTime,
                'remaining' => $this->lockoutTime
            ];
        }

        return false;
    }

    /**
     * Record failed attempt
     */
    public function recordAttempt($identifier = null, $success = false) {
        $identifier = $identifier ?? $this->getIdentifier();

        if ($success) {
            // Clear attempts on success
            $this->clearAttempts($identifier);
            return;
        }

        $attempts = $this->getAttempts($identifier);
        $attempts[] = [
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $this->saveAttempts($identifier, $attempts);
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts($identifier = null) {
        $identifier = $identifier ?? $this->getIdentifier();
        $attempts = $this->getAttempts($identifier);
        $recentCount = $this->countRecentAttempts($attempts);

        return max(0, $this->maxAttempts - $recentCount);
    }

    /**
     * Count recent attempts within window
     */
    private function countRecentAttempts($attempts) {
        $cutoff = time() - $this->attemptWindow;
        $recent = array_filter($attempts, function($attempt) use ($cutoff) {
            return isset($attempt['timestamp']) && $attempt['timestamp'] > $cutoff;
        });

        return count($recent);
    }

    /**
     * Lock user
     */
    private function lockUser($identifier) {
        $attempts = $this->getAttempts($identifier);
        $attempts[] = [
            'locked_until' => time() + $this->lockoutTime,
            'locked_at' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->saveAttempts($identifier, $attempts);

        // Log to file
        $logFile = __DIR__ . '/../logs/security-log.txt';
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logEntry = date('Y-m-d H:i:s') . " | LOCKED: $identifier | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get identifier (IP + User Agent hash)
     */
    private function getIdentifier() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return hash('sha256', $ip . $ua);
    }

    /**
     * Get attempts for identifier
     */
    private function getAttempts($identifier) {
        if (!file_exists($this->attemptsFile)) {
            return [];
        }

        $content = file_get_contents($this->attemptsFile);
        $all = json_decode($content, true) ?? [];

        return $all[$identifier] ?? [];
    }

    /**
     * Save attempts
     */
    private function saveAttempts($identifier, $attempts) {
        if (!file_exists($this->attemptsFile)) {
            file_put_contents($this->attemptsFile, '{}');
        }

        $content = file_get_contents($this->attemptsFile);
        $all = json_decode($content, true) ?? [];
        $all[$identifier] = $attempts;

        file_put_contents($this->attemptsFile, json_encode($all, JSON_PRETTY_PRINT));
    }

    /**
     * Clear attempts
     */
    private function clearAttempts($identifier) {
        if (!file_exists($this->attemptsFile)) {
            return;
        }

        $content = file_get_contents($this->attemptsFile);
        $all = json_decode($content, true) ?? [];

        if (isset($all[$identifier])) {
            unset($all[$identifier]);
            file_put_contents($this->attemptsFile, json_encode($all, JSON_PRETTY_PRINT));
        }
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
    $limiter = new RateLimiter();

    switch ($action) {
        case 'check':
            // Check if blocked
            $blocked = $limiter->isBlocked();

            if ($blocked) {
                echo json_encode([
                    'success' => false,
                    'blocked' => true,
                    'remaining_time' => $blocked['remaining'],
                    'message' => 'Too many failed attempts. Try again in ' . ceil($blocked['remaining'] / 60) . ' minutes.'
                ]);
            } else {
                $remaining = $limiter->getRemainingAttempts();
                echo json_encode([
                    'success' => true,
                    'blocked' => false,
                    'remaining_attempts' => $remaining
                ]);
            }
            break;

        case 'record':
            // Record attempt
            $input = json_decode(file_get_contents('php://input'), true);
            $success = $input['success'] ?? false;

            $limiter->recordAttempt(null, $success);

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Attempts cleared'
                ]);
            } else {
                $remaining = $limiter->getRemainingAttempts();
                echo json_encode([
                    'success' => true,
                    'remaining_attempts' => $remaining,
                    'message' => "Failed attempt recorded. $remaining attempts remaining."
                ]);
            }
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
