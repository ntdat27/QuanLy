<?php
session_start();
require_once 'db_connect.php';

if (!isset($_GET['class_id'])) die("Thiếu ID lớp học");
$class_id = intval($_GET['class_id']);
$date = $_POST['date'] ?? date('Y-m-d'); // Mặc định hôm nay

// Lấy thông tin lớp
$class = $conn->query("SELECT * FROM classes WHERE id = $class_id")->fetch_assoc();
if (!$class) die("Lớp học không tồn tại");

// --- 1. KIỂM TRA TRẠNG THÁI KHÓA (CHỈ ĐƯỢC LƯU 1 LẦN) ---
// Kiểm tra xem ngày này đã có dữ liệu trong database chưa
$check_lock = $conn->query("SELECT id FROM student_attendance WHERE class_id = $class_id AND date = '$date' LIMIT 1");
$is_locked = ($check_lock->num_rows > 0); // True nếu đã điểm danh rồi

$msg = "";

// --- 2. XỬ LÝ LƯU ĐIỂM DANH ---
if (isset($_POST['save_attendance'])) {
    // Nếu bị khóa thì chặn luôn không cho lưu
    if ($is_locked) {
        $msg = "<div class='alert alert-danger'><i class='fas fa-lock'></i> Ngày <b>$date</b> đã được điểm danh rồi. Bạn không thể lưu lại lần nữa!</div>";
    } else {
        $statuses = $_POST['status']; // Mảng status[student_id]
        $notes = $_POST['note'];      // Mảng note[student_id]

        if (!empty($statuses)) {
            foreach ($statuses as $sid => $val) {
                $note_val = isset($notes[$sid]) ? $notes[$sid] : '';
                
                // Chỉ INSERT mới (Không update)
                $stmt = $conn->prepare("INSERT INTO student_attendance (class_id, student_id, date, status, note) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $class_id, $sid, $date, $val, $note_val);
                $stmt->execute();
            }
            $msg = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Đã lưu điểm danh ngày $date thành công!</div>";
            
            // Cập nhật lại trạng thái khóa ngay lập tức để giao diện hiển thị đúng
            $is_locked = true; 
        }
    }
}

// LẤY DANH SÁCH HỌC VIÊN + TRẠNG THÁI CŨ (ĐỂ HIỂN THỊ)
$sql = "SELECT s.id, s.full_name, s.phone,
        (SELECT status FROM student_attendance sa WHERE sa.student_id = s.id AND sa.class_id = $class_id AND sa.date = '$date') as status,
        (SELECT note FROM student_attendance sa WHERE sa.student_id = s.id AND sa.class_id = $class_id AND sa.date = '$date') as note
        FROM students s 
        JOIN class_enrollments ce ON s.id = ce.student_id 
        WHERE ce.class_id = $class_id";
$students = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Điểm danh: <?php echo $class['class_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .check-box-lg { transform: scale(1.5); margin: 0 10px; cursor: pointer; }
        .row-present { background-color: #d1e7dd; }
        .row-absent { background-color: #f8d7da; }
        /* Style cho trạng thái bị khóa */
        .locked-form { opacity: 0.8; pointer-events: none; }
        .locked-badge { font-size: 0.9rem; padding: 5px 10px; background-color: #dc3545; color: white; border-radius: 5px; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-clipboard-check text-warning"></i> Điểm danh: <?php echo $class['class_name']; ?></h3>
            <a href="teacher_classes.php" class="btn btn-secondary">Quay lại</a>
        </div>

        <?php echo $msg; ?>

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" id="attendanceForm">
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-4">
                            <label class="fw-bold">Chọn ngày điểm danh:</label>
                            <input type="date" name="date" id="datePicker" class="form-control border-primary fw-bold" value="<?php echo $date; ?>" onchange="document.getElementById('attendanceForm').submit()">
                        </div>
                        <div class="col-md-8 text-end">
                            <?php if ($is_locked): ?>
                                <span class="locked-badge"><i class="fas fa-lock"></i> Đã chốt sổ ngày này</span>
                            <?php else: ?>
                                <button type="submit" name="save_attendance" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Lưu Kết Quả</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Học viên</th>
                                <th width="15%">Có mặt</th>
                                <th width="15%">Vắng</th>
                                <th width="15%">Đi muộn</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="<?php echo $is_locked ? 'locked-form' : ''; ?>">
                            <?php if ($students->num_rows > 0): ?>
                                <?php while($row = $students->fetch_assoc()): 
                                    $st = $row['status'] ?? 'present'; 
                                ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?php echo $row['full_name']; ?> <br> <small class="text-muted"><?php echo $row['phone']; ?></small></td>
                                    
                                    <td class="text-center bg-success bg-opacity-10">
                                        <input type="radio" name="status[<?php echo $row['id']; ?>]" value="present" class="form-check-input check-box-lg" <?php echo ($st=='present')?'checked':''; ?>>
                                    </td>
                                    <td class="text-center bg-danger bg-opacity-10">
                                        <input type="radio" name="status[<?php echo $row['id']; ?>]" value="absent" class="form-check-input check-box-lg" <?php echo ($st=='absent')?'checked':''; ?>>
                                    </td>
                                    <td class="text-center bg-warning bg-opacity-10">
                                        <input type="radio" name="status[<?php echo $row['id']; ?>]" value="late" class="form-check-input check-box-lg" <?php echo ($st=='late')?'checked':''; ?>>
                                    </td>
                                    
                                    <td>
                                        <input type="text" name="note[<?php echo $row['id']; ?>]" class="form-control" value="<?php echo $row['note']; ?>" placeholder="Lý do...">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted">Lớp chưa có học viên nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</body>
</html>