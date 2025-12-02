<?php
session_start();
require_once 'db_connect.php';

// Check quyền
if (!isset($_SESSION['user_id']) || !hasPermission('class.view')) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['user_id'];
$role_id = $_SESSION['role_id']; 
$message = "";
$can_manage_class = ($role_id == 1 || $role_id == 2 || hasPermission('class.manage'));

// --- XỬ LÝ 1: LƯU ĐIỂM DANH (HỌC VIÊN + GIÁO VIÊN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_attendance'])) {
    $class_id = $_POST['class_id'];
    $student_statuses = $_POST['status'] ?? []; // Mảng trạng thái của học viên
    $teacher_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    $now = date('H:i:s');

    // 1. Lưu điểm danh học viên
    foreach ($student_statuses as $student_id => $st_val) {
        // Xóa cũ nếu có để tránh trùng lặp ngày hôm nay
        $conn->query("DELETE FROM student_attendance WHERE class_id=$class_id AND student_id=$student_id AND date='$today'");
        
        // Thêm mới
        $stmt = $conn->prepare("INSERT INTO student_attendance (class_id, student_id, date, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $class_id, $student_id, $today, $st_val);
        $stmt->execute();
    }

    // 2. Lưu lịch sử dạy (Class Logs)
    // Kiểm tra xem đã log chưa để tránh spam
    $check_log = $conn->query("SELECT id FROM class_logs WHERE class_id=$class_id AND DATE(check_in_time)='$today'");
    if ($check_log->num_rows == 0) {
        $stmt_log = $conn->prepare("INSERT INTO class_logs (class_id, teacher_id, check_in_time) VALUES (?, ?, NOW())");
        $stmt_log->bind_param("ii", $class_id, $teacher_id);
        $stmt_log->execute();
    }

    // 3. TỰ ĐỘNG CHẤM CÔNG GIÁO VIÊN (Nếu chưa có)
    $check_att = $conn->query("SELECT id FROM attendance WHERE user_id=$teacher_id AND date='$today'");
    if ($check_att->num_rows == 0) {
        $stmt_att = $conn->prepare("INSERT INTO attendance (user_id, date, check_in, status) VALUES (?, ?, ?, 'present')");
        $stmt_att->bind_param("iss", $teacher_id, $today, $now);
        $stmt_att->execute();
        $message = "<div class='alert alert-success'>Đã lưu điểm danh lớp & Tự động chấm công cho bạn!</div>";
    } else {
        $message = "<div class='alert alert-success'>Đã cập nhật điểm danh lớp học!</div>";
    }
}

// --- XỬ LÝ 2: THÊM LỚP MỚI (Admin) ---
if (isset($_POST['add_class']) && $can_manage_class) {
    $class_name = $_POST['class_name'];
    $schedule = $_POST['schedule'];
    $room = $_POST['room'];
    $tid = $_POST['teacher_id'];
    $count = $_POST['student_count'];
    $conn->query("INSERT INTO classes (class_name, schedule, room, teacher_id, student_count) VALUES ('$class_name', '$schedule', '$room', $tid, $count)");
    $message = "<div class='alert alert-success'>Đã thêm lớp mới!</div>";
}

// --- LẤY DỮ LIỆU ---
// 1. Danh sách lớp
if ($role_id == 1 || $role_id == 2) {
    $sql = "SELECT c.*, u.full_name as teacher_name FROM classes c LEFT JOIN users u ON c.teacher_id = u.id ORDER BY c.id DESC";
} else {
    $sql = "SELECT *, 'Tôi' as teacher_name FROM classes WHERE teacher_id = $uid ORDER BY id DESC";
}
$classes = $conn->query($sql);

