<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // 1. Lấy mật khẩu cũ từ DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user) {
        // 2. Kiểm tra mật khẩu cũ
        if (password_verify($current_pass, $user['password'])) {
            // 3. Kiểm tra 2 mật khẩu mới khớp nhau
            if ($new_pass === $confirm_pass) {
                if (strlen($new_pass) >= 6) {
                    // 4. Mã hóa và cập nhật
                    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update->bind_param("si", $new_hash, $user_id);
                    
                    if ($update->execute()) {
                        $message = "<div class='alert alert-success'>Đổi mật khẩu thành công!</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Lỗi hệ thống!</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Mật khẩu mới phải có ít nhất 6 ký tự!</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Mật khẩu xác nhận không khớp!</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Mật khẩu hiện tại không đúng!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0"><i class="fas fa-key me-2"></i>Đổi Mật Khẩu</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php echo $message; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Mật khẩu mới</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" name="new_password" class="form-control" required placeholder="Ít nhất 6 ký tự">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Nhập lại mật khẩu mới</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold">
                                    Xác nhận thay đổi
                                </button>
                                <a href="user_dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>