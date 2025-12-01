<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra quyền truy cập (Lead Manage)
if (!isset($_SESSION['user_id']) || !hasPermission('lead.manage')) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['user_id'];
$role_id = $_SESSION['role_id']; // Lấy Role ID từ session

// --- LOGIC PHÂN QUYỀN MỚI ---
// Nếu là Admin (1) hoặc Trưởng phòng (2) thì xem TOÀN BỘ danh sách
if ($role_id == 1 || $role_id == 2) {
    // JOIN bảng users để lấy tên nhân viên đang phụ trách (assigned_name)
    $sql = "SELECT l.*, u.full_name as assigned_name 
            FROM leads l 
            LEFT JOIN users u ON l.assigned_to = u.id 
            ORDER BY l.created_at DESC";
} else {
    // Nhân viên bình thường: Chỉ xem danh sách được phân công cho mình ($uid)
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
            <a href="user_dashboard.php" class="btn btn-secondary">Quay lại</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-success">
                            <tr>
                                <th>Họ tên</th>
                                <th>Email</th> <th>SĐT</th>
                                <th>Quan tâm</th>
                                
                                <?php if($role_id == 1 || $role_id == 2): ?>
                                    <th>Phụ trách</th>
                                <?php endif; ?>

                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($leads && $leads->num_rows > 0): ?>
                                <?php while($row = $leads->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <?php echo $row['name']; ?>
                                        <br>
                                        <small class="text-muted" style="font-size: 0.8rem;">
                                            <i class="far fa-clock"></i> <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                                        </small>
                                    </td>
                                    
                                    <td>
                                        <?php echo !empty($row['email']) ? $row['email'] : '<span class="text-muted small">Chưa có</span>'; ?>
                                    </td>

                                    <td>
                                        <a href="tel:<?php echo $row['phone']; ?>" class="text-decoration-none text-dark fw-bold">
                                            <?php echo $row['phone']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo $row['course_interest']; ?>
                                        </span>
                                    </td>

                                    <?php if($role_id == 1 || $role_id == 2): ?>
                                    <td>
                                        <?php if(!empty($row['assigned_name'])): ?>
                                            <span class="badge bg-info text-dark"><?php echo $row['assigned_name']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Chưa phân công</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>

                                    <td>
                                        <?php 
                                        $st = $row['status'];
                                        if($st=='new') echo '<span class="badge bg-danger">Mới</span>';
                                        elseif($st=='contacted') echo '<span class="badge bg-warning text-dark">Đã gọi</span>';
                                        elseif($st=='enrolled') echo '<span class="badge bg-success">Đã chốt</span>';
                                        else echo '<span class="badge bg-secondary">Thất bại</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="tel:<?php echo $row['phone']; ?>" class="btn btn-sm btn-primary" title="Gọi điện">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                            <?php if(!empty($row['email'])): ?>
                                                <a href="mailto:<?php echo $row['email']; ?>" class="btn btn-sm btn-info text-white" title="Gửi mail">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-secondary" title="Cập nhật">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo ($role_id == 1 || $role_id == 2) ? 7 : 6; ?>" class="text-center py-4 text-muted">
                                        Chưa có dữ liệu khách hàng cần tư vấn.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>