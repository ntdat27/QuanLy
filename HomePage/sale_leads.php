<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra quyền truy cập (Lead Manage)
if (!isset($_SESSION['user_id']) || !hasPermission('lead.manage')) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$msg = "";

// --- 1. XỬ LÝ: THÊM MỚI KHÁCH HÀNG (ADD LEAD) ---
if (isset($_POST['add_lead'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $interest = trim($_POST['course_interest']);
    
    // Validate cơ bản
    if (empty($name) || empty($phone)) {
        $msg = "<div class='alert alert-danger'>Vui lòng nhập Tên và Số điện thoại!</div>";
    } else {
        // Mặc định gán cho người đang tạo (assigned_to = $uid) và trạng thái là 'new'
        $sql_add = "INSERT INTO leads (name, phone, email, course_interest, status, assigned_to, created_at) 
                    VALUES (?, ?, ?, ?, 'new', ?, NOW())";
        
        $stmt = $conn->prepare($sql_add);
        $stmt->bind_param("ssssi", $name, $phone, $email, $interest, $uid);

        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success alert-dismissible fade show'>Đã thêm khách hàng mới thành công! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $msg = "<div class='alert alert-danger'>Lỗi hệ thống: " . $conn->error . "</div>";
        }
    }
}

// --- 2. XỬ LÝ: CẬP NHẬT TRẠNG THÁI (EDIT LEAD) ---
if (isset($_POST['update_lead'])) {
    $lead_id = $_POST['lead_id'];
    $status = $_POST['status'];
    $note = $_POST['course_interest'];

    $stmt = $conn->prepare("UPDATE leads SET status = ?, course_interest = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $note, $lead_id);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Cập nhật trạng thái thành công! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- 3. LẤY DỮ LIỆU HIỂN THỊ ---
if ($role_id == 1 || $role_id == 2) {
    // Admin/Trưởng phòng: Xem tất cả
    $sql = "SELECT l.*, u.full_name as assigned_name 
            FROM leads l 
            LEFT JOIN users u ON l.assigned_to = u.id 
            ORDER BY l.created_at DESC";
} else {
    // Nhân viên: Chỉ xem khách mình phụ trách
    $sql = "SELECT *, '' as assigned_name FROM leads WHERE assigned_to = $uid ORDER BY created_at DESC";
}
$leads = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tuyển sinh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .table td { vertical-align: middle; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-headset text-success"></i> Danh sách Tư vấn</h3>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus-circle"></i> Thêm khách mới
                </button>
                <a href="user_dashboard.php" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-success">
                            <tr>
                                <th>Ngày tạo</th>
                                <th>Họ tên</th>
                                <th>Liên hệ</th>
                                <th>Quan tâm</th>
                                <?php if($role_id == 1 || $role_id == 2): ?><th>Phụ trách</th><?php endif; ?>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($leads && $leads->num_rows > 0): ?>
                                <?php while($row = $leads->fetch_assoc()): ?>
                                <tr>
                                    <td class="small text-muted"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td class="fw-bold text-primary"><?php echo $row['name']; ?></td>
                                    <td>
                                        <div><i class="fas fa-phone small text-muted"></i> <a href="tel:<?php echo $row['phone']; ?>" class="text-decoration-none text-dark"><?php echo $row['phone']; ?></a></div>
                                        <?php if(!empty($row['email'])): ?>
                                            <div class="small text-muted"><i class="fas fa-envelope"></i> <?php echo $row['email']; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $row['course_interest']; ?></span></td>
                                    
                                    <?php if($role_id == 1 || $role_id == 2): ?>
                                    <td>
                                        <?php echo !empty($row['assigned_name']) ? "<span class='badge bg-info text-dark'>{$row['assigned_name']}</span>" : "<span class='badge bg-warning text-dark'>Chưa gán</span>"; ?>
                                    </td>
                                    <?php endif; ?>

                                    <td>
                                        <?php 
                                        $st = $row['status'];
                                        $colors = ['new'=>'danger', 'contacted'=>'warning text-dark', 'enrolled'=>'success', 'lost'=>'secondary'];
                                        $txts = ['new'=>'Mới', 'contacted'=>'Đã gọi', 'enrolled'=>'Đã chốt', 'lost'=>'Hủy/Fail'];
                                        echo "<span class='badge bg-".($colors[$st]??'secondary')."'>".($txts[$st]??$st)."</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="tel:<?php echo $row['phone']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-phone"></i></a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit" 
                                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-name="<?php echo $row['name']; ?>"
                                                    data-status="<?php echo $row['status']; ?>"
                                                    data-interest="<?php echo $row['course_interest']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có dữ liệu. Hãy thêm mới!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-user-plus"></i> Thêm Khách Hàng Mới</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Nhập tên khách hàng">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required placeholder="09xxxxxxxx">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="example@email.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Khóa học quan tâm</label>
                            <select name="course_interest" class="form-select">
                                <option value="IELTS">Luyện thi IELTS</option>
                                <option value="Giao tiếp">Tiếng Anh Giao Tiếp</option>
                                <option value="TOEIC">Luyện thi TOEIC</option>
                                <option value="Trẻ em">Tiếng Anh Trẻ Em</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="add_lead" class="btn btn-success">Lưu Khách Hàng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Cập Nhật Trạng Thái</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="lead_id" id="modal_lead_id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Khách hàng:</label>
                            <input type="text" class="form-control bg-light" id="modal_lead_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Trạng thái xử lý:</label>
                            <select name="status" id="modal_lead_status" class="form-select">
                                <option value="new">Mới (Chưa gọi)</option>
                                <option value="contacted">Đang tư vấn (Đã gọi)</option>
                                <option value="enrolled">Đã đăng ký học (Thành công)</option>
                                <option value="lost">Không liên lạc được / Từ chối</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú khóa học:</label>
                            <input type="text" name="course_interest" id="modal_lead_interest" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="update_lead" class="btn btn-primary">Lưu Thay Đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editButtons = document.querySelectorAll('.btn-edit');
            var modalId = document.getElementById('modal_lead_id');
            var modalName = document.getElementById('modal_lead_name');
            var modalStatus = document.getElementById('modal_lead_status');
            var modalInterest = document.getElementById('modal_lead_interest');

            editButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    modalId.value = this.getAttribute('data-id');
                    modalName.value = this.getAttribute('data-name');
                    modalStatus.value = this.getAttribute('data-status');
                    modalInterest.value = this.getAttribute('data-interest');
                });
            });
        });
    </script>
</body>
</html>