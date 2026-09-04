<?php
// db.php
// db.php
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

error_reporting(0);
ini_set('display_errors', 0);

$host     = '127.0.0.1';
$port     = 3307; 
$dbname   = 'todolist';
$username = 'root';       
$password = ''; 
$charset  = 'utf8mb4';

// เพิ่ม port=$port ใน DSN string
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
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
            $stmtTasks = $pdo->query("SELECT id, DATE_FORMAT(task_date, '%d-%m-%Y') AS date, recorder_name AS recorder, job_type AS jobType, quantity, score, note FROM tasks ORDER BY id DESC");
        } else {
            $stmtTasks = $pdo->prepare("SELECT id, DATE_FORMAT(task_date, '%d-%m-%Y') AS date, recorder_name AS recorder, job_type AS jobType, quantity, score, note FROM tasks WHERE recorder_name = ? ORDER BY id DESC");
            $stmtTasks->execute([$currentUser]);
        }
        $tasks = $stmtTasks->fetchAll();

        if ($currentRole === 'admin') {
            $stmtEmp = $pdo->query("SELECT DISTINCT TRIM(REPLACE(REPLACE(name, '\r', ''), '\n', '')) AS name FROM employees WHERE name IS NOT NULL AND TRIM(name) != '' ORDER BY id ASC");
            $employees = array_column($stmtEmp->fetchAll(), 'name');
        } else {
            $employees = [$currentUser];
        }

        $stmtJobs = $pdo->query("SELECT TRIM(REPLACE(REPLACE(type_name, '\r', ''), '\n', '')) AS type, score FROM job_types");
        $jobTypes = $stmtJobs->fetchAll();

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
                    // มาในรูปแบบ YYYY-MM-DD
                    $taskDate = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
                } else {
                    // มาในรูปแบบ DD-MM-YYYY
                    $taskDate = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
                }
            }
        }
        // -------------------------------------------------------------

        $recorder = ($currentRole === 'admin') ? trim($input['recorder'] ?? $currentUser) : $currentUser;
        $note     = trim($input['note'] ?? '');
        $rowId    = intval($input['rowId'] ?? 0);

        $combinedJobTypes = [];
        $totalQty = 0;
        $totalScore = 0;

        if (!empty($input['items']) && is_array($input['items'])) {
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
                SET task_date = ?, recorder_name = ?, job_type = ?, quantity = ?, score = ?, note = ?
                WHERE id = ?
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note, $rowId]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO tasks (task_date, recorder_name, job_type, quantity, score, note)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note]);
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
?>