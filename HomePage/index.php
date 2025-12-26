<?php
require_once 'db_connect.php'; // Kết nối database

// --- 1. LẤY DỮ LIỆU PHÒNG BAN (Sửa lỗi Undefined variable $all_departments) ---
$dept_query = $conn->query("SELECT * FROM departments");
$all_departments = [];
if ($dept_query) {
    while($dept = $dept_query->fetch_assoc()) {
        $all_departments[] = $dept;
    }
}

// --- 2. LẤY DỮ LIỆU TIN TỨC ---
$news_query = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 6");
$news_items = [];
if ($news_query) {
    while($row = $news_query->fetch_assoc()) {
        $news_items[] = $row;
    }
}
$news_chunks = array_chunk($news_items, 2);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS school</title>
    <meta name="description" content="Ielts school - Nơi cung cấp các khóa học IELTS chất lượng cao với đội ngũ giảng viên giàu kinh nghiệm và phương pháp giảng dạy hiện đại.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="chatbot-toggler">
        <span class="material-symbols-rounded"><i class="fas fa-comment-dots"></i></span>
        <span class="material-symbols-outlined"><i class="fas fa-times"></i></span>
    </div>
    <div class="chatbot">
        <header>
            <h2>Trợ lý ảo IELTSschool</h2>
            <span class="close-btn material-symbols-outlined"><i class="fas fa-times"></i></span>
        </header>
        <ul class="chatbox">
            <li class="chat incoming">
                <span class="material-symbols-outlinphed"><i class="fas fa-robot"></i></span>
                <p>Xin chào 👋<br>Tôi là trợ lý ảo của trung tâm. Bạn cần hỗ trợ gì không?</p>
            </li>
        </ul>
        <div class="chat-input">
            <textarea placeholder="Nhập câu hỏi..." spellcheck="false" required></textarea>
            <span id="send-btn" class="material-symbols-rounded"><i class="fas fa-paper-plane"></i></span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#home">IELTS school</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#home">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">Về chúng tôi</a></li>
                    <!-- <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#servicesModal">Dịch vụ</a></li> -->
                    <li class="nav-item"><a class="nav-link" href="#departments">Phòng ban</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stats">Thống kê</a></li>
                    <li class="nav-item"><a class="nav-link" href="#news">Tin tức & Sự kiện</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="mailto:IELTSschool@gmail.com" class="me-3 text-white d-none d-lg-block"><i class="bi bi-envelope"></i> Ieltschool@gmail.com</a>
                    <a href="tel:+84 8627516189" class="me-3 text-white d-none d-lg-block"><i class="bi bi-telephone"></i> +84 862 7516 189</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php 
                            // Kiểm tra quyền để điều hướng đúng Dashboard
                            // Role ID 1 (Admin) và 2 (Trưởng phòng) -> Admin Dashboard
                            // Các Role khác -> User Dashboard
                            $dashboard_link = (isset($_SESSION['role_id']) && ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2)) 
                                            ? 'admin_dashboard.php' 
                                            : 'user_dashboard.php';
                        ?>
                        <div class="dropdown">
                            <a href="<?php echo $dashboard_link; ?>" class="btn btn-orange ms-2">
                                <i class="fas fa-user-circle me-1"></i> Dashboard
                            </a>
                            <a href="logout.php" class="btn btn-outline-light ms-2 border-0" title="Đăng xuất">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-orange ms-2">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section id="home" class="hero hero-slideshow">
        <div class="hero-overlay">
            <div class="container">
                <h1 class="display-3 fw-bold mb-4 animate-hero-text">IELTS School – Đồng hành cùng học viên chinh phục IELTS 6.5+ dễ dàng</h1>
                <p class="lead mb-4 fs-5 animate-hero-text delay-1">Khi đó giảng viên không chỉ có nhiệm vụ truyền đạt kiến thức cho học viên hiểu, mà còn phải định hướng học viên cách tiếp cận với IELTS một cách rõ ràng nhưng thú vị và đơn giản hơn, tạo sự hứng thú trong quá trình học tập.</p>
                <a href="#about" class="btn btn-orange btn-lg px-5 py-3 animate-hero-text delay-2">Tìm hiểu thêm</a>
            </div>
        </div>
    </section>

    <!-- <section class="container py-5 fade-in">
        <div class="row justify-content-center">
            <div class="col-md-3"><div class="stats-card scroll-animate"><h3>100</h3><p>Doanh nghiệp tại Việt Nam</p></div></div>
            <div class="col-md-3"><div class="stats-card scroll-animate"><h3>1,360</h3><p>Dự án được hoàn thành</p></div></div>
            <div class="col-md-3"><div class="stats-card scroll-animate"><h3>85%</h3><p>Đạt được thành tựu</p></div></div>
            <div class="col-md-3"><div class="stats-card scroll-animate"><h3>15</h3><p>Năm kinh nghiệm</p></div></div>
        </div>
    </section> -->

    <section class="mission-vision" id="mission">
        <div class="container position-relative">
            <h2 class="text-center mb-5">Khám phá những nguyên tắc cốt lõi của chúng tôi</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="mv-card scroll-animate">
                        <img src="img/giangvien.jpg" alt="Focused consultant at desk discussing strategies" class="img-fluid mb-3 rounded">
                        <h4>Phương pháp giảng dạy độc quyền</h4>
                        <p>Chúng tôi tự hào mang đến phương pháp giảng dạy IELTS được nghiên cứu và phát triển bởi đội ngũ giáo viên có điểm số IELTS 8.0+ với nhiều năm kinh nghiệm.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mv-card scroll-animate">
                        <img src="img/achi.jpg" alt="Team brainstorming innovative solutions in a meeting" class="img-fluid mb-3 rounded">
                        <h4>Cam kết đầu ra rõ ràng</h4>
                        <p>IELTS School cam kết đồng hành cùng học viên đến khi đạt mục tiêu, với chính sách rõ ràng và minh bạch.</p>
                    </div>
                </div>
            </div>
            <!-- <div class="text-center mt-4"><button class="play-btn">▶</button></div> -->
        </div>
    </section>

    <section id="about" class="container py-5 fade-in">
    <h2 class="text-center mb-5">Chúng tôi là ai</h2>
    <p class="lead text-center mb-5">IELTS school - Nơi cung cấp các khóa học IELTS chất lượng cao với đội ngũ giảng viên giàu kinh nghiệm và phương pháp giảng dạy hiện đại.</p>
    
    <div class="timeline">
        <div class="timeline-item scroll-animate">
            <h5>2015</h5>
            <p>Thành lập</p>
        </div>
        <div class="timeline-item scroll-animate">
            <h5>2020</h5>
            <p>Mở rộng nhiều cơ sở ở Việt Nam</p>
        </div>
        <div class="timeline-item scroll-animate">
            <h5>2025</h5>
            <p>Đạt trên 85% sự hài lòng của khách hàng</p>
        </div>
    </div>