// 2. Danh sách Giáo viên (cho Admin)
$teachers_list = [];
if ($can_manage_class) {
    $res = $conn->query("SELECT id, full_name FROM users WHERE role_id = 4 AND status='active'");
    while($r = $res->fetch_assoc()) $teachers_list[] = $r;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Lớp & Điểm danh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .card-class { border-left: 5px solid #0d6efd; transition: 0.3s; }
        .card-class:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .modal-body { max-height: 70vh; overflow-y: auto; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-chalkboard-teacher text-primary"></i> Quản lý Lớp & Điểm danh</h3>
            <div>
                <?php if ($can_manage_class): ?>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addClassModal"><i class="fas fa-plus"></i> Thêm Lớp</button>
                <?php endif; ?>
                <a href="user_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
        </div>

        <?php echo $message; ?>

        <div class="row g-4">
            <?php if($classes && $classes->num_rows > 0): ?>
                <?php while($row = $classes->fetch_assoc()): 
                    $cid = $row['id'];
                    $today = date('Y-m-d');
                    
                    // Kiểm tra đã điểm danh hôm nay chưa
                    $check = $conn->query("SELECT id FROM class_logs WHERE class_id=$cid AND DATE(check_in_time)='$today'");
                    $is_done = ($check->num_rows > 0);
                    
                    // Lấy danh sách học viên của lớp này để nạp vào Modal
                    $stu_sql = "SELECT s.id, s.full_name, s.phone 
                                FROM students s 
                                JOIN class_enrollments ce ON s.id = ce.student_id 
                                WHERE ce.class_id = $cid";
                    $students = $conn->query($stu_sql);
                    $student_list = [];
                    while($s = $students->fetch_assoc()) $student_list[] = $s;
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100 card-class">
                        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                            <span class="text-primary"><?php echo $row['class_name']; ?></span>
                            <?php if($is_done): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Đã xong</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Chưa dạy</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><i class="fas fa-clock text-warning me-2"></i> <?php echo $row['schedule']; ?></p>
                            <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i> <?php echo $row['room']; ?></p>
                            <p class="mb-2"><i class="fas fa-users text-info me-2"></i> <?php echo count($student_list); ?> học viên</p>
                            <p class="mb-3"><i class="fas fa-user-tie text-secondary me-2"></i> GV: <strong><?php echo $row['teacher_name']; ?></strong></p>
                            
                            <hr>
                            
                            <?php if($uid == $row['teacher_id'] || $role_id == 1): ?>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#attModal<?php echo $cid; ?>">
                                    <i class="fas fa-clipboard-check"></i> Điểm danh & Vào lớp
                                </button>
                            <?php else: ?>
                                <button class="btn btn-light w-100 border" disabled>Không phụ trách</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="attModal<?php echo $cid; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Điểm danh: <strong><?php echo $row['class_name']; ?></strong></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="class_id" value="<?php echo $cid; ?>">
                                    <div class="alert alert-info small">
                                        <i class="fas fa-info-circle"></i> Xác nhận điểm danh này sẽ đồng thời ghi nhận công làm việc hôm nay cho bạn.
                                    </div>
                                    
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Học viên</th>
                                                <th class="text-center text-success">Có mặt</th>
                                                <th class="text-center text-danger">Vắng</th>
                                                <th class="text-center text-warning">Muộn</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(count($student_list) > 0): ?>
                                                <?php foreach($student_list as $stu): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo $stu['full_name']; ?></strong><br>
                                                        <small class="text-muted"><?php echo $stu['phone']; ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" class="form-check-input" name="status[<?php echo $stu['id']; ?>]" value="present" checked>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" class="form-check-input" name="status[<?php echo $stu['id']; ?>]" value="absent">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" class="form-check-input" name="status[<?php echo $stu['id']; ?>]" value="late">
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="4" class="text-center">Lớp chưa có học viên nào.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" name="save_attendance" class="btn btn-success fw-bold">
                                        <i class="fas fa-save"></i> Lưu & Xác nhận dạy
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">Chưa có lớp học.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($can_manage_class): ?>
    <div class="modal fade" id="addClassModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Thêm Lớp Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3"><label>Tên lớp</label><input type="text" name="class_name" class="form-control" required></div>
                        <div class="mb-3"><label>Giáo viên</label>
                            <select name="teacher_id" class="form-select" required>
                                <?php foreach($teachers_list as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo $t['full_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3"><label>Phòng</label><input type="text" name="room" class="form-control"></div>
                            <div class="col-6 mb-3"><label>Sĩ số</label><input type="number" name="student_count" class="form-control" value="0"></div>
                        </div>
                        <div class="mb-3"><label>Lịch học</label><input type="text" name="schedule" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="add_class" class="btn btn-success">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>