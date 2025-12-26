<?php
session_start();
require_once 'db_connect.php';

// Check quyền
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 4 && $_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$msg = "";

// --- 1. XỬ LÝ: TẠO LỚP MỚI ---
if (isset($_POST['create_class'])) {
    $class_name = trim($_POST['class_name']);
    $teacher_id_assign = $_POST['teacher_id'];
    $schedule = trim($_POST['schedule']);
    $room = trim($_POST['room']);

    $stmt = $conn->prepare("INSERT INTO classes (class_name, teacher_id, schedule, room) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $class_name, $teacher_id_assign, $schedule, $room);
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Tạo lớp thành công!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- 2. XỬ LÝ: SỬA LỚP HỌC (EDIT) ---
if (isset($_POST['edit_class'])) {
    $class_id_edit = $_POST['class_id'];
    $class_name = trim($_POST['class_name']);
    $schedule = trim($_POST['schedule']);
    $room = trim($_POST['room']);
    
    // Nếu là Admin thì được sửa giáo viên, Giáo viên thì không
    if ($role_id == 1 || $role_id == 2) {
        $teacher_id_assign = $_POST['teacher_id'];
        $stmt = $conn->prepare("UPDATE classes SET class_name=?, teacher_id=?, schedule=?, room=? WHERE id=?");
        $stmt->bind_param("sissi", $class_name, $teacher_id_assign, $schedule, $room, $class_id_edit);
    } else {
        $stmt = $conn->prepare("UPDATE classes SET class_name=?, schedule=?, room=? WHERE id=?");
        $stmt->bind_param("sssi", $class_name, $schedule, $room, $class_id_edit);
    }

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Cập nhật thông tin lớp thành công!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- 3. XỬ LÝ: XÓA LỚP HỌC (DELETE) ---
if (isset($_GET['delete_class'])) {
    $id_del = $_GET['delete_class'];
    // Xóa lớp (Database đã có ON DELETE CASCADE nên tự xóa học viên/điểm danh liên quan)
    $conn->query("DELETE FROM classes WHERE id=$id_del");
    header("Location: teacher_classes.php?msg=deleted");
    exit();
}

// --- 4. XỬ LÝ: THÊM HỌC VIÊN ---
if (isset($_POST['add_student'])) {
    $class_id_add = $_POST['class_id'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    $check = $conn->query("SELECT id FROM students WHERE phone = '$phone'");
    if ($check->num_rows > 0) {
        $student_id = $check->fetch_assoc()['id'];
    } else {
        $conn->query("INSERT INTO students (full_name, phone, email) VALUES ('$full_name', '$phone', '$email')");
        $student_id = $conn->insert_id;
    }

    $conn->query("INSERT IGNORE INTO class_enrollments (class_id, student_id) VALUES ($class_id_add, $student_id)");
    $msg = "<div class='alert alert-success'>Đã thêm học viên vào lớp!</div>";
}

// --- QUERY LẤY DANH SÁCH LỚP ---
if ($role_id == 1 || $role_id == 2) {
    $sql = "SELECT c.*, u.full_name as teacher_name, 
            (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = c.id) as count_st 
            FROM classes c LEFT JOIN users u ON c.teacher_id = u.id ORDER BY c.id DESC";
} else {
    $sql = "SELECT c.*, u.full_name as teacher_name,
            (SELECT COUNT(*) FROM class_enrollments ce WHERE ce.class_id = c.id) as count_st 
            FROM classes c JOIN users u ON c.teacher_id = u.id WHERE c.teacher_id = $user_id ORDER BY c.id DESC";
}
$classes = $conn->query($sql);
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
                    <i class="fas fa-plus"></i> Tạo Lớp
                </button>
                <?php endif; ?>
                <a href="user_dashboard.php" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>

        <?php echo $msg; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted') echo "<div class='alert alert-success'>Đã xóa lớp thành công.</div>"; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle border">
                    <thead class="table-primary">
                        <tr>
                            <th>Tên Lớp</th>
                            <th>Lịch & Phòng</th>
                            <th class="text-center">Sĩ số</th>
                            <?php if($role_id == 1 || $role_id == 2): ?><th>Giáo viên</th><?php endif; ?>
                            <th class="text-center">Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $classes->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $row['class_name']; ?></td>
                            <td>
                                <div><i class="far fa-calendar-alt text-muted"></i> <?php echo $row['schedule']; ?></div>
                                <div class="small text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo $row['room']; ?></div>
                            </td>
                            <td class="text-center"><span class="badge bg-info text-dark"><?php echo $row['count_st']; ?> HV</span></td>
                            <?php if($role_id == 1 || $role_id == 2): ?><td><?php echo $row['teacher_name']; ?></td><?php endif; ?>
                            <td class="text-center">
                                <a href="class_attendance.php?class_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning text-dark" title="Điểm danh">
                                    <i class="fas fa-clipboard-check"></i>
                                </a>

                                <button class="btn btn-sm btn-success btn-view" 
                                        data-bs-toggle="modal" data-bs-target="#viewModal"
                                        data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo $row['class_name']; ?>">
                                    <i class="fas fa-users"></i>
                                </button>

                                <button class="btn btn-sm btn-primary btn-edit" 
                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo $row['class_name']; ?>"
                                        data-schedule="<?php echo $row['schedule']; ?>"
                                        data-room="<?php echo $row['room']; ?>"
                                        data-teacher="<?php echo $row['teacher_id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <?php if($role_id == 1 || $role_id == 2): ?>
                                <a href="teacher_classes.php?delete_class=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Bạn chắc chắn muốn xóa lớp này? Mọi dữ liệu điểm danh sẽ mất hết!')">
                                   <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createClassModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white"><h5 class="modal-title">Tạo Lớp Mới</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="text" name="class_name" class="form-control mb-2" required placeholder="Tên lớp">
                        <select name="teacher_id" class="form-select mb-2">
                            <?php $teachers->data_seek(0); while($t=$teachers->fetch_assoc()) echo "<option value='{$t['id']}'>{$t['full_name']}</option>"; ?>
                        </select>
                        <input type="text" name="schedule" class="form-control mb-2" required placeholder="Lịch học">
                        <input type="text" name="room" class="form-control" placeholder="Phòng học">
                    </div>
                    <div class="modal-footer"><button type="submit" name="create_class" class="btn btn-primary">Lưu</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-warning"><h5 class="modal-title">Cập nhật Lớp</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="class_id" id="edit_id">
                        <label>Tên lớp</label><input type="text" name="class_name" id="edit_name" class="form-control mb-2" required>
                        <?php if($role_id == 1 || $role_id == 2): ?>
                            <label>Giáo viên</label>
                            <select name="teacher_id" id="edit_teacher" class="form-select mb-2">
                                <?php $teachers->data_seek(0); while($t=$teachers->fetch_assoc()) echo "<option value='{$t['id']}'>{$t['full_name']}</option>"; ?>
                            </select>
                        <?php endif; ?>
                        <label>Lịch học</label><input type="text" name="schedule" id="edit_schedule" class="form-control mb-2" required>
                        <label>Phòng</label><input type="text" name="room" id="edit_room" class="form-control">
                    </div>
                    <div class="modal-footer"><button type="submit" name="edit_class" class="btn btn-warning">Cập nhật</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white"><h5 class="modal-title" id="viewTitle">Danh sách học viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7 border-end">
                            <table class="table table-sm"><tbody id="studentList"></tbody></table>
                        </div>
                        <div class="col-md-5">
                            <h6>Thêm học viên</h6>
                            <form method="POST">
                                <input type="hidden" name="class_id" id="add_class_id">
                                <input type="text" name="full_name" class="form-control mb-2" placeholder="Họ tên" required>
                                <input type="text" name="phone" class="form-control mb-2" placeholder="SĐT" required>
                                <input type="text" name="email" class="form-control mb-2" placeholder="Email">
                                <button type="submit" name="add_student" class="btn btn-success w-100">Thêm</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JS cho nút Sửa
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_schedule').value = this.dataset.schedule;
                document.getElementById('edit_room').value = this.dataset.room;
                if(document.getElementById('edit_teacher')) document.getElementById('edit_teacher').value = this.dataset.teacher;
            });
        });

        // JS cho nút Xem DS (Load AJAX)
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                document.getElementById('viewTitle').innerText = "Lớp: " + this.dataset.name;
                document.getElementById('add_class_id').value = id;
                document.getElementById('studentList').innerHTML = "<tr><td>Đang tải...</td></tr>";
                
                fetch('get_students_in_class.php?class_id=' + id)
                    .then(res => res.json())
                    .then(data => {
                        let html = '';
                        if(data.length > 0) {
                            data.forEach(s => html += `<tr><td>${s.full_name}<br><small>${s.phone}</small></td><td class='text-end'><a href='teacher_classes.php?remove_student=${s.id}&class_id=${id}' class='text-danger' onclick='return confirm("Xóa?")'>&times;</a></td></tr>`);
                        } else { html = '<tr><td class="text-muted">Chưa có học viên</td></tr>'; }
                        document.getElementById('studentList').innerHTML = html;
                    });
            });
        });
    </script>
</body>
</html>