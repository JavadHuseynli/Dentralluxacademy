<?php
// save_data.php - Teacher.json faylı yazıcı

// Error reporting aktivləşdir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS və header ayarları
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// GET request - test məqsədi
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = array(
        'status' => 'success',
        'message' => 'PHP server işləyir',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'method' => 'GET'
    );
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// POST request - məlumat saxlama
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $response = array('success' => false, 'message' => '');
    
    try {
        // Input məlumatını oxu
        $rawInput = file_get_contents('php://input');
        
        if (empty($rawInput)) {
            throw new Exception('Boş məlumat göndərildi');
        }
        
        // JSON decode
        $inputData = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON xətası: ' . json_last_error_msg());
        }
        
        if (!$inputData) {
            throw new Exception('JSON məlumat boşdur');
        }
        
        // Teacher.json fayl yolu
        $jsonFile = __DIR__ . '/teacher.json';
        
        // Mövcud məlumatları oxu
        $existingData = array();
        
        if (file_exists($jsonFile)) {
            $fileContent = file_get_contents($jsonFile);
            if ($fileContent !== false && !empty($fileContent)) {
                $decoded = json_decode($fileContent, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $existingData = $decoded;
                }
            }
        }
        
        // Struktur yarat (əgər yoxdursa)
        if (!isset($existingData['metadata'])) {
            $existingData['metadata'] = array(
                'fileName' => 'teacher.json',
                'createdAt' => date('c'),
                'totalApplications' => 0,
                'version' => '1.0'
            );
        }
        
        if (!isset($existingData['applications']) || !is_array($existingData['applications'])) {
            $existingData['applications'] = array();
        }
        
        // Yeni məlumatı əlavə et
        $existingData['applications'][] = $inputData;
        
        // Metadata yenilə
        $existingData['metadata']['lastUpdated'] = date('c');
        $existingData['metadata']['totalApplications'] = count($existingData['applications']);
        
        // JSON string yarat
        $jsonString = json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($jsonString === false) {
            throw new Exception('JSON yaratma xətası: ' . json_last_error_msg());
        }
        
        // Faylı yaz
        $bytesWritten = file_put_contents($jsonFile, $jsonString, LOCK_EX);
        
        if ($bytesWritten === false) {
            throw new Exception('Fayl yazma xətası. Qovluq icazələrini yoxlayın.');
        }
        
        // Uğur cavabı
        $response = array(
            'success' => true,
            'message' => 'Məlumat uğurla teacher.json faylına əlavə edildi',
            'totalApplications' => $existingData['metadata']['totalApplications'],
            'bytesWritten' => $bytesWritten,
            'filePath' => $jsonFile
        );
        
    } catch (Exception $e) {
        $response = array(
            'success' => false,
            'message' => $e->getMessage(),
            'file' => basename(__FILE__),
            'line' => $e->getLine()
        );
        http_response_code(500);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Digər metodlar üçün
http_response_code(405);
echo json_encode(array(
    'success' => false,
    'message' => 'Yalnız GET və POST metodlarına icazə verilir'
), JSON_UNESCAPED_UNICODE);
?>