<?php
/**
 * Fetch Emails via IMAP
 * Reads emails from Hostinger mailbox
 */

header('Content-Type: application/json');
session_start();

// Check if IMAP extension is available
if (!function_exists('imap_open')) {
    echo json_encode([
        'success' => false,
        'message' => 'IMAP extension not installed. Run: brew install php-imap (Mac) or apt install php-imap (Linux)',
        'emails' => []
    ]);
    exit;
}

// Get credentials from headers or session
$username = $_SERVER['HTTP_X_MAIL_USERNAME'] ?? $_SESSION['mail_username'] ?? null;
$password = $_SERVER['HTTP_X_MAIL_PASSWORD'] ?? $_SESSION['mail_password'] ?? null;

// If credentials provided in headers, save to session
if (!empty($_SERVER['HTTP_X_MAIL_USERNAME']) && !empty($_SERVER['HTTP_X_MAIL_PASSWORD'])) {
    $_SESSION['mail_username'] = $_SERVER['HTTP_X_MAIL_USERNAME'];
    $_SESSION['mail_password'] = $_SERVER['HTTP_X_MAIL_PASSWORD'];
}

// Fallback to config file if no credentials in session/headers
if (empty($username) || empty($password)) {
    if (file_exists('mail-config.php')) {
        require_once 'mail-config.php';
        $username = MAIL_CONFIG['imap']['username'];
        $password = MAIL_CONFIG['imap']['password'];
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Mail credentials not found. Please login first.',
            'emails' => []
        ]);
        exit;
    }
}

$action = $_GET['action'] ?? 'list';
$folder = $_GET['folder'] ?? 'INBOX';
$limit = (int)($_GET['limit'] ?? 50);

try {
    // Build IMAP config dynamically
    $config = [
        'host' => 'imap.hostinger.com',
        'port' => 993,
        'username' => $username,
        'password' => $password
    ];

    // Connect to IMAP server
    $mailbox = sprintf(
        '{%s:%d/imap/ssl/novalidate-cert}%s',
        $config['host'],
        $config['port'],
        $folder
    );

    $connection = @imap_open(
        $mailbox,
        $config['username'],
        $config['password']
    );

    if (!$connection) {
        throw new Exception('IMAP bağlantı xətası: ' . imap_last_error());
    }

    // Get mailbox info
    $check = imap_check($connection);
    $totalEmails = $check->Nmsgs;

    $emails = [];

    if ($action === 'list') {
        // Fetch latest emails
        $start = max(1, $totalEmails - $limit + 1);
        $end = $totalEmails;

        for ($i = $end; $i >= $start; $i--) {
            $header = imap_headerinfo($connection, $i);
            $structure = imap_fetchstructure($connection, $i);

            // Get subject (decode if encoded)
            $subject = isset($header->subject) ? $header->subject : '(Mövzu yoxdur)';
            $subject = imap_utf8($subject);

            // Get from address
            $from = isset($header->from[0]) ? $header->from[0] : null;
            $fromEmail = $from ? $from->mailbox . '@' . $from->host : 'Unknown';
            $fromName = $from && isset($from->personal) ? imap_utf8($from->personal) : $fromEmail;

            // Get to address
            $to = isset($header->to[0]) ? $header->to[0] : null;
            $toEmail = $to ? $to->mailbox . '@' . $to->host : '';

            // Get date
            $date = isset($header->date) ? strtotime($header->date) : time();

            // Check if seen
            $isSeen = strpos($header->Unseen ?? '', 'U') === false;

            // Get message preview (first 200 chars)
            $body = imap_fetchbody($connection, $i, 1);
            if (!$body) {
                $body = imap_body($connection, $i);
            }

            // Decode body
            if ($structure && $structure->encoding == 3) { // Base64
                $body = base64_decode($body);
            } elseif ($structure && $structure->encoding == 4) { // Quoted-printable
                $body = quoted_printable_decode($body);
            }

            $preview = substr(strip_tags($body), 0, 200);

            $emails[] = [
                'id' => $i,
                'uid' => imap_uid($connection, $i),
                'subject' => $subject,
                'from' => [
                    'name' => $fromName,
                    'email' => $fromEmail
                ],
                'to' => $toEmail,
                'date' => $date,
                'dateFormatted' => date('d.m.Y H:i', $date),
                'isSeen' => $isSeen,
                'hasAttachments' => isset($structure->parts) && count($structure->parts) > 1,
                'preview' => $preview
            ];
        }
    } elseif ($action === 'read') {
        $emailId = (int)$_GET['id'];

        if ($emailId > 0 && $emailId <= $totalEmails) {
            $header = imap_headerinfo($connection, $emailId);
            $structure = imap_fetchstructure($connection, $emailId);

            // Get subject
            $subject = isset($header->subject) ? imap_utf8($header->subject) : '(Mövzu yoxdur)';

            // Get from
            $from = isset($header->from[0]) ? $header->from[0] : null;
            $fromEmail = $from ? $from->mailbox . '@' . $from->host : 'Unknown';
            $fromName = $from && isset($from->personal) ? imap_utf8($from->personal) : $fromEmail;

            // Get to
            $to = isset($header->to[0]) ? $header->to[0] : null;
            $toEmail = $to ? $to->mailbox . '@' . $to->host : '';

            // Get full body
            $body = '';
            $htmlBody = '';

            if (isset($structure->parts)) {
                foreach ($structure->parts as $partNum => $part) {
                    $data = imap_fetchbody($connection, $emailId, $partNum + 1);

                    // Decode
                    if ($part->encoding == 3) { // Base64
                        $data = base64_decode($data);
                    } elseif ($part->encoding == 4) { // Quoted-printable
                        $data = quoted_printable_decode($data);
                    }

                    if ($part->subtype == 'HTML') {
                        $htmlBody = $data;
                    } elseif ($part->subtype == 'PLAIN') {
                        $body = $data;
                    }
                }
            } else {
                $body = imap_body($connection, $emailId);

                if ($structure->encoding == 3) {
                    $body = base64_decode($body);
                } elseif ($structure->encoding == 4) {
                    $body = quoted_printable_decode($body);
                }
            }

            // Mark as seen
            imap_setflag_full($connection, $emailId, "\\Seen");

            $emails = [
                'id' => $emailId,
                'uid' => imap_uid($connection, $emailId),
                'subject' => $subject,
                'from' => [
                    'name' => $fromName,
                    'email' => $fromEmail
                ],
                'to' => $toEmail,
                'date' => strtotime($header->date),
                'dateFormatted' => date('d.m.Y H:i', strtotime($header->date)),
                'body' => $body ?: $htmlBody,
                'htmlBody' => $htmlBody,
                'isHtml' => !empty($htmlBody)
            ];
        }
    }

    imap_close($connection);

    echo json_encode([
        'success' => true,
        'total' => $totalEmails,
        'folder' => $folder,
        'emails' => $emails
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'emails' => []
    ]);
}
?>
