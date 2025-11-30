<?php
/**
 * Save Teacher Applications
 * Updates teacher.json file
 */

header('Content-Type: application/json');

// Read POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['applications'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Applications data missing'
    ]);
    exit;
}

$applications = $input['applications'];

// Save to teacher.json
$data = ['applications' => $applications];
$result = file_put_contents('../teacher.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save applications'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Applications saved successfully'
    ]);
}
?>
