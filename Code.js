const express = require('express');
const mysql = require('mysql2/promise'); // ใช้ mysql2 แบบ promise
const path = require('path');

const app = express();

app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// -------------------------------------------------------------
// ตั้งค่าการเชื่อมต่อ MySQL (ตรงนี้คือส่วนเชื่อมฐานข้อมูล)
// -------------------------------------------------------------
const dbConfig = {
  host: 'localhost',    
  port: 3307,             
  user: 'root',           
  password: '',           
  database: 'todolist',   
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};

const pool = mysql.createPool(dbConfig);

// ทดสอบการเชื่อมต่อฐานข้อมูล
(async () => {
  try {
    const connection = await pool.getConnection();
    console.log('✅ เชื่อมต่อฐานข้อมูล MySQL (todolist) สำเร็จ!');
    connection.release();
  } catch (err) {
    console.error('❌ เชื่อมต่อฐานข้อมูลล้มเหลว:', err.message);
  }
})();

// -------------------------------------------------------------
// API Endpoints ดึงและบันทึกข้อมูลเข้า MySQL
// -------------------------------------------------------------

// 1. ดึงข้อมูลรายการงาน พนักงาน และประเภทงาน
app.get('/api/sheet-data', async (req, res) => {
  try {
    // ดึงงานจากตาราง tasks
    const [rows] = await pool.query(`
      SELECT 
        id, 
        DATE_FORMAT(task_date, '%d-%m-%Y') AS date, 
        recorder_name AS recorder, 
        job_type AS jobType, 
        quantity, 
        score, 
        note 
      FROM tasks 
      ORDER BY id DESC
    `);

    // ดึงรายชื่อจากตาราง employees
    const [employeesRows] = await pool.query('SELECT name FROM employees ORDER BY name ASC');

// let employees = employeesRows.map(e => e.name.trim());

let employees = employeesRows
  .filter(e => e.name)
  .map(e => e.name.trim());


    // ดึงประเภทงานจากตาราง job_types
    const [jobTypesRows] = await pool.query('SELECT type_name AS type, score FROM job_types');

    res.json({
      success: true,
      rows: rows,
      employees: employees,
      jobTypes: jobTypesRows
    });

  } catch (error) {
    res.status(500).json({ success: false, error: error.message });
  }
});

// 2. บันทึก/แก้ไขข้อมูลงาน
app.post('/api/save-task', async (req, res) => {
  try {
    const taskData = req.body;
    const taskDate = taskData.date || new Date().toISOString().split('T')[0];

    let combinedJobTypes = [];
    let totalQty = 0;
    let totalScore = 0;

    if (taskData.items && Array.isArray(taskData.items) && taskData.items.length > 0) {
      for (const itm of taskData.items) {
        if (itm.jobType) combinedJobTypes.push(itm.jobType);
        totalQty += Number(itm.quantity) || 0;
        totalScore += Number(itm.score) || 0;
      }
    } else {
      combinedJobTypes.push(taskData.jobType || '');
      totalQty = Number(taskData.quantity) || 0;
      totalScore = Number(taskData.score) || 0;
    }

    const jobTypeStr = combinedJobTypes.join(', ');
    const recorder = taskData.recorder || '';
    const note = taskData.note || '';

    if (taskData.rowId && Number(taskData.rowId) > 0) {
      // UPDATE
      const updateQuery = `
        UPDATE tasks 
        SET task_date = ?, recorder_name = ?, job_type = ?, quantity = ?, score = ?, note = ?
        WHERE id = ?
      `;
      await pool.execute(updateQuery, [
        taskDate, recorder, jobTypeStr, totalQty, totalScore, note, taskData.rowId
      ]);
    } else {
      // INSERT
      const insertQuery = `
        INSERT INTO tasks (task_date, recorder_name, job_type, quantity, score, note)
        VALUES (?, ?, ?, ?, ?, ?)
      `;
      await pool.execute(insertQuery, [
        taskDate, recorder, jobTypeStr, totalQty, totalScore, note
      ]);
    }

    res.json({ success: true, message: 'บันทึกข้อมูลเรียบร้อยแล้ว' });

  } catch (error) {
    res.status(500).json({ success: false, error: error.message });
  }
});

// 3. รายงานสรุปรายเดือน
// 3. รายงานสรุปรายเดือน (ดึงข้อมูลตรงจากตาราง summary)
app.get('/api/summary', async (req, res) => {
  try {
    // ดึงข้อมูลทั้งหมดจากตาราง summary
    const [summaryRows] = await pool.query('SELECT * FROM summary ORDER BY id DESC');
    
    res.json({ 
      success: true, 
      data: summaryRows 
    });
  } catch (error) {
    res.status(500).json({ success: false, error: error.message });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on http://127.0.0.1:${PORT}`);
});