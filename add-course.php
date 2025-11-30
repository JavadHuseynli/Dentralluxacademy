<?php
// add-course.php - Yeni kurs əlavə edən PHP backend

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Yalnız POST metoduna icazə verilir'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    // Read raw input
    $rawInput = file_get_contents('php://input');

    if (empty($rawInput)) {
        throw new Exception('Boş məlumat göndərildi');
    }

    // Decode JSON
    $newCourse = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON xətası: ' . json_last_error_msg());
    }

    // Validate required fields
    $requiredFields = ['id', 'category', 'title', 'location', 'date', 'day', 'payment', 'image', 'participants', 'description', 'seat', 'schedule'];

    foreach ($requiredFields as $field) {
        if (!isset($newCourse[$field])) {
            throw new Exception("Tələb olunan sahə yoxdur: {$field}");
        }
    }

    // JSON file path
    $jsonFile = __DIR__ . '/upcoming-courses.json';

    // Read existing courses
    $existingCourses = [];

    if (file_exists($jsonFile)) {
        $fileContent = file_get_contents($jsonFile);
        if ($fileContent !== false && !empty($fileContent)) {
            $decoded = json_decode($fileContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $existingCourses = $decoded;
            }
        }
    }

    // Check for duplicate ID
    foreach ($existingCourses as $course) {
        if ($course['id'] == $newCourse['id']) {
            throw new Exception("Bu ID artıq mövcuddur: {$newCourse['id']}. Fərqli ID istifadə edin.");
        }
    }

    // Add new course
    $existingCourses[] = $newCourse;

    // Sort by date (newest first)
    usort($existingCourses, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // Create JSON string
    $jsonString = json_encode($existingCourses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($jsonString === false) {
        throw new Exception('JSON yaratma xətası: ' . json_last_error_msg());
    }

    // Write to file
    $bytesWritten = file_put_contents($jsonFile, $jsonString, LOCK_EX);

    if ($bytesWritten === false) {
        throw new Exception('Fayl yazma xətası. Qovluq icazələrini yoxlayın.');
    }

    // Success response
    $response = [
        'success' => true,
        'message' => 'Kurs uğurla əlavə edildi!',
        'totalCourses' => count($existingCourses),
        'bytesWritten' => $bytesWritten,
        'filePath' => $jsonFile,
        'addedCourse' => $newCourse
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine()
    ];
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
