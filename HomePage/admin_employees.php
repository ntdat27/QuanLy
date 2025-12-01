<?php
session_start();
require_once 'db_connect.php';

// Check quyền: Phải là Admin HOẶC có quyền xem nhân viên
if (!isset($_SESSION['user_id']) || (!hasPermission('user.view') && $_SESSION['role_id'] != 1)) {
    header("Location: index.php");
    exit();
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$dept_filter = isset($_GET['dept']) ? $_GET['dept'] : '';

// Xử lý Xóa
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) echo "<script>alert('Đã xóa!'); window.location.href='admin_employees.php';</script>";
    } else {
        echo "<script>alert('Không thể xóa chính mình!');</script>";
    }
}

// Lấy danh sách phòng ban cho Dropdown
$all_depts = $conn->query("SELECT * FROM departments ORDER BY name ASC");

// --- TRUY VẤN DỮ LIỆU ---
// Thêm LEFT JOIN departments để lấy tên phòng
$sql = "SELECT u.*, r.name as role_name, d.name as dept_name, ed.phone, ed.education_level 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        LEFT JOIN departments d ON u.department_id = d.id 
        LEFT JOIN employee_details ed ON u.id = ed.user_id 
        WHERE u.id != ?"; // Luôn trừ bản thân người đang login

// Chuẩn bị tham số cho bind_param
$types = "i";
$params = [$_SESSION['user_id']];

// Điều kiện Tìm kiếm từ khóa
if (!empty($search_query)) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $types .= "sss";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Điều kiện Lọc phòng ban
if (!empty($dept_filter)) {
    $sql .= " AND u.department_id = ?";
    $types .= "i";
    $params[] = $dept_filter;
}

$sql .= " ORDER BY u.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params); // PHP 8.1+ hỗ trợ spread operator
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Nhân sự</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users text-primary"></i> Danh sách Nhân sự</h2>
            
            <div class="d-flex gap-2">
                <a href="register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Thêm mới</a>
               <a href="<?php echo ($_SESSION['role_id'] == 1) ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Quay lại
</a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <select name="dept" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Tất cả phòng ban --</option>
                            <?php while($d = $all_depts->fetch_assoc()): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo ($dept_filter == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo $d['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-auto">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Tìm tên, email..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 250px;">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Tìm</button>
                            <?php if(!empty($search_query) || !empty($dept_filter)): ?>
                                <a href="admin_employees.php" class="btn btn-outline-secondary" title="Xóa bộ lọc"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-auto ms-auto">
                        <a href="export_employees.php?search=<?php echo urlencode($search_query); ?>&dept=<?php echo urlencode($dept_filter); ?>" class="btn btn-success fw-bold">
                            <i class="fas fa-file-excel"></i> Xuất danh sách
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Họ tên & Email</th>
                            <th>Phòng ban</th> <th>Vai trò</th>
                            <th>Trình độ</th>
                            <th>Liên hệ</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo $row['full_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $row['email']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['dept_name']): ?>
                                        <span class="badge bg-light text-dark border"><?php echo $row['dept_name']; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">Chưa phân phòng</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info text-dark"><?php echo $row['role_name']; ?></span></td>
                                <td><?php echo $row['education_level'] ?? '-'; ?></td>
                                <td><?php echo $row['phone'] ?? '-'; ?></td>
                                <td>
                                    <?php if($row['status'] == 'active'): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đã nghỉ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="admin_employee_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-file-alt"></i> Hồ sơ</a>
                                    <a href="admin_employees.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-search me-1"></i> Không tìm thấy nhân viên nào phù hợp.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>