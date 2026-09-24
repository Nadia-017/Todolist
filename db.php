<?php
// db.php
session_start();

date_default_timezone_set('Asia/Bangkok');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

error_reporting(0);
ini_set('display_errors', 0);

$charset  = 'utf8mb4';
$host     = getenv('DB_HOST')     ?: 'sql205.infinityfree.com'; 
$port     = getenv('DB_PORT')     ?: '3306';
$dbname   = getenv('DB_NAME')     ?: 'if0_42851763_todolist'; 
$username = getenv('DB_USER')     ?: 'if0_42851763';          
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '1Cfhfvjs5ua'; 

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci; SET time_zone = '+07:00';"
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'เชื่อมต่อฐานข้อมูลล้มเหลว: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

// 1. LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userInput = trim($input['username'] ?? '');
    $passInput = trim($input['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE username = ? AND password = ?");
    $stmt->execute([$userInput, $passInput]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        echo json_encode([
            'success' => true,
            'user' => [
                'name' => $user['name'],
                'role' => $user['role']
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 2. LOGOUT
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// Check Session Auth
if (!isset($_SESSION['user_name'])) {
    echo json_encode([
        'success' => false, 
        'auth_required' => true, 
        'error' => 'กรุณาเข้าสู่ระบบก่อน'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$currentUser = $_SESSION['user_name'];
$currentRole = $_SESSION['user_role'];

// 3. GET DATA
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_data') {
    try {
        if ($currentRole === 'admin') {
            $stmtTasks = $pdo->query("SELECT id, DATE_FORMAT(task_date, '%d-%m-%Y') AS date, recorder_name AS recorder, job_type AS jobType, quantity, score, note, task_items FROM tasks ORDER BY id DESC");
            $stmtEmp = $pdo->query("SELECT DISTINCT TRIM(REPLACE(REPLACE(name, '\r', ''), '\n', '')) AS name FROM employees WHERE name IS NOT NULL AND TRIM(name) != '' ORDER BY id ASC");
            $employees = array_column($stmtEmp->fetchAll(), 'name');
        } else {
            $stmtTasks = $pdo->prepare("SELECT id, DATE_FORMAT(task_date, '%d-%m-%Y') AS date, recorder_name AS recorder, job_type AS jobType, quantity, score, note, task_items FROM tasks WHERE recorder_name = ? ORDER BY id DESC");
            $stmtTasks->execute([$currentUser]);
            $employees = [$currentUser];
        }
        $tasks = $stmtTasks->fetchAll();

        // ดึงรายการประเภทงานทั่วไปสำรอง (ถ้ามี)
        $jobTypes = [];
        try {
            $stmtJobs = $pdo->query("SELECT TRIM(REPLACE(REPLACE(type_name, '\r', ''), '\n', '')) AS type, score FROM job_types");
            $jobTypes = $stmtJobs->fetchAll();
        } catch (Exception $ex) {
            $jobTypes = [];
        }

        echo json_encode([
            'success' => true,
            'currentUser' => ['name' => $currentUser, 'role' => $currentRole],
            'rows' => $tasks,
            'employees' => $employees,
            'jobTypes' => $jobTypes
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 4. SAVE TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_task') {
    try {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        if (!$input) {
            throw new Exception('ไม่พบข้อมูลที่ส่งมาจากฟอร์มหรือโครงสร้าง JSON ไม่ถูกต้อง');
        }

        $rawDate = !empty($input['date']) ? trim($input['date']) : date('Y-m-d');
        $taskDate = date('Y-m-d');

        if (!empty($rawDate)) {
            $cleanDate = str_replace('/', '-', $rawDate);
            $parts = explode('-', $cleanDate);

            if (count($parts) === 3) {
                if (strlen($parts[0]) === 4) {
                    $taskDate = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
                } else {
                    $taskDate = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
                }
            }
        }

        $rowId = intval($input['rowId'] ?? 0);
        $note  = trim($input['note'] ?? '');

        $inputRecorder = trim($input['recorder'] ?? '');
        $currentUserVal = isset($currentUser) ? trim($currentUser) : '';

        if ($currentRole === 'admin') {
            $recorder = !empty($inputRecorder) ? $inputRecorder : $currentUserVal;
        } else {
            $recorder = $currentUserVal;
        }

        if (empty($recorder)) {
            throw new Exception('ไม่พบชื่อผู้บันทึกข้อมูล กรุณาเข้าสู่ระบบใหม่อีกครั้ง');
        }

        $combinedJobTypes = [];
        $totalQty = 0;
        $totalScore = 0;
        $taskItemsJson = null;

        if (!empty($input['items']) && is_array($input['items'])) {
            $taskItemsJson = json_encode($input['items'], JSON_UNESCAPED_UNICODE);
            foreach ($input['items'] as $itm) {
                if (!empty($itm['jobType'])) {
                    $combinedJobTypes[] = $itm['jobType'];
                }
                $totalQty += floatval($itm['quantity'] ?? 0);
                $totalScore += floatval($itm['score'] ?? 0);
            }
        }

        $jobTypeStr = implode(', ', $combinedJobTypes);

        if ($rowId > 0) {
            if ($currentRole !== 'admin') {
                $chk = $pdo->prepare("SELECT recorder_name FROM tasks WHERE id = ?");
                $chk->execute([$rowId]);
                $owner = $chk->fetchColumn();
                if ($owner !== $currentUser) {
                    throw new Exception('คุณไม่มีสิทธิ์แก้ไขรายการของผู้อื่น');
                }
            }

            $stmt = $pdo->prepare("
                UPDATE tasks 
                SET task_date = ?, recorder_name = ?, job_type = ?, quantity = ?, score = ?, note = ?, task_items = ?
                WHERE id = ?
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note, $taskItemsJson, $rowId]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO tasks (task_date, recorder_name, job_type, quantity, score, note, task_items)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note, $taskItemsJson]);
        }

        echo json_encode([
            'success' => true, 
            'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        http_response_code(200);
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 5. GET DEPARTMENT TASKS 
if ($action === 'get_dept_tasks') {
    $dept = $_GET['dept'] ?? '';

    $table_map = [
        'finance'         => 'finance_types',
        'loan'            => 'loan_types',
        'debt_collection' => 'debt_types',
        'accounting'      => 'accounting_types',
        'it'              => 'it_types',
        'administrative'  => 'administrative_types',
        'hr'              => 'hr_types',
        'community'       => 'community_types'
    ];

    if (!isset($table_map[$dept])) {
        echo json_encode(['success' => false, 'error' => 'ไม่พบแผนกที่ระบุ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $table_name = $table_map[$dept];

    try {
        $stmt = $pdo->prepare("SELECT * FROM {$table_name}");
        $stmt->execute();
        $rawTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tasks = [];
        foreach ($rawTasks as $row) {
            $taskName = '';
            $score = 0;

            // ตรวจหาชื่อรายการ รองรับทั้ง job_name, type_name, task_name, name ภาษาไทย
            if (isset($row['job_name'])) {
                $taskName = $row['job_name'];
            } elseif (isset($row['type_name'])) {
                $taskName = $row['type_name'];
            } elseif (isset($row['task_name'])) {
                $taskName = $row['task_name'];
            } elseif (isset($row['name'])) {
                $taskName = $row['name'];
            } else {
                foreach ($row as $key => $val) {
                    $lKey = strtolower($key);
                    if (strpos($lKey, 'name') !== false || strpos($lKey, 'title') !== false || strpos($lKey, 'job') !== false) {
                        $taskName = $val;
                        break;
                    }
                }
            }

            if (empty($taskName)) {
                $values = array_values($row);
                $taskName = $values[1] ?? reset($row);
            }

            // ตรวจหาคะแนน
            if (isset($row['score'])) {
                $score = floatval($row['score']);
            } else {
                foreach ($row as $key => $val) {
                    if (strpos(strtolower($key), 'score') !== false || strpos(strtolower($key), 'point') !== false) {
                        $score = floatval($val);
                        break;
                    }
                }
            }

            $tasks[] = [
                'id' => $row['id'] ?? null,
                'task_name' => trim($taskName),
                'score' => $score
            ];
        }

        echo json_encode(['success' => true, 'data' => $tasks], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 6. DELETE TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_task') {
    try {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        $rowId = intval($input['rowId'] ?? 0);

        if ($rowId <= 0) {
            throw new Exception('ไม่พบรหัสข้อมูลที่ต้องการลบ');
        }

        if ($currentRole !== 'admin') {
            $chk = $pdo->prepare("SELECT recorder_name FROM tasks WHERE id = ?");
            $chk->execute([$rowId]);
            $owner = $chk->fetchColumn();
            if ($owner !== $currentUser) {
                throw new Exception('คุณไม่มีสิทธิ์ลบรายการของผู้อื่น');
            }
        }

        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$rowId]);

        echo json_encode([
            'success' => true, 
            'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        http_response_code(200);
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}
?>