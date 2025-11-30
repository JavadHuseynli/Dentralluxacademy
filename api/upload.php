<?php
/**
 * File Upload API - Microservice
 * Şəkil və fayl yükləmə xidməti
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Create uploads directory if not exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['file'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        throw new Exception('File too large. Maximum size is 5MB');
    }

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only images allowed (JPEG, PNG, GIF, WebP)');
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $destination = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Optimize image (resize if too large)
    optimizeImage($destination, $mimeType);

    $response = [
        'success' => true,
        'message' => 'File uploaded successfully',
        'data' => [
            'filename' => $filename,
            'url' => 'uploads/' . $filename,
            'size' => filesize($destination),
            'type' => $mimeType,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function optimizeImage($filepath, $mimeType) {
    $maxWidth = 1200;
    $maxHeight = 1200;
    $quality = 85;

    list($width, $height) = getimagesize($filepath);

    if ($width <= $maxWidth && $height <= $maxHeight) {
        return; // No need to optimize
    }

    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $source = imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filepath);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            return;
    }

    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            imagejpeg($newImage, $filepath, $quality);
            break;
        case 'image/png':
            imagepng($newImage, $filepath, 9);
            break;
        case 'image/gif':
            imagegif($newImage, $filepath);
            break;
        case 'image/webp':
            imagewebp($newImage, $filepath, $quality);
            break;
    }

    imagedestroy($source);
    imagedestroy($newImage);
}
?>
