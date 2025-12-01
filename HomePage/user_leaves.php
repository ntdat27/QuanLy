<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";

// Xử lý gửi đơn
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);

    if (strtotime($end_date) < strtotime($start_date)) {
        $message = "<div class='alert alert-danger'>Ngày kết thúc không thể trước ngày bắt đầu!</div>";
    } else {
        // Insert vào bảng leave_requests
        $stmt = $conn->prepare("INSERT INTO leave_requests (user_id, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isss", $user_id, $start_date, $end_date, $reason);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Đã gửi đơn thành công! Vui lòng chờ quản lý duyệt.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// Lấy lịch sử nghỉ phép
$uid = $_SESSION['user_id'];
$history = $conn->query("SELECT * FROM leave_requests WHERE user_id = $uid ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xin Nghỉ Phép</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-calendar-plus text-primary"></i> Đơn Xin Nghỉ Phép</h3>
            <a href="user_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-primary text-white fw-bold">Tạo đơn mới</div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lý do nghỉ</label>
                                <textarea name="reason" class="form-control" rows="4" required placeholder="VD: Bị ốm, Việc gia đình..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Gửi Đơn</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">Lịch sử đơn từ của bạn</div>
                    <div class="card-body">
                        <?php if ($history->num_rows > 0): ?>
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Thời gian</th>
                                        <th>Lý do</th>
                                        <th>Ngày gửi</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $history->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['start_date'])); ?> <br>
                                            <small class="text-muted">đến</small> 
                                            <?php echo date('d/m/Y', strtotime($row['end_date'])); ?>
                                        </td>
                                        <td><?php echo $row['reason']; ?></td>
                                        <td class="text-muted small"><?php echo date('H:i d/m', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            if($row['status'] == 'pending') echo '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                                            elseif($row['status'] == 'approved') echo '<span class="badge bg-success">Đã duyệt</span>';
                                            else echo '<span class="badge bg-danger">Từ chối</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">Bạn chưa gửi đơn nghỉ phép nào.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>