</section>

    <section id="stats" class="achievements fade-in">
    <div class="container">
        <h2 class="text-center mb-5">Thành tựu nổi bật</h2>
        <div class="achievements-grid scroll-animate">
            <div class="achievement-box">
                <div class="number" data-target="2005">0</div> 
                <div class="description">Học viên đã đạt mục tiêu</div>
            </div>
            <div class="achievement-box">
                <div class="number" data-target="98">0</div>
                <div class="description">Tỷ lệ đạt điểm cam kết (%)</div>
            </div>
            <div class="achievement-box">
                <div class="number" data-target="6.5">0</div>
                <div class="description">Là điểm IELTS trung bình</div>
            </div>
            <div class="achievement-box">
                <div class="number" data-target="24">0</div>
                <div class="description">Giáo viên đạt 7.5+ IELTS</div>
            </div>
            <div class="achievement-box">
                <div class="number" data-target="10">0</div>
                <div class="description">Năm kinh nghiệm đào tạo</div>
            </div>
        </div>
        <!-- ✅ PHẦN HỌC VIÊN ĐẠT KẾT QUẢ CAO - 4 HỌC VIÊN, MỖI SLIDE 1 NGƯỜI (ẢNH TRÁI, TEXT PHẢI) -->
        <div class="top-students-section scroll-animate mt-5">
            <h3 class="text-center mb-4 text-white">Học viên đạt kết quả cao</h3>
            <div id="topStudentsCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    // Chỉ 4 học viên demo
                    $top_students = [
                        ['name' => 'Hà Văn Dũng', 'image' => 'img/dung.jpg', 'score' => '8.0', 'testimonial' => 'Cảm ơn IELTS School đã giúp tôi đạt band 8.0 chỉ trong 3 tháng! Phương pháp học hiệu quả và giáo viên tận tâm.'],
                        ['name' => 'Nguyễn Tiến Đạt', 'image' => 'img/dat.jpg', 'score' => '7.5', 'testimonial' => 'Phương pháp học thú vị, giáo viên tận tâm. Điểm Listening lên 9.0! Tôi rất hài lòng với lộ trình cá nhân hóa.'],
                        ['name' => 'Nguyễn Minh Quân', 'image' => 'img/manh.jpg', 'score' => '7.0', 'testimonial' => 'Từ 5.0 lên 7.0, nhờ lộ trình cá nhân hóa. Trung tâm đã đồng hành sát sao, giúp tôi tự tin hơn trong kỳ thi.'],
                        ['name' => 'Nguyễn Ngọc Mạnh', 'image' => 'img/quan.jpg', 'score' => '8.5', 'testimonial' => 'Tuyệt vời! Đã chinh phục Writing band 8.5. Cảm ơn đội ngũ giảng viên đã truyền cảm hứng học tập cho tôi.']
                    ];
                    $isActive = true;
                    foreach($top_students as $student): ?>
                    <div class="carousel-item <?php echo $isActive ? 'active' : ''; $isActive = false; ?>">
                        <div class="container">
                            <div class="row align-items-center student-slide">
                                <div class="col-md-6 text-center mb-3 mb-md-0">
                                    <img src="<?php echo $student['image']; ?>" alt="<?php echo $student['name']; ?>" class="img-fluid rounded-circle student-image">
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3"><?php echo $student['name']; ?></h5>
                                    <p class="fw-bold text-orange mb-3 fs-4">IELTS <?php echo $student['score']; ?></p>
                                    <p class="lead"><?php echo $student['testimonial']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#topStudentsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Trước</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#topStudentsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Sau</span>
                </button>
            </div>
        </div>
    </div>
