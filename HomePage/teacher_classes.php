<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra quyền: Chỉ Giáo viên (4) hoặc Admin (1,2) mới được vào
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 4 && $_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$msg = "";

// --- 1. XỬ LÝ: TẠO LỚP HỌC MỚI (Chỉ Admin/Manager) ---
if (isset($_POST['create_class']) && ($role_id == 1 || $role_id == 2)) {
    $class_name = trim($_POST['class_name']);
    $teacher_id_assign = $_POST['teacher_id'];
    $schedule = trim($_POST['schedule']);
    $room = trim($_POST['room']);

    if (!empty($class_name) && !empty($schedule)) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, teacher_id, schedule, room, student_count) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("siss", $class_name, $teacher_id_assign, $schedule, $room);
        
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success alert-dismissible fade show'>Đã tạo lớp <b>$class_name</b> thành công! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}

// --- 2. XỬ LÝ: THÊM HỌC VIÊN VÀO LỚP ---
if (isset($_POST['add_student_to_class'])) {
    $class_id_add = $_POST['class_id'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    // Kiểm tra học viên tồn tại chưa
    $check_student = $conn->prepare("SELECT id FROM students WHERE phone = ?");
    $check_student->bind_param("s", $phone);
    $check_student->execute();
    $res_student = $check_student->get_result();

    if ($res_student->num_rows > 0) {
        $st_data = $res_student->fetch_assoc();
        $student_id = $st_data['id'];
    } else {
        $stmt_new = $conn->prepare("INSERT INTO students (full_name, phone, email) VALUES (?, ?, ?)");
        $stmt_new->bind_param("sss", $full_name, $phone, $email);
        $stmt_new->execute();
        $student_id = $conn->insert_id;
    }

    if ($student_id > 0) {
        // Kiểm tra đã vào lớp chưa
        $check_enroll = $conn->query("SELECT id FROM class_enrollments WHERE class_id = $class_id_add AND student_id = $student_id");
        if ($check_enroll->num_rows == 0) {
            $conn->query("INSERT INTO class_enrollments (class_id, student_id) VALUES ($class_id_add, $student_id)");
            // Không cần update cột student_count thủ công nữa, vì ta sẽ đếm trực tiếp khi hiển thị
            $msg = "<div class='alert alert-success alert-dismissible fade show'>Đã thêm <b>$full_name</b> vào lớp! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $msg = "<div class='alert alert-warning alert-dismissible fade show'>Học viên này đã có trong lớp rồi! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    }
}

// --- 3. XỬ LÝ: XÓA HỌC VIÊN KHỎI LỚP ---
if (isset($_GET['remove_student']) && isset($_GET['class_id'])) {
    $sid = $_GET['remove_student'];
    $cid = $_GET['class_id'];
    $conn->query("DELETE FROM class_enrollments WHERE student_id = $sid AND class_id = $cid");
    header("Location: teacher_classes.php?msg=removed");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'removed') {
    $msg = "<div class='alert alert-success'>Đã xóa học viên khỏi lớp.</div>";
}

// --- 4. LẤY DANH SÁCH LỚP HỌC (QUERY SỬA LỖI SĨ SỐ) ---
// Thay vì lấy cột `student_count` có sẵn (dễ sai), ta dùng sub-query COUNT trực tiếp từ bảng enrollment
if ($role_id == 1 || $role_id == 2) {
    // Admin: Xem hết
    $sql = "SELECT c.*, u.full_name as teacher_name,
            (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = c.id) as real_student_count 
            FROM classes c 
            LEFT JOIN users u ON c.teacher_id = u.id 
            ORDER BY c.id DESC";
} else {
    // Giáo viên: Xem lớp mình dạy
    $sql = "SELECT c.*, u.full_name as teacher_name,
            (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = c.id) as real_student_count 
            FROM classes c 
            JOIN users u ON c.teacher_id = u.id 
            WHERE c.teacher_id = $user_id 
            ORDER BY c.id DESC";
}
$classes = $conn->query($sql);

// Lấy danh sách giáo viên (để Admin chọn khi tạo lớp)
$teachers = $conn->query("SELECT id, full_name FROM users WHERE role_id = 4");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Lớp học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-chalkboard-teacher text-primary"></i> Quản lý Lớp học</h3>
            <div>
                <?php if($role_id == 1 || $role_id == 2): ?>
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createClassModal">
                        <i class="fas fa-plus-square"></i> Tạo Lớp Mới
                    </button>
                <?php endif; ?>
                <a href="user_dashboard.php" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle border">
                    <thead class="table-primary">
                        <tr>
                            <th>Tên Lớp</th>
                            <th>Lịch học</th>
                            <th>Phòng</th>
                            <th class="text-center">Sĩ số (Thực)</th>
                            <?php if($role_id == 1 || $role_id == 2): ?><th>Giáo viên</th><?php endif; ?>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($classes->num_rows > 0): ?>
                            <?php while($row = $classes->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo $row['class_name']; ?></td>
                                <td><i class="far fa-calendar-alt text-muted"></i> <?php echo $row['schedule']; ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $row['room']; ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark" style="font-size: 0.9rem;">
                                        <?php echo $row['real_student_count']; ?> HV
                                    </span>
                                </td>
                                <?php if($role_id == 1 || $role_id == 2): ?><td><?php echo $row['teacher_name']; ?></td><?php endif; ?>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-success btn-view-students" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#classModal"
                                            data-class-id="<?php echo $row['id']; ?>"
                                            data-class-name="<?php echo $row['class_name']; ?>">
                                        <i class="fas fa-users"></i> DS & Thêm
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-3">Chưa có dữ liệu lớp học.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="classModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalClassTitle">Chi tiết Lớp học</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7 border-end">
                            <h6 class="text-primary fw-bold mb-2">Danh sách học viên</h6>
                            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                <table class="table table-sm table-striped" id="studentListTable">
                                    <thead><tr><th>Họ tên</th><th>SĐT</th><th>Xóa</th></tr></thead>
                                    <tbody id="studentListBody">
                                        </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <h6 class="text-success fw-bold mb-2">Thêm học viên vào lớp</h6>
                            <form method="POST" class="bg-light p-3 rounded border shadow-sm">
                                <input type="hidden" name="class_id" id="modalClassIdInput">
                                <div class="mb-2">
                                    <label class="small fw-bold">Họ và tên</label>
                                    <input type="text" name="full_name" class="form-control form-control-sm" required placeholder="Nhập tên...">
                                </div>
                                <div class="mb-2">
                                    <label class="small fw-bold">SĐT (Để check trùng)</label>
                                    <input type="text" name="phone" class="form-control form-control-sm" required placeholder="09xxxxxxxx">
                                </div>
                                <div class="mb-2">
                                    <label class="small fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control form-control-sm">
                                </div>
                                <button type="submit" name="add_student_to_class" class="btn btn-success btn-sm w-100 mt-2">
                                    <i class="fas fa-plus-circle"></i> Thêm ngay
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createClassModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-folder-plus"></i> Tạo Lớp Học Mới</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên lớp</label>
                            <input type="text" name="class_name" class="form-control" required placeholder="VD: IELTS Intensive K15">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giáo viên phụ trách</label>
                            <select name="teacher_id" class="form-select" required>
                                <?php 
                                if ($teachers->num_rows > 0) {
                                    while($t = $teachers->fetch_assoc()) {
                                        echo "<option value='".$t['id']."'>".$t['full_name']."</option>";
                                    }
                                } else {
                                    echo "<option value=''>Chưa có giáo viên nào</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Lịch học</label>
                            <input type="text" name="schedule" class="form-control" required placeholder="VD: T2-T4-T6 (19h30)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phòng học</label>
                            <input type="text" name="room" class="form-control" placeholder="VD: P.301">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="create_class" class="btn btn-primary">Tạo Lớp</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // SCRIPT AJAX: Lấy danh sách học viên
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('classModal');
            var title = document.getElementById('modalClassTitle');
            var inputId = document.getElementById('modalClassIdInput');
            var listBody = document.getElementById('studentListBody');

            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var classId = button.getAttribute('data-class-id');
                var className = button.getAttribute('data-class-name');

                title.textContent = "Lớp: " + className;
                inputId.value = classId;
                listBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Đang tải...</td></tr>';

                // Gọi file get_students_in_class.php (Đã tạo ở bước trước)
                fetch('get_students_in_class.php?class_id=' + classId)
                    .then(response => response.json())
                    .then(data => {
                        listBody.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(st => {
                                listBody.innerHTML += `
                                    <tr>
                                        <td>${st.full_name}</td>
                                        <td>${st.phone}</td>
                                        <td>
                                            <a href="teacher_classes.php?remove_student=${st.id}&class_id=${classId}" 
                                               class="text-danger small"
                                               onclick="return confirm('Xóa học viên này khỏi lớp?')">
                                               <i class="fas fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>`;
                            });
                        } else {
                            listBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Chưa có học viên.</td></tr>';
                        }
                    })
                    .catch(err => {
                        listBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Lỗi tải dữ liệu.</td></tr>';
                    });
            });
        });
    </script>
</body>
</html>