<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// --- XỬ LÝ GỬI YÊU CẦU CẬP NHẬT HỒ SƠ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Tab Cá nhân
    if (isset($_POST['req_personal'])) {
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => $_POST['phone'] ?? '',
            'dob' => $_POST['dob'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'nationality' => $_POST['nationality'] ?? '',
            'marital_status' => $_POST['marital_status'] ?? '',
            'zalo' => $_POST['zalo'] ?? '',
            'current_address' => $_POST['current_address'] ?? '',
            'hometown' => $_POST['hometown'] ?? '',
            'biography' => $_POST['biography'] ?? ''
        ];

        // Xử lý upload ảnh đại diện (Avatar)
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $target = "img/avatars/" . time() . "_" . basename($_FILES['avatar']['name']);
            if (!is_dir('img/avatars')) mkdir('img/avatars', 0777, true);
            move_uploaded_file($_FILES['avatar']['tmp_name'], $target);
            $data['avatar'] = $target;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO profile_requests (user_id, type, data_content) VALUES (?, 'personal', ?)");
        $stmt->bind_param("is", $user_id, $json);
        
        if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã gửi yêu cầu cập nhật Thông tin cá nhân!</div>";
        else $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }

    // 2. Tab Chuyên môn
    if (isset($_POST['req_teaching'])) {
        $data = [
            'education_level' => $_POST['education_level'] ?? '',
            'major' => $_POST['major'] ?? '',
            'certificate_type' => $_POST['certificate_type'] ?? '',
            'certificate_score' => $_POST['certificate_score'] ?? '',
            'main_subject' => $_POST['main_subject'] ?? '',
            'teaching_band' => $_POST['teaching_band'] ?? '',
            'demo_video_link' => $_POST['demo_video_link'] ?? ''
        ];

        // Upload ảnh bằng cấp/chứng chỉ
        if (isset($_FILES['edu_proof']) && $_FILES['edu_proof']['error'] == 0) {
            $t = "img/proofs/" . time() . "_edu_" . basename($_FILES['edu_proof']['name']);
            if (!is_dir('img/proofs')) mkdir('img/proofs', 0777, true);
            move_uploaded_file($_FILES['edu_proof']['tmp_name'], $t);
            $data['edu_proof'] = $t;
        }
        if (isset($_FILES['cert_proof']) && $_FILES['cert_proof']['error'] == 0) {
            $t = "img/proofs/" . time() . "_cert_" . basename($_FILES['cert_proof']['name']);
            if (!is_dir('img/proofs')) mkdir('img/proofs', 0777, true);
            move_uploaded_file($_FILES['cert_proof']['tmp_name'], $t);
            $data['cert_proof'] = $t;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO profile_requests (user_id, type, data_content) VALUES (?, 'teaching', ?)");
        $stmt->bind_param("is", $user_id, $json);

        if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã gửi yêu cầu cập nhật Hồ sơ chuyên môn!</div>";
    }

    // 3. Tab Giấy tờ (Pháp lý)
    if (isset($_POST['req_legal'])) {
        $data = [
            'doc_type' => $_POST['doc_type'] ?? '',
            'doc_number' => $_POST['doc_number'] ?? '',
            'issue_date' => $_POST['issue_date'] ?? '',
            'place_of_issue' => $_POST['place_of_issue'] ?? '',
            'expiry_date' => $_POST['expiry_date'] ?? ''
        ];

        if (isset($_FILES['doc_front']) && $_FILES['doc_front']['error'] == 0) {
            $t = "img/proofs/" . time() . "_front_" . basename($_FILES['doc_front']['name']);
            if (!is_dir('img/proofs')) mkdir('img/proofs', 0777, true);
            move_uploaded_file($_FILES['doc_front']['tmp_name'], $t);
            $data['doc_file_front'] = $t;
        }
        if (isset($_FILES['doc_back']) && $_FILES['doc_back']['error'] == 0) {
            $t = "img/proofs/" . time() . "_back_" . basename($_FILES['doc_back']['name']);
            if (!is_dir('img/proofs')) mkdir('img/proofs', 0777, true);
            move_uploaded_file($_FILES['doc_back']['tmp_name'], $t);
            $data['doc_file_back'] = $t;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO profile_requests (user_id, type, data_content) VALUES (?, 'legal', ?)");
        $stmt->bind_param("is", $user_id, $json);
        
        if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã gửi giấy tờ mới chờ duyệt!</div>";
    }

    // 4. Tab Bảo hiểm
    if (isset($_POST['req_insurance'])) {
        $data = [
            'social_status' => $_POST['social_status'] ?? '',
            'social_book_number' => $_POST['social_book_number'] ?? '',
            'health_card_number' => $_POST['health_card_number'] ?? '',
            'hospital_reg' => $_POST['hospital_reg'] ?? ''
        ];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO profile_requests (user_id, type, data_content) VALUES (?, 'insurance', ?)");
        $stmt->bind_param("is", $user_id, $json);
        if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã gửi cập nhật thông tin Bảo hiểm!</div>";
    }

    // 5. Tab Người thân
    if (isset($_POST['req_contact'])) {
        $data = [
            'name' => $_POST['contact_name'] ?? '',
            'relationship' => $_POST['relationship'] ?? '',
            'phone' => $_POST['contact_phone'] ?? '',
            'address' => $_POST['contact_address'] ?? ''
        ];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO profile_requests (user_id, type, data_content) VALUES (?, 'contact', ?)");
        $stmt->bind_param("is", $user_id, $json);
        if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã thêm người thân mới (Chờ duyệt)!</div>";
    }
}

// --- LẤY DỮ LIỆU HIỂN THỊ ---
$user = $conn->query("SELECT u.*, r.name as role_name, d.name as dept_name FROM users u LEFT JOIN roles r ON u.role_id=r.id LEFT JOIN departments d ON u.department_id=d.id WHERE u.id=$user_id")->fetch_assoc();
$detail = $conn->query("SELECT * FROM employee_details WHERE user_id=$user_id")->fetch_assoc();
$teaching = $conn->query("SELECT * FROM teaching_profile WHERE user_id=$user_id")->fetch_assoc();
$legal_docs = $conn->query("SELECT * FROM legal_documents WHERE user_id=$user_id");
$contracts = $conn->query("SELECT * FROM labor_contracts WHERE user_id=$user_id ORDER BY start_date DESC");
$insurance = $conn->query("SELECT * FROM insurance WHERE user_id=$user_id")->fetch_assoc();
$contacts = $conn->query("SELECT * FROM emergency_contacts WHERE user_id=$user_id");
$pending_reqs = $conn->query("SELECT * FROM profile_requests WHERE user_id=$user_id AND status='pending'");

// Helper lấy giá trị an toàn cho Chứng chỉ
$curr_cert = $detail['certificate_type'] ?? 'None';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .nav-pills .nav-link.active { background-color: #0d6efd; }
        .tab-content { padding: 20px; background: #fff; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 5px 5px; }
        .avatar-upload { position: relative; max-width: 150px; margin: 0 auto; }
        .avatar-edit { position: absolute; right: 0; bottom: 0; }
        .profile-header { background: linear-gradient(135deg, #0d6efd, #0099ff); color: white; padding: 30px 0; border-radius: 10px 10px 0 0; }
    </style>
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="user_dashboard.php"><i class="fas fa-chevron-left"></i> Quay lại Dashboard</a>
        </div>
    </nav>

    <div class="container mb-5">
        <?php echo $msg; ?>

        <?php if($pending_reqs->num_rows > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i> Bạn đang có <strong><?php echo $pending_reqs->num_rows; ?></strong> yêu cầu cập nhật đang chờ Admin duyệt.
            </div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="profile-header text-center">
                    <img src="<?php echo $user['avatar'] ?? 'img/default.jpg'; ?>" class="rounded-circle border border-3 border-white shadow" style="width: 120px; height: 120px; object-fit: cover;">
                    <h3 class="mt-3 fw-bold"><?php echo $user['full_name']; ?></h3>
                    <p class="mb-1 opacity-75"><?php echo $user['role_name']; ?> - <?php echo $user['dept_name']; ?></p>
                    <span class="badge bg-light text-primary"><?php echo $user['email']; ?></span>
                </div>

                <div class="p-4">
                    <ul class="nav nav-pills nav-fill mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="pill" data-bs-target="#tab-personal">1. Cá nhân</button></li>
                        <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="pill" data-bs-target="#tab-teaching">2. Chuyên môn</button></li>
                        <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="pill" data-bs-target="#tab-legal">3. Giấy tờ</button></li>
                        <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="pill" data-bs-target="#tab-contract">4. Hợp đồng</button></li>
                        <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="pill" data-bs-target="#tab-insurance">5. Bảo hiểm</button></li>
                        <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="pill" data-bs-target="#tab-contact">6. Người thân</button></li>
                    </ul>

                    <div class="tab-content border rounded">
                        
                        <div class="tab-pane fade show active" id="tab-personal">
                            <form method="POST" enctype="multipart/form-data">
                                <h6 class="text-primary border-bottom pb-2">Thông tin cơ bản (Gửi yêu cầu sửa)</h6>
                                <div class="row g-3">
                                    <div class="col-md-6"><label>Họ tên</label><input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>"></div>
                                    <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>"></div>
                                    <div class="col-md-4"><label>Ngày sinh</label><input type="date" name="dob" class="form-control" value="<?php echo $detail['dob'] ?? ''; ?>"></div>
                                    <div class="col-md-4"><label>Giới tính</label><select name="gender" class="form-select"><option value="Nam" <?php echo ($detail['gender']??'')=='Nam'?'selected':''; ?>>Nam</option><option value="Nữ" <?php echo ($detail['gender']??'')=='Nữ'?'selected':''; ?>>Nữ</option></select></div>
                                    <div class="col-md-4"><label>Quốc tịch</label><input type="text" name="nationality" class="form-control" value="<?php echo $detail['nationality'] ?? 'Việt Nam'; ?>"></div>
                                    
                                    <div class="col-md-4"><label>SĐT</label><input type="text" name="phone" class="form-control" value="<?php echo $detail['phone'] ?? ''; ?>"></div>
                                    <div class="col-md-4"><label>Zalo</label><input type="text" name="zalo" class="form-control" value="<?php echo $detail['zalo'] ?? ''; ?>"></div>
                                    <div class="col-md-4"><label>Hôn nhân</label><select name="marital_status" class="form-select"><option value="Độc thân">Độc thân</option><option value="Đã kết hôn">Đã kết hôn</option></select></div>
                                    
                                    <div class="col-md-12"><label>Địa chỉ</label><input type="text" name="current_address" class="form-control" value="<?php echo $detail['current_address'] ?? ''; ?>"></div>
                                    <div class="col-md-12"><label>Quê quán</label><input type="text" name="hometown" class="form-control" value="<?php echo $detail['hometown'] ?? ''; ?>"></div>
                                    
                                    <div class="col-12 mt-3">
                                        <label class="fw-bold">Thay đổi ảnh đại diện</label>
                                        <input type="file" name="avatar" class="form-control">
                                    </div>
                                </div>
                                <div class="mt-3 text-end"><button type="submit" name="req_personal" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi yêu cầu cập nhật</button></div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-teaching">
                            <form method="POST" enctype="multipart/form-data">
                                <h6 class="text-primary border-bottom pb-2">Hồ sơ Giảng dạy & Bằng cấp</h6>
                                <div class="row g-3">
                                    <div class="col-md-6"><label>Môn dạy chính</label><input type="text" name="main_subject" class="form-control" value="<?php echo $teaching['main_subject'] ?? ''; ?>"></div>
                                    <div class="col-md-6"><label>Band điểm</label><input type="text" name="teaching_band" class="form-control" value="<?php echo $teaching['teaching_band'] ?? ''; ?>"></div>
                                    <div class="col-md-12"><label>Link Demo Video</label><input type="text" name="demo_video_link" class="form-control" value="<?php echo $teaching['demo_video_link'] ?? ''; ?>"></div>
                                    
                                    <div class="col-md-3"><label>Trình độ</label><select name="education_level" class="form-select"><option value="Đại học">Đại học</option><option value="Thạc sĩ">Thạc sĩ</option></select></div>
                                    <div class="col-md-3"><label>Chuyên ngành</label><input type="text" name="major" class="form-control" value="<?php echo $detail['major'] ?? ''; ?>"></div>
                                    
                                    <div class="col-md-3">
                                        <label>Chứng chỉ</label>
                                        <select name="certificate_type" class="form-select">
                                            <option value="None" <?php echo ($curr_cert == 'None') ? 'selected' : ''; ?>>Không</option>
                                            <option value="IELTS" <?php echo ($curr_cert == 'IELTS') ? 'selected' : ''; ?>>IELTS</option>
                                            <option value="TOEIC" <?php echo ($curr_cert == 'TOEIC') ? 'selected' : ''; ?>>TOEIC</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3"><label>Điểm số</label><input type="number" step="0.5" name="certificate_score" class="form-control" value="<?php echo $detail['certificate_score'] ?? ''; ?>"></div>

                                    <div class="col-md-6"><label>Ảnh Bằng cấp (Minh chứng)</label><input type="file" name="edu_proof" class="form-control"></div>
                                    <div class="col-md-6"><label>Ảnh Chứng chỉ (Minh chứng)</label><input type="file" name="cert_proof" class="form-control"></div>
                                </div>
                                <div class="mt-3 text-end"><button type="submit" name="req_teaching" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi yêu cầu cập nhật</button></div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-legal">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Loại</th><th>Số</th><th>Ngày cấp</th><th>Nơi cấp</th><th>Hết hạn</th><th>File</th></tr></thead>
                                <tbody>
                                    <?php while($d = $legal_docs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $d['doc_type']; ?></td>
                                        <td><?php echo $d['doc_number']; ?></td>
                                        <td><?php echo $d['issue_date']; ?></td>
                                        <td><?php echo $d['place_of_issue']; ?></td>
                                        <td><?php echo $d['expiry_date']; ?></td>
                                        <td><?php if($d['doc_file_front']) echo "<a href='{$d['doc_file_front']}' target='_blank'>Xem</a>"; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <hr>
                            <h6>Bổ sung giấy tờ mới</h6>
                            <form method="POST" enctype="multipart/form-data" class="row g-2">
                                <div class="col-md-2"><select name="doc_type" class="form-select"><option>CCCD</option><option>Visa</option></select></div>
                                <div class="col-md-3"><input type="text" name="doc_number" class="form-control" placeholder="Số giấy tờ" required></div>
                                <div class="col-md-2"><input type="date" name="issue_date" class="form-control" title="Ngày cấp"></div>
                                <div class="col-md-3"><input type="text" name="place_of_issue" class="form-control" placeholder="Nơi cấp"></div>
                                <div class="col-md-2"><input type="date" name="expiry_date" class="form-control" title="Hết hạn"></div>
                                <div class="col-md-6"><input type="file" name="doc_front" class="form-control" title="Mặt trước"></div>
                                <div class="col-md-6"><input type="file" name="doc_back" class="form-control" title="Mặt sau"></div>
                                <div class="col-12 text-end"><button type="submit" name="req_legal" class="btn btn-success">Gửi thêm</button></div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-contract">
                            <table class="table table-bordered">
                                <thead class="table-light"><tr><th>Số HĐ</th><th>Loại</th><th>Ngày ký</th><th>Hết hạn</th><th>Lương cứng</th><th>File</th></tr></thead>
                                <tbody>
                                    <?php while($ct = $contracts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $ct['contract_number']; ?></td>
                                        <td><?php echo $ct['contract_type']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($ct['start_date'])); ?></td>
                                        <td><?php echo $ct['end_date'] ? date('d/m/Y', strtotime($ct['end_date'])) : 'Vô thời hạn'; ?></td>
                                        <td class="fw-bold"><?php echo number_format($ct['base_salary']); ?></td>
                                        <td><?php if($ct['contract_file']) echo "<a href='{$ct['contract_file']}' target='_blank'>PDF</a>"; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="alert alert-info small"><i class="fas fa-info-circle"></i> Vui lòng liên hệ trực tiếp phòng Nhân sự nếu có sai sót về Hợp đồng.</div>
                        </div>

                        <div class="tab-pane fade" id="tab-insurance">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-bold">Trạng thái</label>
                                        <input type="text" class="form-control fw-bold text-primary" value="<?php echo $insurance['social_status'] ?? 'Chưa cập nhật'; ?>" readonly>
                                        <input type="hidden" name="social_status" value="<?php echo $insurance['social_status'] ?? 'Không đóng'; ?>">
                                    </div>
                                    <div class="col-md-6"><label>Số sổ BHXH (Nếu cần sửa)</label><input type="text" name="social_book_number" class="form-control" value="<?php echo $insurance['social_book_number'] ?? ''; ?>"></div>
                                    <div class="col-md-6"><label>Mã thẻ BHYT</label><input type="text" name="health_card_number" class="form-control" value="<?php echo $insurance['health_card_number'] ?? ''; ?>"></div>
                                    <div class="col-md-6"><label>Nơi ĐK KCB</label><input type="text" name="hospital_reg" class="form-control" value="<?php echo $insurance['hospital_reg'] ?? ''; ?>"></div>
                                </div>
                                <div class="mt-3 text-end"><button type="submit" name="req_insurance" class="btn btn-primary">Gửi cập nhật</button></div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-contact">
                            <table class="table table-striped">
                                <thead><tr><th>Họ tên</th><th>Quan hệ</th><th>SĐT</th><th>Địa chỉ</th></tr></thead>
                                <tbody>
                                    <?php while($c = $contacts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $c['name']; ?></td>
                                        <td><?php echo $c['relationship']; ?></td>
                                        <td><?php echo $c['phone']; ?></td>
                                        <td><?php echo $c['address']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <hr>
                            <form method="POST" class="row g-2">
                                <div class="col-md-4"><input type="text" name="contact_name" class="form-control" placeholder="Họ tên" required></div>
                                <div class="col-md-2"><input type="text" name="relationship" class="form-control" placeholder="Quan hệ" required></div>
                                <div class="col-md-3"><input type="text" name="contact_phone" class="form-control" placeholder="SĐT" required></div>
                                <div class="col-md-3"><input type="text" name="contact_address" class="form-control" placeholder="Địa chỉ"></div>
                                <div class="col-12 mt-2 text-end"><button type="submit" name="req_contact" class="btn btn-success">Thêm mới</button></div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>