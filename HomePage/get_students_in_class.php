<?php
require_once 'db_connect.php';

if (isset($_GET['class_id'])) {
    $class_id = intval($_GET['class_id']);
    
    // Query lấy thông tin học viên trong lớp đó
    $sql = "SELECT s.id, s.full_name, s.phone 
            FROM students s 
            JOIN class_enrollments ce ON s.id = ce.student_id 
            WHERE ce.class_id = $class_id";
            
    $result = $conn->query($sql);
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    // Trả về JSON cho Javascript đọc
    header('Content-Type: application/json');
    echo json_encode($students);
}
?>