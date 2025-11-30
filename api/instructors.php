<?php
/**
 * Instructors API - Microservice
 * Həkimlərin idarə edilməsi üçün REST API
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$instructorsFile = __DIR__ . '/../data/instructors.json';

// Ensure data directory exists
if (!file_exists(__DIR__ . '/../data')) {
    mkdir(__DIR__ . '/../data', 0755, true);
}

// Ensure instructors file exists
if (!file_exists($instructorsFile)) {
    file_put_contents($instructorsFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

class InstructorService {
    private $file;

    public function __construct($file) {
        $this->file = $file;
    }

    private function readData() {
        $content = file_get_contents($this->file);
        return json_decode($content, true) ?: [];
    }

    private function writeData($data) {
        return file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public function getAll() {
        return $this->readData();
    }

    public function getById($id) {
        $instructors = $this->readData();
        foreach ($instructors as $instructor) {
            if ($instructor['id'] == $id) {
                return $instructor;
            }
        }
        return null;
    }

    public function create($data) {
        $instructors = $this->readData();

        // Generate new ID
        $maxId = 0;
        foreach ($instructors as $instructor) {
            if ($instructor['id'] > $maxId) {
                $maxId = $instructor['id'];
            }
        }

        $newInstructor = [
            'id' => $maxId + 1,
            'name' => $data['name'] ?? '',
            'title' => $data['title'] ?? '',
            'specialty' => $data['specialty'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'bio' => $data['bio'] ?? '',
            'image' => $data['image'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $instructors[] = $newInstructor;
        $this->writeData($instructors);

        return $newInstructor;
    }

    public function update($id, $data) {
        $instructors = $this->readData();
        $updated = false;

        foreach ($instructors as &$instructor) {
            if ($instructor['id'] == $id) {
                $instructor['name'] = $data['name'] ?? $instructor['name'];
                $instructor['title'] = $data['title'] ?? $instructor['title'];
                $instructor['specialty'] = $data['specialty'] ?? $instructor['specialty'];
                $instructor['email'] = $data['email'] ?? $instructor['email'];
                $instructor['phone'] = $data['phone'] ?? $instructor['phone'];
                $instructor['bio'] = $data['bio'] ?? $instructor['bio'];
                $instructor['image'] = $data['image'] ?? $instructor['image'];
                $instructor['updated_at'] = date('Y-m-d H:i:s');
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->writeData($instructors);
            return $this->getById($id);
        }

        return null;
    }

    public function delete($id) {
        $instructors = $this->readData();
        $filtered = array_filter($instructors, function($instructor) use ($id) {
            return $instructor['id'] != $id;
        });

        $result = count($instructors) != count($filtered);

        if ($result) {
            $this->writeData(array_values($filtered));
        }

        return $result;
    }
}

$service = new InstructorService($instructorsFile);

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $instructor = $service->getById($_GET['id']);
                if ($instructor) {
                    echo json_encode(['success' => true, 'data' => $instructor]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Instructor not found']);
                }
            } else {
                $instructors = $service->getAll();
                echo json_encode(['success' => true, 'data' => $instructors, 'count' => count($instructors)]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception('Invalid JSON data');
            }

            $newInstructor = $service->create($input);
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Instructor created', 'data' => $newInstructor]);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                throw new Exception('ID required for update');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception('Invalid JSON data');
            }

            $updated = $service->update($_GET['id'], $input);
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Instructor updated', 'data' => $updated]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Instructor not found']);
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('ID required for delete');
            }

            $deleted = $service->delete($_GET['id']);
            if ($deleted) {
                echo json_encode(['success' => true, 'message' => 'Instructor deleted']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Instructor not found']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
