<?php
session_start();
require_once 'db_connect.php';

// Check quyền
if (!isset($_SESSION['user_id']) || !hasPermission('class.view')) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['user_id'];
$role_id = $_SESSION['role_id']; // Lấy Role ID từ session

// LOGIC MỚI: Nếu là Admin (role_id = 1) hoặc Trưởng phòng (role_id = 2) thì xem HẾT. 
// Còn lại (Giáo viên) chỉ xem lớp của mình.
if ($role_id == 1 || $role_id == 2) {
    // Admin xem tất cả lớp, JOIN thêm bảng users để lấy tên giáo viên dạy lớp đó
    $sql = "SELECT c.*, u.full_name as teacher_name 
            FROM classes c 
            JOIN users u ON c.teacher_id = u.id";
} else {
    // Giáo viên chỉ xem lớp mình dạy
    $sql = "SELECT *, 'Tôi' as teacher_name FROM classes WHERE teacher_id = $uid";
}

$classes = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Dạy Của Tôi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-chalkboard-teacher text-primary"></i> Lớp Học & Lịch Dạy</h3>
            <a href="user_dashboard.php" class="btn btn-secondary">Quay lại</a>
        </div>

        <div class="row">
            <?php if($classes->num_rows > 0): ?>
                <?php while($row = $classes->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-primary h-100">
                        <div class="card-header bg-primary text-white fw-bold">
                            <?php echo $row['class_name']; ?>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><i class="fas fa-clock me-2 text-muted"></i> <strong>Lịch:</strong> <?php echo $row['schedule']; ?></p>
                            <p class="card-text"><i class="fas fa-map-marker-alt me-2 text-muted"></i> <strong>Phòng:</strong> <?php echo $row['room']; ?></p>
                            <p class="card-text"><i class="fas fa-users me-2 text-muted"></i> <strong>Sĩ số:</strong> <?php echo $row['student_count']; ?> học viên</p>
                            <p class="card-text"><i class="fas fa-user-tie me-2 text-muted"></i> <strong>GV:</strong> <?php echo $row['teacher_name']; ?></p>
                            <a href="#" class="btn btn-outline-primary btn-sm w-100 mt-2">Điểm danh lớp này</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">Bạn chưa được phân công lớp dạy nào.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>