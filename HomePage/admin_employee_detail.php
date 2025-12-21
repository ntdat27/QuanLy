<?php
session_start();
require_once 'db_connect.php';

// Check quyền Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    header("Location: index.php");
    exit();
}

$user_id = $_GET['id'] ?? 0;
$msg = "";

// --- XỬ LÝ 1: CẬP NHẬT TÀI KHOẢN & VAI TRÒ (User Account) ---
if (isset($_POST['update_account'])) {
    $role_id = $_POST['role_id'];
    $status = $_POST['status'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    if ($user_id == 1 && $role_id != 1) {
        $msg = "<div class='alert alert-danger'>Không thể thay đổi quyền của Super Admin!</div>";
    } else {
        $sql = "UPDATE users SET role_id=?, status=?, full_name=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $role_id, $status, $full_name, $email, $user_id);
        
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Đã cập nhật Tài khoản & Phân quyền!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}

// --- XỬ LÝ 2: CÁC TAB HỒ SƠ CHI TIẾT ---

// Tab 1: Cá nhân & Lý lịch
if (isset($_POST['save_tab1'])) {
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $nation = $_POST['nationality'] ?? '';
    $marital = $_POST['marital_status'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $zalo = $_POST['zalo'] ?? '';
    $addr = $_POST['current_address'] ?? '';
    $home = $_POST['hometown'] ?? '';
    $crm_status = $_POST['criminal_record_status'] ?? '';
    $crm_num = $_POST['criminal_record_number'] ?? '';
    $crm_date = $_POST['criminal_record_date'] ?? '';

    $crm_file = $_POST['current_crm_file'] ?? '';
    if (isset($_FILES['crm_file_up']) && $_FILES['crm_file_up']['error'] == 0) {
        $target = "img/docs/" . time() . "_crm_" . basename($_FILES['crm_file_up']['name']);
        if (!is_dir('img/docs')) mkdir('img/docs', 0777, true);
        if (move_uploaded_file($_FILES['crm_file_up']['tmp_name'], $target)) $crm_file = $target;
    }

    $check = $conn->query("SELECT user_id FROM employee_details WHERE user_id=$user_id");
    if($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE employee_details SET dob=?, gender=?, nationality=?, marital_status=?, phone=?, zalo=?, current_address=?, hometown=?, criminal_record_status=?, criminal_record_number=?, criminal_record_date=?, criminal_record_file=? WHERE user_id=?");
        $stmt->bind_param("ssssssssssssi", $dob, $gender, $nation, $marital, $phone, $zalo, $addr, $home, $crm_status, $crm_num, $crm_date, $crm_file, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO employee_details (dob, gender, nationality, marital_status, phone, zalo, current_address, hometown, criminal_record_status, criminal_record_number, criminal_record_date, criminal_record_file, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssi", $dob, $gender, $nation, $marital, $phone, $zalo, $addr, $home, $crm_status, $crm_num, $crm_date, $crm_file, $user_id);
    }
    if($stmt->execute()) $msg = "<div class='alert alert-success'>Đã lưu thông tin cá nhân!</div>";
}

// Tab 2: Chuyên môn (SỬA LỖI Ở ĐÂY)
if (isset($_POST['save_tab2'])) {
    $main_sub = $_POST['main_subject'] ?? '';
    $band = $_POST['teaching_band'] ?? '';
    $demo = $_POST['demo_video_link'] ?? '';
    $edu = $_POST['education_level'] ?? '';
    $major = $_POST['major'] ?? '';
    $cert_type = $_POST['certificate_type'] ?? '';
    $cert_score = $_POST['certificate_score'] ?? 0;

    $conn->query("UPDATE employee_details SET education_level='$edu', major='$major', certificate_type='$cert_type', certificate_score='$cert_score' WHERE user_id=$user_id");

    $check = $conn->query("SELECT user_id FROM teaching_profile WHERE user_id=$user_id");
    if($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE teaching_profile SET main_subject=?, teaching_band=?, demo_video_link=? WHERE user_id=?");
        $stmt->bind_param("sssi", $main_sub, $band, $demo, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO teaching_profile (main_subject, teaching_band, demo_video_link, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $main_sub, $band, $demo, $user_id);
    }
    $stmt->execute();
    $msg = "<div class='alert alert-success'>Đã lưu hồ sơ chuyên môn!</div>";
}

// Tab 3: Giấy tờ pháp lý
if (isset($_POST['add_doc'])) {
    $type = $_POST['doc_type'];
    $num = $_POST['doc_number'];
    $issue = $_POST['issue_date'] ?? ''; 
    $place = $_POST['place_of_issue'] ?? '';
    $expiry = $_POST['expiry_date'] ?? '';
    
    $front = ''; $back = '';
    if (isset($_FILES['file_front']) && $_FILES['file_front']['error'] == 0) {
        $front = "img/docs/" . time() . "_front_" . basename($_FILES['file_front']['name']);
        move_uploaded_file($_FILES['file_front']['tmp_name'], $front);
    }
    if (isset($_FILES['file_back']) && $_FILES['file_back']['error'] == 0) {
        $back = "img/docs/" . time() . "_back_" . basename($_FILES['file_back']['name']);
        move_uploaded_file($_FILES['file_back']['tmp_name'], $back);
    }

    $stmt = $conn->prepare("INSERT INTO legal_documents (user_id, doc_type, doc_number, issue_date, place_of_issue, expiry_date, doc_file_front, doc_file_back) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $type, $num, $issue, $place, $expiry, $front, $back);
    
    if($stmt->execute()) $msg = "<div class='alert alert-success'>Đã thêm giấy tờ!</div>";
    else $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
}

// Tab 4: Hợp đồng
if (isset($_POST['add_contract'])) {
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    
    $check_contract = $conn->query("SELECT id FROM labor_contracts WHERE user_id = $user_id AND end_date >= '$start'");
    
    if ($check_contract->num_rows > 0) {
        $msg = "<div class='alert alert-danger fw-bold'><i class='fas fa-exclamation-circle'></i> Lỗi: Nhân viên này vẫn còn hợp đồng cũ chưa hết hạn!</div>";
    } else {
        $num = $_POST['contract_number'];
        $type = $_POST['contract_type'];
        $base = $_POST['base_salary'];
        $hourly = $_POST['hourly_rate'];
        $bank = $_POST['bank_name'] ?? '';
        $acc = $_POST['bank_number'] ?? '';
        $tax = $_POST['tax_code'] ?? '';
        
        $file = '';
        if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] == 0) {
            $file = "img/docs/" . time() . "_contract_" . basename($_FILES['contract_file']['name']);
            move_uploaded_file($_FILES['contract_file']['tmp_name'], $file);
        }

        $stmt = $conn->prepare("INSERT INTO labor_contracts (user_id, contract_number, contract_type, start_date, end_date, base_salary, hourly_rate, bank_name, bank_number, tax_code, contract_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssss", $user_id, $num, $type, $start, $end, $base, $hourly, $bank, $acc, $tax, $file);
        
        if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã thêm hợp đồng mới!</div>";
        else $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// Tab 5: Bảo hiểm
if (isset($_POST['save_tab5'])) {
    $status = $_POST['social_status'] ?? '';
    $book = $_POST['social_book_number'] ?? '';
    $card = $_POST['health_card_number'] ?? '';
    $hosp = $_POST['hospital_reg'] ?? '';
    $sal = $_POST['social_salary_base'] ?? 0;
    $com_pkg = $_POST['commercial_pkg_name'] ?? '';
    $com_num = $_POST['commercial_contract_num'] ?? '';
    $com_exp = $_POST['commercial_expiry'] ?? '';

    $check = $conn->query("SELECT user_id FROM insurance WHERE user_id=$user_id");
    if($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE insurance SET social_status=?, social_book_number=?, health_card_number=?, hospital_reg=?, social_salary_base=?, commercial_pkg_name=?, commercial_contract_num=?, commercial_expiry=? WHERE user_id=?");
        $stmt->bind_param("ssssssssi", $status, $book, $card, $hosp, $sal, $com_pkg, $com_num, $com_exp, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO insurance (social_status, social_book_number, health_card_number, hospital_reg, social_salary_base, commercial_pkg_name, commercial_contract_num, commercial_expiry, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssi", $status, $book, $card, $hosp, $sal, $com_pkg, $com_num, $com_exp, $user_id);
    }
    if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã lưu thông tin bảo hiểm!</div>";
    else $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
}

// Tab 6: Người thân
if (isset($_POST['add_contact'])) {
    $name = $_POST['contact_name'];
    $rel = $_POST['relationship'];
    $phone = $_POST['contact_phone'];
    $addr = $_POST['contact_address'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, name, relationship, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $rel, $phone, $addr);
    
    if ($stmt->execute()) $msg = "<div class='alert alert-success'>Đã thêm người thân!</div>";
    else $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
}

// --- LẤY DỮ LIỆU HIỂN THỊ ---
$sql_u = "SELECT u.*, r.name as role_name, d.name as dept_name 
          FROM users u 
          LEFT JOIN roles r ON u.role_id = r.id 
          LEFT JOIN departments d ON u.department_id = d.id
          WHERE u.id = $user_id";
$user = $conn->query($sql_u)->fetch_assoc();

if (!$user) die("Không tìm thấy nhân viên!");

$detail = $conn->query("SELECT * FROM employee_details WHERE user_id=$user_id")->fetch_assoc();
$teaching = $conn->query("SELECT * FROM teaching_profile WHERE user_id=$user_id")->fetch_assoc();
$legal_docs = $conn->query("SELECT * FROM legal_documents WHERE user_id=$user_id");
$contracts = $conn->query("SELECT * FROM labor_contracts WHERE user_id=$user_id ORDER BY start_date DESC");
$insurance = $conn->query("SELECT * FROM insurance WHERE user_id=$user_id")->fetch_assoc();
$contacts = $conn->query("SELECT * FROM emergency_contacts WHERE user_id=$user_id");
$roles_list = $conn->query("SELECT * FROM roles");

// Helper lấy giá trị an toàn
$curr_cert = $detail['certificate_type'] ?? 'None';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ: <?php echo $user['full_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .section-title { border-left: 4px solid #0d6efd; padding-left: 10px; font-weight: bold; color: #0d6efd; margin-bottom: 15px; margin-top: 10px; }
        .bg-account { background-color: #fff3cd; border: 1px solid #ffecb5; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-user-tie text-primary"></i> Hồ sơ: <?php echo $user['full_name']; ?></h3>
            <a href="admin_employees.php" class="btn btn-secondary">Quay lại</a>
        </div>

        <?php echo $msg; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-body text-center">
                        <img src="<?php echo $user['avatar'] ?? 'img/default.jpg'; ?>" class="rounded-circle mx-auto d-block mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="mb-0"><?php echo $user['full_name']; ?></h5>
                        <small class="text-muted"><?php echo $user['email']; ?></small>
                        <div class="mt-2">
                            <span class="badge bg-primary"><?php echo $user['role_name']; ?></span>
                            <span class="badge bg-info text-dark"><?php echo $user['dept_name'] ?? 'Chưa phân phòng'; ?></span>
                            <span class="badge <?php echo ($user['status']=='active')?'bg-success':'bg-danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="fas fa-user-cog"></i> Cài đặt Tài khoản & Vai trò
                    </div>
                    <div class="card-body bg-account">
                        <form method="POST">
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Họ và tên (Hiển thị)</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Email (Đăng nhập)</label>
                                <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold small">Vai trò (Phân quyền)</label>
                                    <select name="role_id" class="form-select border-primary">
                                        <?php 
                                        $roles_list->data_seek(0); 
                                        while($r = $roles_list->fetch_assoc()): 
                                            $selected = ($user['role_id'] == $r['id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $r['id']; ?>" <?php echo $selected; ?>>
                                                <?php echo $r['name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold small">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>Mở</option>
                                        <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Khóa</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="update_account" class="btn btn-warning w-100 fw-bold">
                                <i class="fas fa-sync-alt"></i> Cập nhật Tài khoản
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab1">1. Nhân viên</button></li>
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
                                    <h6 class="text-primary border-bottom pb-2">Thông tin cá nhân & Liên hệ</h6>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4"><label>Ngày sinh</label><input type="date" name="dob" class="form-control" value="<?php echo $detail['dob'] ?? ''; ?>"></div>
                                        <div class="col-md-4"><label>Giới tính</label><select name="gender" class="form-select"><option value="Nam" <?php echo ($detail['gender']??'')=='Nam'?'selected':''; ?>>Nam</option><option value="Nữ" <?php echo ($detail['gender']??'')=='Nữ'?'selected':''; ?>>Nữ</option></select></div>
                                        <div class="col-md-4"><label>Quốc tịch</label><input type="text" name="nationality" class="form-control" value="<?php echo $detail['nationality'] ?? 'Việt Nam'; ?>"></div>
                                        <div class="col-md-4"><label>Hôn nhân</label><select name="marital_status" class="form-select"><option value="Độc thân">Độc thân</option><option value="Đã kết hôn">Đã kết hôn</option></select></div>
                                        <div class="col-md-4"><label>SĐT chính</label><input type="text" name="phone" class="form-control" value="<?php echo $detail['phone'] ?? ''; ?>"></div>
                                        <div class="col-md-4"><label>Zalo</label><input type="text" name="zalo" class="form-control" value="<?php echo $detail['zalo'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Địa chỉ hiện tại</label><input type="text" name="current_address" class="form-control" value="<?php echo $detail['current_address'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Quê quán</label><input type="text" name="hometown" class="form-control" value="<?php echo $detail['hometown'] ?? ''; ?>"></div>
                                    </div>

                                    <h6 class="text-primary border-bottom pb-2 mt-4">Lý lịch & Đạo đức</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4"><label>Trạng thái</label>
                                            <select name="criminal_record_status" class="form-select">
                                                <option value="Đang xác minh" <?php echo ($detail['criminal_record_status']??'')=='Đang xác minh'?'selected':''; ?>>Đang xác minh</option>
                                                <option value="Trong sạch" <?php echo ($detail['criminal_record_status']??'')=='Trong sạch'?'selected':''; ?>>Trong sạch</option>
                                                <option value="Có tiền án" <?php echo ($detail['criminal_record_status']??'')=='Có tiền án'?'selected':''; ?>>Có tiền án</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4"><label>Số phiếu LLTP</label><input type="text" name="criminal_record_number" class="form-control" value="<?php echo $detail['criminal_record_number'] ?? ''; ?>"></div>
                                        <div class="col-md-4"><label>Ngày cấp</label><input type="date" name="criminal_record_date" class="form-control" value="<?php echo $detail['criminal_record_date'] ?? ''; ?>"></div>
                                        <div class="col-12">
                                            <label>File Scan Lý lịch</label>
                                            <input type="file" name="crm_file_up" class="form-control">
                                            <input type="hidden" name="current_crm_file" value="<?php echo $detail['criminal_record_file'] ?? ''; ?>">
                                            <?php if(!empty($detail['criminal_record_file'])): ?><a href="<?php echo $detail['criminal_record_file']; ?>" target="_blank">Xem file hiện tại</a><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-end"><button type="submit" name="save_tab1" class="btn btn-primary">Lưu Thông Tin</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab2">
                                <form method="POST">
                                    <h6 class="text-primary border-bottom pb-2">Năng lực giảng dạy</h6>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6"><label>Môn dạy chính</label><input type="text" name="main_subject" class="form-control" value="<?php echo $teaching['main_subject'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Band điểm dạy</label><input type="text" name="teaching_band" class="form-control" value="<?php echo $teaching['teaching_band'] ?? ''; ?>"></div>
                                        <div class="col-12"><label>Link Video Demo</label><input type="text" name="demo_video_link" class="form-control" value="<?php echo $teaching['demo_video_link'] ?? ''; ?>"></div>
                                    </div>
                                    
                                    <h6 class="text-primary border-bottom pb-2">Bằng cấp & Chứng chỉ</h6>
                                    <div class="row g-3 bg-light p-3 rounded border mx-0">
                                        <div class="col-md-3"><label class="fw-bold">Trình độ</label><select name="education_level" class="form-select"><option value="Đại học">Đại học</option><option value="Thạc sĩ">Thạc sĩ</option></select></div>
                                        <div class="col-md-3"><label>Chuyên ngành</label><input type="text" name="major" class="form-control" value="<?php echo $detail['major'] ?? ''; ?>"></div>
                                        
                                        <div class="col-md-3">
                                            <label class="fw-bold">Chứng chỉ</label>
                                            <select name="certificate_type" class="form-select">
                                                <option value="None" <?php echo ($curr_cert == 'None') ? 'selected' : ''; ?>>Không</option>
                                                <option value="IELTS" <?php echo ($curr_cert == 'IELTS') ? 'selected' : ''; ?>>IELTS</option>
                                                <option value="TOEIC" <?php echo ($curr_cert == 'TOEIC') ? 'selected' : ''; ?>>TOEIC</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3"><label>Điểm số</label><input type="number" step="0.5" name="certificate_score" class="form-control" value="<?php echo $detail['certificate_score'] ?? ''; ?>"></div>
                                    </div>
                                    <div class="mt-3 text-end"><button type="submit" name="save_tab2" class="btn btn-primary">Lưu Chuyên Môn</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab3">
                                <h6 class="text-primary">Danh sách giấy tờ</h6>
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light"><tr><th>Loại</th><th>Số</th><th>Hết hạn</th><th>File</th></tr></thead>
                                    <tbody>
                                        <?php while($doc = $legal_docs->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $doc['doc_type']; ?></td>
                                            <td><?php echo $doc['doc_number']; ?></td>
                                            <td class="<?php echo ($doc['expiry_date'] < date('Y-m-d')) ? 'text-danger' : ''; ?>"><?php echo $doc['expiry_date']; ?></td>
                                            <td><?php if($doc['doc_file_front']): ?><a href="<?php echo $doc['doc_file_front']; ?>" target="_blank">Xem</a><?php endif; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <hr>
                                <h6>Thêm mới</h6>
                                <form method="POST" enctype="multipart/form-data" class="bg-light p-3 border rounded">
                                    <div class="row g-2">
                                        <div class="col-md-3"><label class="small fw-bold">Loại</label><select name="doc_type" class="form-select"><option>CCCD</option><option>Visa</option></select></div>
                                        <div class="col-md-3"><label class="small fw-bold">Số giấy tờ</label><input type="text" name="doc_number" class="form-control" required></div>
                                        <div class="col-md-3"><label class="small fw-bold">Ngày cấp</label><input type="date" name="issue_date" class="form-control"></div>
                                        <div class="col-md-3"><label class="small fw-bold">Hết hạn</label><input type="date" name="expiry_date" class="form-control"></div>
                                        
                                        <div class="col-md-6"><label class="small fw-bold">Nơi cấp</label><input type="text" name="place_of_issue" class="form-control"></div>
                                        <div class="col-md-3"><label class="small fw-bold">Mặt trước</label><input type="file" name="file_front" class="form-control"></div>
                                        <div class="col-md-3"><label class="small fw-bold">Mặt sau</label><input type="file" name="file_back" class="form-control"></div>
                                        
                                        <div class="col-12 text-end mt-2"><button type="submit" name="add_doc" class="btn btn-success btn-sm">Thêm Giấy Tờ</button></div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab4">
                                <h6 class="text-primary">Danh sách Hợp đồng</h6>
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light"><tr><th>Số HĐ</th><th>Loại</th><th>Thời hạn</th><th>Lương cứng</th><th>Ngân hàng</th><th>File</th></tr></thead>
                                    <tbody>
                                        <?php while($ct = $contracts->fetch_assoc()): 
                                            $is_active = ($ct['end_date'] >= date('Y-m-d'));
                                            $row_class = $is_active ? 'table-success' : 'text-muted';
                                        ?>
                                        <tr class="<?php echo $row_class; ?>">
                                            <td><?php echo $ct['contract_number']; ?> <?php if($is_active) echo '<i class="fas fa-check-circle text-success small"></i>'; ?></td>
                                            <td><?php echo $ct['contract_type']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($ct['start_date'])) . ' -> ' . date('d/m/Y', strtotime($ct['end_date'])); ?></td>
                                            <td class="fw-bold"><?php echo number_format($ct['base_salary']); ?></td>
                                            <td><?php echo $ct['bank_name'] . ' - ' . $ct['bank_number']; ?></td>
                                            <td><?php if($ct['contract_file']): ?><a href="<?php echo $ct['contract_file']; ?>" target="_blank">PDF</a><?php endif; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <hr>
                                <h6>Thêm Hợp đồng mới</h6>
                                <form method="POST" enctype="multipart/form-data" class="row g-2 bg-light p-2 border rounded">
                                    <div class="col-md-3"><input type="text" name="contract_number" class="form-control" placeholder="Số HĐ" required></div>
                                    <div class="col-md-3"><select name="contract_type" class="form-select"><option>Thử việc</option><option>Chính thức</option></select></div>
                                    <div class="col-md-3"><input type="date" name="start_date" class="form-control" title="Bắt đầu" required></div>
                                    <div class="col-md-3"><input type="date" name="end_date" class="form-control" title="Kết thúc" required></div>
                                    
                                    <div class="col-md-3"><input type="number" name="base_salary" class="form-control" placeholder="Lương cứng" required></div>
                                    <div class="col-md-3"><input type="number" name="hourly_rate" class="form-control" placeholder="Lương giờ"></div>
                                    
                                    <div class="col-md-6"><input type="text" name="bank_name" class="form-control" placeholder="Tên Ngân hàng"></div>
                                    <div class="col-md-6"><input type="text" name="bank_number" class="form-control" placeholder="Số tài khoản"></div>
                                    <div class="col-md-6"><input type="text" name="tax_code" class="form-control" placeholder="Mã số thuế"></div>

                                    <div class="col-md-12"><input type="file" name="contract_file" class="form-control"></div>
                                    <div class="col-12 text-end"><button type="submit" name="add_contract" class="btn btn-success btn-sm">Lưu Hợp Đồng</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab5">
                                <form method="POST">
                                    <h6 class="text-primary">Bảo hiểm Xã hội</h6>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4"><label>Trạng thái</label><select name="social_status" class="form-select"><option value="Không đóng" <?php echo ($insurance['social_status']??'')=='Không đóng'?'selected':''; ?>>Không đóng</option><option value="Có đóng" <?php echo ($insurance['social_status']??'')=='Có đóng'?'selected':''; ?>>Có đóng</option></select></div>
                                        <div class="col-md-4"><label>Số sổ BHXH</label><input type="text" name="social_book_number" class="form-control" value="<?php echo $insurance['social_book_number'] ?? ''; ?>"></div>
                                        <div class="col-md-4"><label>Mã thẻ BHYT</label><input type="text" name="health_card_number" class="form-control" value="<?php echo $insurance['health_card_number'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Nơi ĐK KCB</label><input type="text" name="hospital_reg" class="form-control" value="<?php echo $insurance['hospital_reg'] ?? ''; ?>"></div>
                                        <div class="col-md-6"><label>Mức lương đóng BH</label><input type="number" name="social_salary_base" class="form-control" value="<?php echo $insurance['social_salary_base'] ?? 0; ?>"></div>
                                    </div>
                                    <h6 class="text-primary">Bảo hiểm Thương mại</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4"><label>Tên gói</label><input type="text" name="commercial_pkg_name" class="form-control" value="<?php echo $insurance['commercial_pkg_name'] ?? ''; ?>"></div>
                                        <div class="col-md-4"><label>Số HĐ Bảo hiểm</label><input type="text" name="commercial_contract_num" class="form-control" value="<?php echo $insurance['commercial_contract_num'] ?? ''; ?>"></div>
                                        <div class="col-md-4"><label>Ngày hết hạn</label><input type="date" name="commercial_expiry" class="form-control" value="<?php echo $insurance['commercial_expiry'] ?? ''; ?>"></div>
                                    </div>
                                    <div class="mt-3 text-end"><button type="submit" name="save_tab5" class="btn btn-primary">Lưu Bảo Hiểm</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab6">
                                <table class="table table-striped table-sm">
                                    <thead><tr><th>Họ tên</th><th>Quan hệ</th><th>SĐT</th><th>Địa chỉ</th></tr></thead>
                                    <tbody>
                                        <?php while($ct = $contacts->fetch_assoc()): ?>
                                        <tr><td><?php echo $ct['name']; ?></td><td><?php echo $ct['relationship']; ?></td><td><?php echo $ct['phone']; ?></td><td><?php echo $ct['address']; ?></td></tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <hr>
                                <form method="POST" class="row g-2">
                                    <div class="col-md-4"><input type="text" name="contact_name" class="form-control" placeholder="Họ tên" required></div>
                                    <div class="col-md-2"><input type="text" name="relationship" class="form-control" placeholder="Quan hệ" required></div>
                                    <div class="col-md-3"><input type="text" name="contact_phone" class="form-control" placeholder="SĐT" required></div>
                                    <div class="col-md-3"><input type="text" name="contact_address" class="form-control" placeholder="Địa chỉ"></div>
                                    <div class="col-12 mt-2 text-end"><button type="submit" name="add_contact" class="btn btn-success">Thêm Liên Hệ</button></div>
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