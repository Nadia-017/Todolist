<?php
// api.php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_data') {
    try {
        // 1. ดึงข้อมูลงานจากตาราง tasks
        $stmtTasks = $pdo->query("
            SELECT 
                id, 
DATE_FORMAT(task_date, '%Y-%m-%d') AS date,
                recorder_name AS recorder, 
                job_type AS jobType, 
                quantity, 
                score, 
                note 
            FROM tasks 
            ORDER BY id DESC
        ");
        $tasks = $stmtTasks->fetchAll();

        // 2. ดึงรายชื่อพนักงานจากตาราง employees
        $stmtEmp = $pdo->query("SELECT name FROM employees ORDER BY name ASC");
        $employeesRows = $stmtEmp->fetchAll();
        $employees = array_map(function($e) {
            return trim($e['name']);
        }, $employeesRows);

        // 3. ดึงประเภทงานจากตาราง job_types
        $stmtJobs = $pdo->query("SELECT type_name AS type, score FROM job_types");
        $jobTypes = $stmtJobs->fetchAll();

        echo json_encode([
            'success' => true,
            'rows' => $tasks,
            'employees' => $employees,
            'jobTypes' => $jobTypes
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_task') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $taskDate = $input['date'] ?? date('d-m-Y');
        $recorder = $input['recorder'] ?? '';
        $note = $input['note'] ?? '';
        $rowId = $input['rowId'] ?? null;

        $combinedJobTypes = [];
        $totalQty = 0;
        $totalScore = 0;

        if (!empty($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as $itm) {
                if (!empty($itm['jobType'])) $combinedJobTypes[] = $itm['jobType'];
                $totalQty += floatval($itm['quantity'] ?? 0);
                $totalScore += floatval($itm['score'] ?? 0);
            }
        }

        $jobTypeStr = implode(', ', $combinedJobTypes);

        if ($rowId && intval($rowId) > 0) {
            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE tasks 
                SET task_date = ?, recorder_name = ?, job_type = ?, quantity = ?, score = ?, note = ?
                WHERE id = ?
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note, $rowId]);
        } else {
            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO tasks (task_date, recorder_name, job_type, quantity, score, note)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$taskDate, $recorder, $jobTypeStr, $totalQty, $totalScore, $note]);
        }

        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>