</section>

    <section id="departments" class="container py-5 fade-in">
    <h2 class="text-center mb-5">CÁC PHÒNG BAN CHUYÊN MÔN</h2>
    
    <div class="dept-grid">
        <?php 
        // Hiển thị tối đa 4 phòng ban
        $limit = 3;
        $count = 0;
        
        foreach($all_departments as $dept): 
            if($count >= $limit) break;
            
            // Logic map file chi tiết dựa trên ID (Hoặc bạn có thể thêm cột 'filename' vào DB để chuẩn hơn)
            $link = "#";
            switch($dept['id']) {
                case 1: $link = "departments/marketing.php"; break;
                case 2: $link = "departments/hanhchinh.php"; break;
                case 3: $link = "departments/daotao.php"; break;
                case 4: $link = "departments/tuyensinh.php"; break;
                case 5: $link = "departments/congnghe.php"; break;
                default: $link = "departments/full_departments.php"; break;
            }
        ?>
        <div class="dept-card scroll-animate h-100">
            <div style="height: 200px; overflow: hidden; border-radius: 8px 8px 0 0;">
                <img src="<?php echo $dept['image']; ?>" alt="<?php echo $dept['name']; ?>" class="img-fluid w-100 h-100" style="object-fit: cover;" onerror="this.src='https://via.placeholder.com/300x200?text=<?php echo urlencode($dept['name']); ?>';">
            </div>
            <div class="card-body p-3 d-flex flex-column">
                <h5 class="fw-bold text-primary mt-3"><?php echo $dept['name']; ?></h5>
                <p class="text-muted small flex-grow-1"><?php echo substr($dept['description'], 0, 100) . '...'; ?></p>
                <a href="<?php echo $link; ?>" class="btn btn-orange btn-sm mt-2">Xem chi tiết</a>
            </div>
        </div>
        <?php 
            $count++;
        endforeach; 
        ?>
    </div>

    <div class="text-center mt-5">
        <a href="departments/full_departments.php" class="btn btn-outline-orange btn-lg px-4">
            Xem tất cả phòng ban <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<!-- ✅ MODAL HIỂN THỊ TẤT CẢ PHÒNG BAN -->
