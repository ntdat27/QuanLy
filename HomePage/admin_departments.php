<?php
session_start();
require_once 'db_connect.php';

// Check quyền
if (!isset($_SESSION['user_id']) || (!hasPermission('user.view') && $_SESSION['role_id'] != 1)) {
    header("Location: index.php");
    exit();
}

$message = "";

// --- XỬ LÝ: THÊM NHÂN VIÊN VÀO PHÒNG ---
if (isset($_POST['assign_user'])) {
    $dept_id = $_POST['dept_id'];
    $user_id = $_POST['user_id'];
    
    $stmt = $conn->prepare("UPDATE users SET department_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $dept_id, $user_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Đã thêm nhân sự vào phòng ban!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- XỬ LÝ: XÓA NHÂN VIÊN KHỎI PHÒNG ---
if (isset($_POST['remove_user'])) {
    $user_id = $_POST['user_id'];
    $conn->query("UPDATE users SET department_id = NULL WHERE id = $user_id");
    $message = "<div class='alert alert-warning'>Đã đưa nhân sự ra khỏi phòng ban.</div>";
}

// Lấy danh sách 5 phòng ban cố định
$depts = $conn->query("SELECT * FROM departments ORDER BY id ASC");

// Lấy danh sách tất cả nhân viên (để nạp vào dropdown chọn người)
$all_users = $conn->query("SELECT id, full_name, username FROM users WHERE status='active' ORDER BY full_name ASC");
$users_list = [];
while($u = $all_users->fetch_assoc()) { $users_list[] = $u; }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cơ cấu Tổ chức</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .dept-card-header { height: 100px; background-size: cover; background-position: center; position: relative; }
        .dept-overlay { background: rgba(0,0,0,0.6); position: absolute; top:0; left:0; right:0; bottom:0; display: flex; align-items: center; justify-content: center; }
        .avatar-small { width: 30px; height: 30px; object-fit: cover; border-radius: 50%; margin-right: 5px; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-sitemap text-info"></i> Cơ cấu Tổ chức & Nhân sự</h2>
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>

        <?php echo $message; ?>

        <div class="row g-4">
            <?php while($dept = $depts->fetch_assoc()): 
                // Lấy danh sách nhân viên thuộc phòng này
                $d_id = $dept['id'];
                $members = $conn->query("SELECT * FROM users WHERE department_id = $d_id");
                $count = $members->num_rows;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="dept-card-header" style="background-image: url('<?php echo $dept['image']; ?>');">
                        <div class="dept-overlay">
                            <h4 class="text-white fw-bold text-center px-2"><?php echo $dept['name']; ?></h4>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <p class="text-muted small mb-3"><?php echo $dept['description']; ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-primary m-0"><i class="fas fa-users"></i> Thành viên (<?php echo $count; ?>)</h6>
                            <button class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#addMemberModal<?php echo $d_id; ?>" title="Thêm người">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <div class="list-group list-group-flush border rounded" style="max-height: 200px; overflow-y: auto;">
                            <?php if ($count > 0): ?>
                                <?php while($mem = $members->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-1">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $mem['avatar']; ?>" class="avatar-small">
                                        <div class="small lh-1">
                                            <strong><?php echo $mem['full_name']; ?></strong><br>
                                            <span class="text-muted" style="font-size: 10px;"><?php echo $mem['username']; ?></span>
                                        </div>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Xóa nhân viên này khỏi phòng?');">
                                        <input type="hidden" name="user_id" value="<?php echo $mem['id']; ?>">
                                        <button type="submit" name="remove_user" class="btn btn-link text-danger p-0" style="font-size: 0.8rem;"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-3 text-muted small">Chưa có nhân sự</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addMemberModal<?php echo $d_id; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Thêm nhân sự vào: <strong><?php echo $dept['name']; ?></strong></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="dept_id" value="<?php echo $d_id; ?>">
                                <label class="form-label">Chọn nhân viên:</label>
                                <select name="user_id" class="form-select" required size="5">
                                    <?php foreach($users_list as $u): ?>
                                        <option value="<?php echo $u['id']; ?>"><?php echo $u['full_name']; ?> (<?php echo $u['username']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-warning"><i class="fas fa-exclamation-triangle"></i> Nếu nhân viên đã ở phòng khác, họ sẽ được chuyển sang phòng này.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" name="assign_user" class="btn btn-primary">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>