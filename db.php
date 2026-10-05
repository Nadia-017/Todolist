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

// ---------- ไฟล์แนบ (photo) ----------
function safePhotoPath($p) {
    $p = trim((string)$p);
    return preg_match('#^uploads/[A-Za-z0-9_\-]+\.[A-Za-z0-9]{2,5}$#', $p) ? $p : '';
}
function unlinkPhoto($p) {
    $p = safePhotoPath($p);
    if ($p !== '') {
        $full = __DIR__ . '/' . $p;
        if (is_file($full)) @unlink($full);
    }
}

// คะแนนต่อหน่วยของงาน (ใช้คำนวณฝั่งเซิร์ฟเวอร์สำหรับผู้ใช้ทั่วไป)
function unitScoreOf($pdo, $jobName) {
    $tables = ['finance_types', 'loan_types', 'debt_types', 'accounting_types',
               'it_types', 'administrative_types', 'hr_types', 'community_types'];
    $jobName = trim((string)$jobName);
    foreach ($tables as $t) {
        try {
            $rows = $pdo->query("SELECT * FROM {$t}")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { continue; }
        foreach ($rows as $row) {
            $name = $row['job_name'] ?? $row['type_name'] ?? $row['task_name'] ?? $row['name'] ?? '';
            if (trim((string)$name) === $jobName) {
                return floatval($row['score'] ?? 0);
            }
        }
    }
    return 0.0;
}

// ลบ .htaccess เก่าที่ทำให้ uploads ตอบ 500 (ถ้ามีค้างอยู่)
$__ht = __DIR__ . '/uploads/.htaccess';
if (is_file($__ht)) { @unlink($__ht); }

// 2.0 VIEW FILE (เปิดไฟล์ผ่าน PHP ไม่ต้องเรียกไฟล์ตรงๆ)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'view_file') {
    $p = safePhotoPath($_GET['path'] ?? '');
    $full = $p !== '' ? __DIR__ . '/' . $p : '';
    if ($p === '' || !is_file($full)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'ไม่พบไฟล์';
        exit;
    }
    $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
    $mimes = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
    header('Content-Length: ' . filesize($full));
    header('Content-Disposition: inline; filename="' . basename($full) . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($full);
    exit;
}

// 2.1 UPLOAD FILE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'upload_file') {
    try {
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('อัปโหลดไฟล์ไม่สำเร็จ');
        }
        $f = $_FILES['file'];
        if ($f['size'] > 5 * 1024 * 1024) {
            throw new Exception('ไฟล์ต้องมีขนาดไม่เกิน 5 MB');
        }
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($ext, $allowed, true)) {
            throw new Exception('รองรับเฉพาะไฟล์ ' . implode(', ', $allowed));
        }
        $dir = __DIR__ . '/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $newName)) {
            throw new Exception('บันทึกไฟล์ลงเซิร์ฟเวอร์ไม่สำเร็จ');
        }
        echo json_encode(['success' => true, 'path' => 'uploads/' . $newName], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 2.2 DELETE FILE (เฉพาะไฟล์ที่เพิ่งอัปโหลดและยังไม่ได้บันทึก)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_file') {
    $input = json_decode(file_get_contents('php://input'), true);
    $p = safePhotoPath($input['path'] ?? '');
    if ($p !== '') {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE photo LIKE ?");
        $chk->execute(['%' . $p . '%']);
        if ((int)$chk->fetchColumn() === 0) {
            unlinkPhoto($p);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// 3. GET DATA
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_data') {
    try {
        if ($currentRole === 'admin') {
            $stmtTasks = $pdo->query("SELECT id, DATE_FORMAT(task_date, '%d-%m-%Y') AS date, recorder_name AS recorder, job_type AS jobType, quantity, score, note, task_items, photo FROM tasks ORDER BY id DESC");
            $stmtEmp = $pdo->query("SELECT DISTINCT TRIM(REPLACE(REPLACE(name, '\r', ''), '\n', '')) AS name FROM employees WHERE name IS NOT NULL AND TRIM(name) != '' ORDER BY id ASC");
            $employees = array_column($stmtEmp->fetchAll(), 'name');
        } else {
            $stmtTasks = $pdo->prepare("SELECT id, DATE_FORMAT(task_date, '%d-%m-%Y') AS date, recorder_name AS recorder, job_type AS jobType, quantity, score, note, task_items, photo FROM tasks WHERE recorder_name = ? ORDER BY id DESC");
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

        $photoList = [];
        if (!empty($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as $k => $itm) {
                $rawPhotos = $itm['photos'] ?? (!empty($itm['photo']) ? [$itm['photo']] : []);
                $clean = [];
                foreach ((array)$rawPhotos as $rp) {
                    $sp = safePhotoPath($rp);
                    if ($sp !== '') { $clean[] = $sp; $photoList[] = $sp; }
                }
                // คะแนน: แอดมินแก้ไขเองได้ / ผู้ใช้ทั่วไปคำนวณจากจำนวน x คะแนนต่อหน่วย
                $qtyVal = floatval($itm['quantity'] ?? 0);
                if ($currentRole === 'admin') {
                    $input['items'][$k]['score'] = max(0, floatval($itm['score'] ?? 0));
                } else {
                    $input['items'][$k]['score'] = $qtyVal * unitScoreOf($pdo, $itm['jobType'] ?? '');
                }
                unset($input['items'][$k]['photo']);
                $input['items'][$k]['photos'] = $clean;
            }
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
        $photoList = array_values(array_unique($photoList));
        $photoJson = !empty($photoList) ? json_encode($photoList, JSON_UNESCAPED_UNICODE) : null;

        if ($rowId > 0) {
            if ($currentRole !== 'admin') {
                $chk = $pdo->prepare("SELECT recorder_name FROM tasks WHERE id = ?");
                $chk->execute([$rowId]);
                $owner = $chk->fetchColumn();
                if ($owner !== $currentUser) {
                    throw new Exception('คุณไม่มีสิทธิ์แก้ไขรายการของผู้อื่น');
                }
            }

            // ไฟล์เดิมที่ถูกลบออกจากรายการ -> ลบทิ้งจากเซิร์ฟเวอร์
            $oldStmt = $pdo->prepare("SELECT photo FROM tasks WHERE id = ?");
            $oldStmt->execute([$rowId]);
            $oldPhotos = json_decode((string)$oldStmt->fetchColumn(), true) ?: [];

            $stmt = $pdo->prepare("
                UPDATE tasks 
                SET task_date = ?, recorder_name = ?, job_type = ?, quantity = ?, score = ?, note = ?, task_items = ?, photo = ?
                WHERE id = ?
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note, $taskItemsJson, $photoJson, $rowId]);

            foreach (array_diff($oldPhotos, $photoList) as $removed) {
                unlinkPhoto($removed);
            }
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO tasks (task_date, recorder_name, job_type, quantity, score, note, task_items, photo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note, $taskItemsJson, $photoJson]);
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
        'community'       => 'community_types',
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

        $ph = $pdo->prepare("SELECT photo FROM tasks WHERE id = ?");
        $ph->execute([$rowId]);
        $oldPhotos = json_decode((string)$ph->fetchColumn(), true) ?: [];

        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$rowId]);

        foreach ($oldPhotos as $p) {
            unlinkPhoto($p);
        }

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