<div class="modal fade" id="allDepartmentsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tất cả các phòng ban chuyên môn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="dept-grid">
                    <?php foreach($all_departments as $dept): ?>
                    <div class="dept-card">
                        <img src="<?php echo $dept['image']; ?>" alt="<?php echo $dept['name']; ?>" class="img-fluid mb-3 rounded">
                        <h5><?php echo $dept['name']; ?></h5>
                        <p><?php echo $dept['description']; ?></p>
                        <a href="#" class="btn btn-orange btn-sm">Xem chi tiết</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <section class="sponsors-section fade-in">
        <div class="container py-4">
            <div class="sponsors-container">
                <div class="sponsors-marquee">
                    <div class="sponsor-logo"><img src="imgtaitro/VnEconomy.svg" alt="VnEconomy"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/Vnexpress.svg" alt="VnExpress"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/Cafebiz.svg" alt="Giáo Dục"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/apple.jpg" alt="apple"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/nvidia.jpg" alt="nvidia"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/samsung.jpg" alt="samsung"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/VnEconomy.svg" alt="VnEconomy"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/Vnexpress.svg" alt="VnExpress"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/Cafebiz.svg" alt="Giáo Dục"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/apple.jpg" alt="apple"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/nvidia.jpg" alt="nvidia"></div>
                    <div class="sponsor-logo"><img src="imgtaitro/samsung.jpg" alt="samsung"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="news" class="container py-5 fade-in bg-light-subtle">
    <h2 class="text-center mb-5">BẢN TIN & SỰ KIỆN MỚI NHẤT</h2>
    
    <style>
        .news-grid-2-col {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        @media (max-width: 768px) {
            .news-grid-2-col { grid-template-columns: 1fr; }
        }
    </style>

    <?php if(empty($news_chunks)): ?>
        <div class="alert alert-info text-center">Chưa có tin tức nào được cập nhật.</div>
    <?php else: ?>
        <div id="newsCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php 
                $isActive = true;
                foreach($news_chunks as $chunk): 
                ?>
                <div class="carousel-item <?php echo $isActive ? 'active' : ''; $isActive = false; ?>">
                    <div class="news-grid-2-col"> 
                        <?php foreach($chunk as $news): 
                            $news_link = "news_detail.php?id=" . $news['id']; 
                        ?>
                        <div class="news-card scroll-animate h-100 shadow-sm border-0 bg-white">
                            <div style="height: 250px; overflow: hidden; position: relative;">
                                <img src="<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>" class="img-fluid w-100 h-100" style="object-fit: cover;" onerror="this.src='img/default.jpg';">
                                <div class="position-absolute top-0 start-0 bg-orange text-white px-3 py-1 m-3 rounded fw-bold shadow">
                                    <?php echo date('d/m', strtotime($news['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="p-4 d-flex flex-column h-100">
                                <h4 class="news-title fw-bold mb-3">
                                    <a href="<?php echo $news_link; ?>" class="text-decoration-none text-dark hover-orange">
                                        <?php echo $news['title']; ?>
                                    </a>
                                </h4>
                                <p class="text-muted news-desc flex-grow-1" style="font-size: 1rem;">
                                    <?php echo substr($news['summary'], 0, 150) . '...'; ?>
                                </p>
                                <a href="<?php echo $news_link; ?>" class="btn btn-outline-primary mt-auto w-100 fw-bold">
                                    Xem chi tiết <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if(count($news_items) > 2): ?>
            <button class="carousel-control-prev custom-carousel-btn" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev" style="width: 50px; height: 50px; top: 50%; transform: translateY(-50%); background-color: var(--blue); border-radius: 50%; opacity: 0.8;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Trước</span>
            </button>
            <button class="carousel-control-next custom-carousel-btn" type="button" data-bs-target="#newsCarousel" data-bs-slide="next" style="width: 50px; height: 50px; top: 50%; transform: translateY(-50%); background-color: var(--blue); border-radius: 50%; opacity: 0.8;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sau</span>
            </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="text-center mt-5">
        <a href="news/full_news.php" class="btn btn-orange btn-lg px-5 shadow">
            Xem tất cả tin tức
        </a>
    </div>
</section>
    <section class="cta-banner fade-in">
        <div class="container"></div>
    </section>

    <footer class="footer-mentor bg-dark text-light py-5">
    <div class="container">
        <div class="row">
            <!-- Logo and Follow Us -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="logo-section">
                    <img src="images/logo-ielts-school.png" alt="IELTS School" class="mb-3" style="max-width: 150px;" onerror="this.src='https://via.placeholder.com/150x50?text=IELTS+School';"> <!-- Thay bằng logo thật -->
                    <h5 class="fw-bold mb-3">IELTS School</h5>
                    <p class="small mb-3">Trường Anh ngữ chất lượng cao với đội ngũ giảng viên giàu kinh nghiệm và phương pháp giảng dạy hiện đại.</p>
                    <!-- Follow Us Icons -->
                    <div class="social-icons d-flex gap-2">
                        <a href="https://facebook.com" class="text-white p-2 bg-primary rounded-circle"><i class="fab fa-facebook-f fs-5"></i></a>
                        <a href="https://youtube.com" class="text-white p-2 bg-danger rounded-circle"><i class="fab fa-youtube fs-5"></i></a>
                        <a href="https://instagram.com" class="text-white p-2 bg-gradient rounded-circle" style="background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);"><i class="fab fa-instagram fs-5"></i></a>
                        <a href="https://tiktok.com" class="text-white p-2 bg-black rounded-circle"><i class="fab fa-tiktok fs-5"></i></a>
                        <a href="https://zalo.me" class="text-white p-2 bg-green rounded-circle"><i class="fab fa-whatsapp fs-5"></i></a> <!-- Icon Zalo tương tự WhatsApp -->
                    </div>
                </div>
            </div>

            <!-- Column 1: Danh sách khóa học -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-3">Danh sách khóa học</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Khóa Basic</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Khóa Tiếng cao cấp</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Pre IELTS</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">IELTS 3.5-4.5</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">IELTS 4.5-5.5</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">IELTS 5.5-6.5+</a></li>
                </ul>
            </div>

            <!-- Column 2: Catalog -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-3">Catalog</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Catalogue khóa học</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Giáo viên</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Cơ sở vật chất</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Chính sách học phí</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Học bổng</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Đăng ký</a></li>
                </ul>
            </div>

            <!-- Column 3: Blog for Mentee -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="fw-bold mb-3">Blog </h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sử dụng app học viên</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Blog</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Chia sẻ kinh nghiệm</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Tips ôn IELTS</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Câu chuyện thành công</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none">Hỏi đáp</a></li>
                </ul>
            </div>
        </div>

        <!-- Contact Info Row -->
        <hr class="my-4">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h5 class="fw-bold mb-3">Thông Tin Liên Hệ</h5>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><i class="fas fa-map-marker-alt text-orange me-2"></i>Địa chỉ: Ô diên , Hà Nội</li>
                    <li class="mb-2"><i class="fas fa-phone text-orange me-2"></i>Điện thoại: +84 862 751 689</li>
                    <li class="mb-2"><i class="fas fa-envelope text-orange me-2"></i>Email: ieltschool@gmail.com</li>
                    <li class="mb-2"><i class="fas fa-clock text-orange me-2"></i>Giờ làm việc: Thứ 2 - Thứ 6, 9:00 - 18:00</li>
                </ul>
            </div>
            <div class="col-lg-6 text-end">
                <h5 class="fw-bold mb-3">Kết Nối Với Chúng Tôi</h5>
                <div class="social-icons d-flex justify-content-end gap-2">
                    <a href="index.php#home" class="btn btn-outline-light btn-sm"><i class="fas fa-home me-1"></i> Trang chủ</a>
                    <a href="index.php#about" class="btn btn-outline-light btn-sm"><i class="fas fa-info-circle me-1"></i> Về chúng tôi</a>
                    <a href="https://youtube.com" class="btn btn-danger btn-sm"><i class="fab fa-youtube me-1"></i> YouTube</a>
                    <a href="https://facebook.com" class="btn btn-primary btn-sm"><i class="fab fa-facebook me-1"></i> Fanpage</a>
                    <a href="login.php" class="btn btn-orange btn-sm"><i class="fas fa-sign-in-alt me-1"></i> Đăng nhập</a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <hr class="my-4">
        <div class="text-center">
            <p class="mb-0 small">&copy; 2025 IELTS School. All Rights Reserved. | Designed by ddmq</p>
        </div>
    </div>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        // Animate numbers in stats section (nếu chưa có trong script.js)
        document.addEventListener('DOMContentLoaded', function() {
            const numbers = document.querySelectorAll('.number');
            numbers.forEach(number => {
                const target = parseFloat(number.getAttribute('data-target'));
                const increment = target / 100; // Tốc độ animate
                let current = 0;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    number.textContent = current.toFixed(target % 1 === 0 ? 0 : 1); // Định dạng số
                }, 20);
            });
        });
    </script>
</body>
</html>