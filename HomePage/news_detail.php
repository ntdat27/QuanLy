<?php
session_start();
require_once 'db_connect.php';

// Lấy ID tin tức từ URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php"); // Nếu không có ID thì quay về trang chủ
    exit();
}

$id = $_GET['id'];

// Lấy chi tiết bài viết từ CSDL
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

// Nếu không tìm thấy tin
if (!$news) {
    echo "<h1>Bài viết không tồn tại!</h1>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $news['title']; ?> - IELTS School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .detail-img { width: 100%; max-height: 500px; object-fit: cover; border-radius: 10px; margin-bottom: 30px; }
        .news-content { font-size: 1.1rem; line-height: 1.8; color: #333; }
        .news-date { color: #888; font-style: italic; margin-bottom: 20px; display: block; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">IELTS School</a>
            <a href="index.php" class="btn btn-outline-light btn-sm ms-auto">Quay lại trang chủ</a>
        </div>
    </nav>

    <div class="container py-5 bg-white shadow-sm rounded" style="max-width: 900px;">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold mb-3"><?php echo $news['title']; ?></h1>
                
                <span class="news-date">
                    <i class="far fa-clock me-2"></i> Đăng ngày: <?php echo date('d/m/Y', strtotime($news['created_at'])); ?>
                </span>

                <img src="<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>" class="detail-img shadow">
                
                <div class="news-content">
                    <p class="fw-bold lead"><?php echo $news['summary']; ?></p>
                    <hr>
                    <?php echo nl2br($news['content']); ?>
                </div>

                <div class="mt-5 text-center">
                    <a href="index.php#news" class="btn btn-primary px-4"><i class="fas fa-arrow-left"></i> Quay lại danh sách tin</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-muted mt-5 border-top">
        <small>&copy; 2025 IELTS School. All Rights Reserved.</small>
    </footer>
</body>
</html>