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

// Lấy danh sách phòng ban
$depts = $conn->query("SELECT * FROM departments ORDER BY id ASC");

// Lấy danh sách tất cả nhân viên (để nạp vào dropdown chọn người trong modal)
// CẬP NHẬT: Lấy thêm cột department_id để so sánh
$all_users = $conn->query("SELECT id, full_name, username, department_id FROM users WHERE status='active' ORDER BY full_name ASC");
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
                // Đếm số lượng nhân viên
                $d_id = $dept['id'];
                $count_sql = $conn->query("SELECT COUNT(*) as total FROM users WHERE department_id = $d_id");
                $count = $count_sql->fetch_assoc()['total'];
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
                        
                        <div class="d-flex justify-content-between align-items-center mb-2 p-3 bg-light rounded">
                            <h6 class="fw-bold text-primary m-0"><i class="fas fa-users"></i> Tổng nhân sự:</h6>
                            <span class="badge bg-primary fs-6"><?php echo $count; ?></span>
                        </div>
                        
                        <div class="d-grid mt-3">
                             <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal<?php echo $d_id; ?>">
                                <i class="fas fa-user-plus"></i> Thêm nhân sự
                            </button>
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
                                <select name="user_id" class="form-select" required size="8">
                                    <?php foreach($users_list as $u): 
                                        // Kiểm tra nếu user đang ở phòng ban hiện tại
                                        $is_in_current_dept = ($u['department_id'] == $d_id);
                                        
                                        // Style bôi đậm và nền xám cho người đã ở trong phòng
                                        $style = $is_in_current_dept ? 'font-weight: bold; background-color: #e9ecef; color: #000;' : '';
                                        $note = $is_in_current_dept ? ' (Đang ở phòng này)' : '';
                                        $disabled = $is_in_current_dept ? 'disabled' : '';
                                    ?>
                                        <option value="<?php echo $u['id']; ?>" style="<?php echo $style; ?>" <?php echo $disabled; ?>>
                                            <?php echo $u['full_name']; ?> (<?php echo $u['username']; ?>)<?php echo $note; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-warning"><i class="fas fa-exclamation-triangle"></i> Những người được bôi đậm là thành viên hiện tại của phòng.</div>
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