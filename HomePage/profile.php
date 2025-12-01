<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// --- XỬ LÝ GỬI YÊU CẦU (Dùng chung logic) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Hàm hỗ trợ upload ảnh
    function uploadFile($fileInputName, $prefix) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $target = "img/proofs/" . time() . "_{$prefix}_" . basename($_FILES[$fileInputName]['name']);
            if (!is_dir('img/proofs')) mkdir('img/proofs', 0777, true);
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $target)) return $target;
        }
        return null;
    }

    $req_type = '';
    $data = [];

    // 1. Yêu cầu Sửa thông tin Cá nhân
    if (isset($_POST['req_personal'])) {
        $req_type = 'personal';
        $data = [
            'full_name' => $_POST['full_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'dob' => $_POST['dob'],
            'address' => $_POST['address'],
            'gender' => $_POST['gender'],
            'nationality' => $_POST['nationality'],
            'marital_status' => $_POST['marital_status'],
            'zalo' => $_POST['zalo'],
            'current_address' => $_POST['current_address'],
            'hometown' => $_POST['hometown'],
            'biography' => $_POST['biography']
        ];
        // Avatar
        $avatar = uploadFile('avatar_img', 'avt');
        if($avatar) $data['avatar'] = $avatar;
    }

    // 2. Yêu cầu Sửa Chuyên môn
    if (isset($_POST['req_teaching'])) {
        $req_type = 'teaching';
        $data = [
            'education_level' => $_POST['education_level'],
            'major' => $_POST['major'],
            'certificate_type' => $_POST['certificate_type'],
            'certificate_score' => $_POST['certificate_score'],
            'main_subject' => $_POST['main_subject'],
            'teaching_band' => $_POST['teaching_band'],
            'demo_video_link' => $_POST['demo_video_link']
        ];
        // Ảnh bằng cấp
        $edu_proof = uploadFile('edu_img', 'edu');
        if($edu_proof) $data['edu_proof'] = $edu_proof;
        
        $cert_proof = uploadFile('cert_img', 'cert');
        if($cert_proof) $data['cert_proof'] = $cert_proof;
    }

    // 3. Yêu cầu Thêm Giấy tờ Pháp lý
    if (isset($_POST['req_legal'])) {
        $req_type = 'legal';
        $data = [
            'doc_type' => $_POST['doc_type'],
            'doc_number' => $_POST['doc_number'],
            'issue_date' => $_POST['issue_date'],
            'expiry_date' => $_POST['expiry_date'],
            'place_of_issue' => $_POST['place_of_issue']
        ];
        $front = uploadFile('file_front', 'doc_front');
        if($front) $data['doc_file_front'] = $front;
        $back = uploadFile('file_back', 'doc_back');
        if($back) $data['doc_file_back'] = $back;
    }

    // 4. Yêu cầu Sửa Bảo hiểm
    if (isset($_POST['req_insurance'])) {
        $req_type = 'insurance';
        $data = [
            'social_status' => $_POST['social_status'],
            'social_book_number' => $_POST['social_book_number'],
            'health_card_number' => $_POST['health_card_number'],
            'hospital_reg' => $_POST['hospital_reg']
        ];
    }

    // 5. Yêu cầu Thêm Người thân
    if (isset($_POST['req_contact'])) {
        $req_type = 'contact';
        $data = [
            'name' => $_POST['contact_name'],
            'relationship' => $_POST['relationship'],
            'phone' => $_POST['contact_phone'],
            'address' => $_POST['contact_address']
        ];
    }

    // LƯU VÀO DB
    if ($req_type && !empty($data)) {
        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO profile_requests (user_id, type, data_content, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iss", $user_id, $req_type, $json_data);
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Đã gửi yêu cầu cập nhật <strong>$req_type</strong> thành công!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}

// --- LẤY DỮ LIỆU HIỂN THỊ ---
$user = $conn->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id=$user_id")->fetch_assoc();
$detail = $conn->query("SELECT * FROM employee_details WHERE user_id=$user_id")->fetch_assoc();
$teaching = $conn->query("SELECT * FROM teaching_profile WHERE user_id=$user_id")->fetch_assoc();
$legal_docs = $conn->query("SELECT * FROM legal_documents WHERE user_id=$user_id");
$contracts = $conn->query("SELECT * FROM labor_contracts WHERE user_id=$user_id");
$insurance = $conn->query("SELECT * FROM insurance WHERE user_id=$user_id")->fetch_assoc();
$contacts = $conn->query("SELECT * FROM emergency_contacts WHERE user_id=$user_id");

// Lấy các yêu cầu đang chờ (để hiển thị trạng thái)
$pending_reqs = [];
$res_pending = $conn->query("SELECT type FROM profile_requests WHERE user_id=$user_id AND status='pending'");
while($r = $res_pending->fetch_assoc()) $pending_reqs[] = $r['type'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-user-circle text-primary"></i> Hồ sơ của tôi</h3>
            <a href="user_dashboard.php" class="btn btn-secondary">Quay lại Dashboard</a>
        </div>

        <?php echo $msg; ?>

        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm mb-3">
                    <div class="card-body text-center">
                        <img src="<?php echo $user['avatar'] ?? 'img/default.jpg'; ?>" class="rounded-circle mb-3 border" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5><?php echo $user['full_name']; ?></h5>
                        <span class="badge bg-primary"><?php echo $user['role_name']; ?></span>
                    </div>
                </div>
                
                <?php if(!empty($pending_reqs)): ?>
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark fw-bold"><i class="fas fa-clock"></i> Đang chờ duyệt</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach($pending_reqs as $type): ?>
                            <li class="list-group-item small">Cập nhật: <strong><?php echo ucfirst($type); ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab1">1. Cá nhân</button></li>
                            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab2">2. Chuyên môn</button></li>
                            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab3">3. Pháp lý</button></li>
                            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab4">4. Hợp đồng</button></li>
                            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab5">5. Bảo hiểm</button></li>
                            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab6">6. Người thân</button></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="tab1">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-12 border-bottom pb-2 text-primary fw-bold">Thông tin cơ bản</div>
                                        <div class="col-md-6"><label>Họ tên</label><input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required></div>
                                        <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required></div>
                                        <div class="col-md-6"><label>SĐT</label><input type="text" name="phone" class="form-control" value="<?php echo $detail['phone'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Ngày sinh</label><input type="date" name="dob" class="form-control" value="<?php echo $detail['dob'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Giới tính</label>
                                            <select name="gender" class="form-select">
                                                <option value="Nam" <?php echo ($detail['gender']??'')=='Nam'?'selected':''; ?>>Nam</option>
                                                <option value="Nữ" <?php echo ($detail['gender']??'')=='Nữ'?'selected':''; ?>>Nữ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6"><label>Quốc tịch</label><input type="text" name="nationality" class="form-control" value="<?php echo $detail['nationality'] ?? 'Việt Nam'; ?>"></div>
                                        <div class="col-12"><label>Địa chỉ hiện tại</label><input type="text" name="current_address" class="form-control" value="<?php echo $detail['current_address'] ?? ''; ?>"></div>
                                        <div class="col-12"><label>Tiểu sử</label><textarea name="biography" class="form-control"><?php echo $detail['biography'] ?? ''; ?></textarea></div>
                                        
                                        <div class="col-12 mt-3"><label class="fw-bold">Ảnh đại diện mới (Nếu đổi)</label><input type="file" name="avatar_img" class="form-control"></div>

                                        <div class="col-12 text-end mt-3">
                                            <button type="submit" name="req_personal" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi yêu cầu sửa</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab2">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label>Trình độ</label><select name="education_level" class="form-select"><option value="Đại học" selected>Đại học</option><option value="Thạc sĩ">Thạc sĩ</option></select></div>
                                        <div class="col-md-6"><label>Chuyên ngành</label><input type="text" name="major" class="form-control" value="<?php echo $detail['major'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Chứng chỉ NN</label><select name="certificate_type" class="form-select"><option value="None">Không</option><option value="IELTS">IELTS</option><option value="TOEIC">TOEIC</option></select></div>
                                        <div class="col-md-6"><label>Điểm số</label><input type="number" step="0.5" name="certificate_score" class="form-control" value="<?php echo $detail['certificate_score'] ?? ''; ?>"></div>
                                        
                                        <div class="col-md-6"><label>Ảnh Bằng cấp</label><input type="file" name="edu_img" class="form-control"></div>
                                        <div class="col-md-6"><label>Ảnh Chứng chỉ</label><input type="file" name="cert_img" class="form-control"></div>

                                        <div class="col-12 border-top pt-3 mt-3"><label class="fw-bold">Năng lực giảng dạy</label></div>
                                        <div class="col-md-6"><label>Môn dạy</label><input type="text" name="main_subject" class="form-control" value="<?php echo $teaching['main_subject'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Band dạy</label><input type="text" name="teaching_band" class="form-control" value="<?php echo $teaching['teaching_band'] ?? ''; ?>"></div>
                                        <div class="col-12"><label>Link Demo Video</label><input type="text" name="demo_video_link" class="form-control" value="<?php echo $teaching['demo_video_link'] ?? ''; ?>"></div>

                                        <div class="col-12 text-end mt-3">
                                            <button type="submit" name="req_teaching" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi yêu cầu sửa</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab3">
                                <h6 class="text-success">Giấy tờ đã lưu</h6>
                                <table class="table table-sm table-bordered mb-3">
                                    <thead><tr><th>Loại</th><th>Số</th><th>Hết hạn</th></tr></thead>
                                    <tbody>
                                        <?php while($d = $legal_docs->fetch_assoc()): ?>
                                            <tr><td><?php echo $d['doc_type']; ?></td><td><?php echo $d['doc_number']; ?></td><td><?php echo $d['expiry_date']; ?></td></tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <hr>
                                <h6>Gửi yêu cầu thêm Giấy tờ mới</h6>
                                <form method="POST" enctype="multipart/form-data" class="row g-2">
                                    <div class="col-md-3"><select name="doc_type" class="form-select"><option>CCCD</option><option>Hộ chiếu</option></select></div>
                                    <div class="col-md-3"><input type="text" name="doc_number" class="form-control" placeholder="Số giấy tờ" required></div>
                                    <div class="col-md-3"><input type="date" name="issue_date" class="form-control" title="Ngày cấp"></div>
                                    <div class="col-md-3"><input type="date" name="expiry_date" class="form-control" title="Hết hạn"></div>
                                    <div class="col-md-6"><input type="file" name="file_front" class="form-control" title="Mặt trước"></div>
                                    <div class="col-md-6"><input type="file" name="file_back" class="form-control" title="Mặt sau"></div>
                                    <div class="col-12 text-end"><button type="submit" name="req_legal" class="btn btn-success">Gửi thêm</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab4">
                                <div class="alert alert-info">Thông tin hợp đồng. Vui lòng liên hệ Nhân sự nếu có sai sót.</div>
                                <table class="table table-bordered">
                                    <thead><tr><th>Số HĐ</th><th>Loại</th><th>Thời hạn</th><th>Lương cứng</th></tr></thead>
                                    <tbody>
                                        <?php while($ct = $contracts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $ct['contract_number']; ?></td>
                                            <td><?php echo $ct['contract_type']; ?></td>
                                            <td><?php echo $ct['start_date'] . ' -> ' . $ct['end_date']; ?></td>
                                            <td><?php echo number_format($ct['base_salary']); ?> đ</td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="tab5">
                                <form method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label>Trạng thái BHXH</label><select name="social_status" class="form-select"><option value="Có đóng">Có đóng</option><option value="Không đóng">Không đóng</option></select></div>
                                        <div class="col-md-6"><label>Số sổ BHXH</label><input type="text" name="social_book_number" class="form-control" value="<?php echo $insurance['social_book_number'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Mã thẻ BHYT</label><input type="text" name="health_card_number" class="form-control" value="<?php echo $insurance['health_card_number'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Nơi KCB</label><input type="text" name="hospital_reg" class="form-control" value="<?php echo $insurance['hospital_reg'] ?? ''; ?>"></div>
                                        <div class="col-12 text-end mt-3"><button type="submit" name="req_insurance" class="btn btn-primary">Gửi yêu cầu sửa</button></div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab6">
                                <table class="table table-striped table-sm mb-3">
                                    <thead><tr><th>Họ tên</th><th>Quan hệ</th><th>SĐT</th></tr></thead>
                                    <tbody>
                                        <?php while($ct = $contacts->fetch_assoc()): ?>
                                        <tr><td><?php echo $ct['name']; ?></td><td><?php echo $ct['relationship']; ?></td><td><?php echo $ct['phone']; ?></td></tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <hr>
                                <h6>Thêm người thân mới</h6>
                                <form method="POST" class="row g-2">
                                    <div class="col-md-4"><input type="text" name="contact_name" class="form-control" placeholder="Họ tên" required></div>
                                    <div class="col-md-3"><input type="text" name="relationship" class="form-control" placeholder="Quan hệ" required></div>
                                    <div class="col-md-3"><input type="text" name="contact_phone" class="form-control" placeholder="SĐT" required></div>
                                    <div class="col-md-2"><button type="submit" name="req_contact" class="btn btn-success w-100">Thêm</button></div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>