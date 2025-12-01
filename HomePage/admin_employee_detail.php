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

// --- XỬ LÝ LƯU DỮ LIỆU ---

// 1. Lưu NHÂN VIÊN (Gốc + Lý lịch)
if (isset($_POST['save_tab1'])) {
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $nation = $_POST['nationality'];
    $marital = $_POST['marital_status'];
    $phone = $_POST['phone'];
    $zalo = $_POST['zalo'];
    $addr = $_POST['current_address'];
    $home = $_POST['hometown'];
    $crm_status = $_POST['criminal_record_status'];
    $crm_num = $_POST['criminal_record_number'];
    $crm_date = $_POST['criminal_record_date'];

    // Upload file Lý lịch tư pháp
    $crm_file = $_POST['current_crm_file'] ?? '';
    if (isset($_FILES['crm_file_up']) && $_FILES['crm_file_up']['error'] == 0) {
        $target = "img/docs/" . time() . "_crm_" . basename($_FILES['crm_file_up']['name']);
        if (!is_dir('img/docs')) mkdir('img/docs', 0777, true);
        if (move_uploaded_file($_FILES['crm_file_up']['tmp_name'], $target)) $crm_file = $target;
    }

    // Upsert vào employee_details
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

// 2. Lưu CHUYÊN MÔN
if (isset($_POST['save_tab2'])) {
    $main_sub = $_POST['main_subject'];
    $band = $_POST['teaching_band'];
    $demo = $_POST['demo_video_link'];

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

// 3. Thêm GIẤY TỜ PHÁP LÝ
if (isset($_POST['add_doc'])) {
    $type = $_POST['doc_type'];
    $num = $_POST['doc_number'];
    $issue = $_POST['issue_date'];
    $place = $_POST['place_of_issue'];
    $expiry = $_POST['expiry_date'];
    
    $front = ''; $back = '';
    // Upload ảnh
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
    $stmt->execute();
    $msg = "<div class='alert alert-success'>Đã thêm giấy tờ!</div>";
}

// 4. Thêm HỢP ĐỒNG
if (isset($_POST['add_contract'])) {
    $num = $_POST['contract_number'];
    $type = $_POST['contract_type'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $base = $_POST['base_salary'];
    $hourly = $_POST['hourly_rate'];
    $bank = $_POST['bank_name'];
    $acc = $_POST['bank_number'];
    $tax = $_POST['tax_code'];
    
    $file = '';
    if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] == 0) {
        $file = "img/docs/" . time() . "_contract_" . basename($_FILES['contract_file']['name']);
        move_uploaded_file($_FILES['contract_file']['tmp_name'], $file);
    }

    $stmt = $conn->prepare("INSERT INTO labor_contracts (user_id, contract_number, contract_type, start_date, end_date, base_salary, hourly_rate, bank_name, bank_number, tax_code, contract_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssss", $user_id, $num, $type, $start, $end, $base, $hourly, $bank, $acc, $tax, $file);
    $stmt->execute();
    $msg = "<div class='alert alert-success'>Đã thêm hợp đồng!</div>";
}

// 5. Lưu BẢO HIỂM
if (isset($_POST['save_tab5'])) {
    $status = $_POST['social_status'];
    $book = $_POST['social_book_number'];
    $card = $_POST['health_card_number'];
    $hosp = $_POST['hospital_reg'];
    $sal = $_POST['social_salary_base'];
    $com_pkg = $_POST['commercial_pkg_name'];
    $com_num = $_POST['commercial_contract_num'];
    $com_exp = $_POST['commercial_expiry'];

    $check = $conn->query("SELECT user_id FROM insurance WHERE user_id=$user_id");
    if($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE insurance SET social_status=?, social_book_number=?, health_card_number=?, hospital_reg=?, social_salary_base=?, commercial_pkg_name=?, commercial_contract_num=?, commercial_expiry=? WHERE user_id=?");
        $stmt->bind_param("ssssssssi", $status, $book, $card, $hosp, $sal, $com_pkg, $com_num, $com_exp, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO insurance (social_status, social_book_number, health_card_number, hospital_reg, social_salary_base, commercial_pkg_name, commercial_contract_num, commercial_expiry, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssi", $status, $book, $card, $hosp, $sal, $com_pkg, $com_num, $com_exp, $user_id);
    }
    $stmt->execute();
    $msg = "<div class='alert alert-success'>Đã lưu thông tin bảo hiểm!</div>";
}

// 6. Thêm NGƯỜI THÂN
if (isset($_POST['add_contact'])) {
    $name = $_POST['contact_name'];
    $rel = $_POST['relationship'];
    $phone = $_POST['contact_phone'];
    $addr = $_POST['contact_address'];
    
    $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, name, relationship, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $rel, $phone, $addr);
    $stmt->execute();
    $msg = "<div class='alert alert-success'>Đã thêm người thân!</div>";
}


// --- LẤY DỮ LIỆU HIỂN THỊ ---
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
if (!$user) die("Not Found");

$detail = $conn->query("SELECT * FROM employee_details WHERE user_id=$user_id")->fetch_assoc();
$teaching = $conn->query("SELECT * FROM teaching_profile WHERE user_id=$user_id")->fetch_assoc();
$legal_docs = $conn->query("SELECT * FROM legal_documents WHERE user_id=$user_id");
$contracts = $conn->query("SELECT * FROM labor_contracts WHERE user_id=$user_id");
$insurance = $conn->query("SELECT * FROM insurance WHERE user_id=$user_id")->fetch_assoc();
$contacts = $conn->query("SELECT * FROM emergency_contacts WHERE user_id=$user_id");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ: <?php echo $user['full_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-user-tie text-primary"></i> Hồ sơ: <?php echo $user['full_name']; ?></h3>
            <a href="admin_employees.php" class="btn btn-secondary">Quay lại</a>
        </div>

        <?php echo $msg; ?>

        <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab1"><i class="fas fa-id-card"></i> 1. Nhân viên</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab2"><i class="fas fa-chalkboard-teacher"></i> 2. Chuyên môn</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab3"><i class="fas fa-passport"></i> 3. Pháp lý</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab4"><i class="fas fa-file-contract"></i> 4. Hợp đồng</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab5"><i class="fas fa-heartbeat"></i> 5. Bảo hiểm</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab6"><i class="fas fa-users"></i> 6. Người thân</button></li>
        </ul>

        <div class="tab-content bg-white p-4 shadow-sm rounded">
            
            <div class="tab-pane fade show active" id="tab1">
                <form method="POST" enctype="multipart/form-data">
                    <h5 class="text-primary border-bottom pb-2 mb-3">Thông tin Định danh & Liên hệ</h5>
                    <div class="row g-3">
                        <div class="col-md-3"><label>Mã NV</label><input type="text" class="form-control" value="<?php echo $user['id']; ?>" disabled></div>
                        <div class="col-md-3"><label>Họ tên</label><input type="text" class="form-control" value="<?php echo $user['full_name']; ?>" disabled></div>
                        <div class="col-md-3"><label>Ngày sinh</label><input type="date" name="dob" class="form-control" value="<?php echo $detail['dob'] ?? ''; ?>"></div>
                        <div class="col-md-3"><label>Giới tính</label>
                            <select name="gender" class="form-select">
                                <option value="Nam" <?php echo ($detail['gender']??'')=='Nam'?'selected':''; ?>>Nam</option>
                                <option value="Nữ" <?php echo ($detail['gender']??'')=='Nữ'?'selected':''; ?>>Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label>Quốc tịch</label><input type="text" name="nationality" class="form-control" value="<?php echo $detail['nationality'] ?? 'Việt Nam'; ?>"></div>
                        <div class="col-md-3"><label>Hôn nhân</label>
                            <select name="marital_status" class="form-select">
                                <option value="Độc thân" <?php echo ($detail['marital_status']??'')=='Độc thân'?'selected':''; ?>>Độc thân</option>
                                <option value="Đã kết hôn" <?php echo ($detail['marital_status']??'')=='Đã kết hôn'?'selected':''; ?>>Đã kết hôn</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label>SĐT chính</label><input type="text" name="phone" class="form-control" value="<?php echo $detail['phone'] ?? ''; ?>"></div>
                        <div class="col-md-3"><label>Zalo/WhatsApp</label><input type="text" name="zalo" class="form-control" value="<?php echo $detail['zalo'] ?? ''; ?>"></div>
                        <div class="col-md-6"><label>Địa chỉ hiện tại</label><input type="text" name="current_address" class="form-control" value="<?php echo $detail['current_address'] ?? ''; ?>"></div>
                        <div class="col-md-6"><label>Quê quán</label><input type="text" name="hometown" class="form-control" value="<?php echo $detail['hometown'] ?? ''; ?>"></div>
                    </div>

                    <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Lý lịch & Đạo đức (Background Check)</h5>
                    <div class="row g-3">
                        <div class="col-md-4"><label>Trạng thái</label>
                            <select name="criminal_record_status" class="form-select">
                                <option value="Đang xác minh" <?php echo ($detail['criminal_record_status']??'')=='Đang xác minh'?'selected':''; ?>>Đang xác minh</option>
                                <option value="Trong sạch" <?php echo ($detail['criminal_record_status']??'')=='Trong sạch'?'selected':''; ?>>Trong sạch (Đã có phiếu)</option>
                                <option value="Có tiền án" <?php echo ($detail['criminal_record_status']??'')=='Có tiền án'?'selected':''; ?>>Có tiền án/tiền sự</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label>Số phiếu Lý lịch tư pháp</label><input type="text" name="criminal_record_number" class="form-control" value="<?php echo $detail['criminal_record_number'] ?? ''; ?>"></div>
                        <div class="col-md-4"><label>Ngày cấp</label><input type="date" name="criminal_record_date" class="form-control" value="<?php echo $detail['criminal_record_date'] ?? ''; ?>"></div>
                        <div class="col-md-12">
                            <label>File Scan Lý lịch (Ảnh/PDF)</label>
                            <input type="file" name="crm_file_up" class="form-control">
                            <input type="hidden" name="current_crm_file" value="<?php echo $detail['criminal_record_file'] ?? ''; ?>">
                            <?php if(!empty($detail['criminal_record_file'])): ?>
                                <a href="<?php echo $detail['criminal_record_file']; ?>" target="_blank" class="mt-2 d-block">Xem file hiện tại</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-3 text-end"><button type="submit" name="save_tab1" class="btn btn-primary">Lưu Thông Tin</button></div>
                </form>
            </div>

            <div class="tab-pane fade" id="tab2">
                <form method="POST">
                    <h5 class="text-primary border-bottom pb-2 mb-3">Năng lực giảng dạy</h5>
                    <div class="row g-3">
                        <div class="col-md-6"><label>Môn dạy chính</label><input type="text" name="main_subject" class="form-control" placeholder="VD: IELTS, TOEIC, Giao tiếp" value="<?php echo $teaching['main_subject'] ?? ''; ?>"></div>
                        <div class="col-md-6"><label>Band điểm dạy được</label><input type="text" name="teaching_band" class="form-control" placeholder="VD: 6.5 - 7.5" value="<?php echo $teaching['teaching_band'] ?? ''; ?>"></div>
                        <div class="col-md-12"><label>Link Video Demo dạy thử</label><input type="text" name="demo_video_link" class="form-control" placeholder="URL Youtube/Drive..." value="<?php echo $teaching['demo_video_link'] ?? ''; ?>"></div>
                    </div>
                    <div class="mt-3 text-end"><button type="submit" name="save_tab2" class="btn btn-primary">Lưu Chuyên Môn</button></div>
                </form>
                </div>

            <div class="tab-pane fade" id="tab3">
                <h5 class="text-primary border-bottom pb-2 mb-3">Giấy tờ tùy thân & Pháp lý</h5>
                <table class="table table-bordered table-sm">
                    <thead class="table-light"><tr><th>Loại</th><th>Số</th><th>Ngày cấp</th><th>Hết hạn</th><th>File</th></tr></thead>
                    <tbody>
                        <?php while($doc = $legal_docs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $doc['doc_type']; ?></td>
                            <td><?php echo $doc['doc_number']; ?></td>
                            <td><?php echo $doc['issue_date']; ?></td>
                            <td class="<?php echo ($doc['expiry_date'] < date('Y-m-d')) ? 'text-danger' : ''; ?>"><?php echo $doc['expiry_date']; ?></td>
                            <td>
                                <?php if($doc['doc_file_front']): ?><a href="<?php echo $doc['doc_file_front']; ?>" target="_blank">Trước</a><?php endif; ?>
                                <?php if($doc['doc_file_back']): ?> | <a href="<?php echo $doc['doc_file_back']; ?>" target="_blank">Sau</a><?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <hr>
                <h6>Thêm giấy tờ mới</h6>
                <form method="POST" enctype="multipart/form-data" class="row g-3 bg-light p-3 border rounded">
                    <div class="col-md-3">
                        <select name="doc_type" class="form-select">
                            <option value="CCCD">CCCD</option><option value="Hộ chiếu">Hộ chiếu</option><option value="Visa">Visa</option><option value="Work Permit">Work Permit</option>
                        </select>
                    </div>
                    <div class="col-md-3"><input type="text" name="doc_number" class="form-control" placeholder="Số giấy tờ" required></div>
                    <div class="col-md-3"><input type="date" name="issue_date" class="form-control" placeholder="Ngày cấp"></div>
                    <div class="col-md-3"><input type="date" name="expiry_date" class="form-control" placeholder="Ngày hết hạn"></div>
                    <div class="col-md-6"><input type="text" name="place_of_issue" class="form-control" placeholder="Nơi cấp"></div>
                    <div class="col-md-3"><input type="file" name="file_front" class="form-control" title="Mặt trước"></div>
                    <div class="col-md-3"><input type="file" name="file_back" class="form-control" title="Mặt sau"></div>
                    <div class="col-12 text-end"><button type="submit" name="add_doc" class="btn btn-success">Thêm Giấy Tờ</button></div>
                </form>
            </div>

            <div class="tab-pane fade" id="tab4">
                <h5 class="text-primary border-bottom pb-2 mb-3">Danh sách Hợp đồng</h5>
                <table class="table table-bordered table-sm">
                    <thead class="table-light"><tr><th>Số HĐ</th><th>Loại</th><th>Hiệu lực</th><th>Lương cứng</th><th>Lương giờ</th><th>File</th></tr></thead>
                    <tbody>
                        <?php while($ct = $contracts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $ct['contract_number']; ?></td>
                            <td><?php echo $ct['contract_type']; ?></td>
                            <td><?php echo $ct['start_date'] . ' -> ' . $ct['end_date']; ?></td>
                            <td><?php echo number_format($ct['base_salary']); ?></td>
                            <td><?php echo number_format($ct['hourly_rate']); ?></td>
                            <td><?php if($ct['contract_file']): ?><a href="<?php echo $ct['contract_file']; ?>" target="_blank">PDF</a><?php endif; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <hr>
                <h6>Thêm Hợp đồng mới</h6>
                <form method="POST" enctype="multipart/form-data" class="row g-3 bg-light p-3 border rounded">
                    <div class="col-md-3"><input type="text" name="contract_number" class="form-control" placeholder="Số HĐ" required></div>
                    <div class="col-md-3">
                        <select name="contract_type" class="form-select"><option>Thử việc</option><option>Chính thức</option><option>Part-time</option></select>
                    </div>
                    <div class="col-md-3"><input type="date" name="start_date" class="form-control" title="Ngày bắt đầu"></div>
                    <div class="col-md-3"><input type="date" name="end_date" class="form-control" title="Ngày kết thúc"></div>
                    <div class="col-md-4"><input type="number" name="base_salary" class="form-control" placeholder="Lương cứng"></div>
                    <div class="col-md-4"><input type="number" name="hourly_rate" class="form-control" placeholder="Lương giờ (Hourly)"></div>
                    <div class="col-md-4"><input type="file" name="contract_file" class="form-control" accept=".pdf,.jpg,.png"></div>
                    
                    <div class="col-12 text-muted small mt-2 mb-1">Thông tin thanh toán:</div>
                    <div class="col-md-4"><input type="text" name="bank_name" class="form-control" placeholder="Tên Ngân hàng"></div>
                    <div class="col-md-4"><input type="text" name="bank_number" class="form-control" placeholder="Số tài khoản"></div>
                    <div class="col-md-4"><input type="text" name="tax_code" class="form-control" placeholder="Mã số thuế CN"></div>
                    
                    <div class="col-12 text-end mt-2"><button type="submit" name="add_contract" class="btn btn-success">Lưu Hợp Đồng</button></div>
                </form>
            </div>

            <div class="tab-pane fade" id="tab5">
                <form method="POST">
                    <h5 class="text-primary border-bottom pb-2 mb-3">A. Bảo hiểm Xã hội (Nhà nước)</h5>
                    <div class="row g-3">
                        <div class="col-md-4"><label>Trạng thái</label>
                            <select name="social_status" class="form-select">
                                <option value="Không đóng" <?php echo ($insurance['social_status']??'')=='Không đóng'?'selected':''; ?>>Không đóng</option>
                                <option value="Có đóng" <?php echo ($insurance['social_status']??'')=='Có đóng'?'selected':''; ?>>Có đóng</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label>Số sổ BHXH</label><input type="text" name="social_book_number" class="form-control" value="<?php echo $insurance['social_book_number'] ?? ''; ?>"></div>
                        <div class="col-md-4"><label>Mã thẻ BHYT</label><input type="text" name="health_card_number" class="form-control" value="<?php echo $insurance['health_card_number'] ?? ''; ?>"></div>
                        <div class="col-md-6"><label>Nơi ĐK khám chữa bệnh</label><input type="text" name="hospital_reg" class="form-control" value="<?php echo $insurance['hospital_reg'] ?? ''; ?>"></div>
                        <div class="col-md-6"><label>Mức lương đóng BH</label><input type="number" name="social_salary_base" class="form-control" value="<?php echo $insurance['social_salary_base'] ?? ''; ?>"></div>
                    </div>

                    <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">B. Bảo hiểm Thương mại / Tai nạn</h5>
                    <div class="row g-3">
                        <div class="col-md-4"><label>Tên gói (PVI/Bảo Việt...)</label><input type="text" name="commercial_pkg_name" class="form-control" value="<?php echo $insurance['commercial_pkg_name'] ?? ''; ?>"></div>
                        <div class="col-md-4"><label>Số HĐ Bảo hiểm</label><input type="text" name="commercial_contract_num" class="form-control" value="<?php echo $insurance['commercial_contract_num'] ?? ''; ?>"></div>
                        <div class="col-md-4"><label>Ngày hết hạn</label><input type="date" name="commercial_expiry" class="form-control" value="<?php echo $insurance['commercial_expiry'] ?? ''; ?>"></div>
                    </div>
                    <div class="mt-3 text-end"><button type="submit" name="save_tab5" class="btn btn-primary">Lưu Bảo Hiểm</button></div>
                </form>
            </div>

            <div class="tab-pane fade" id="tab6">
                <h5 class="text-primary border-bottom pb-2 mb-3">Liên hệ khẩn cấp</h5>
                <table class="table table-striped">
                    <thead><tr><th>Họ tên</th><th>Quan hệ</th><th>SĐT</th><th>Địa chỉ</th></tr></thead>
                    <tbody>
                        <?php while($ct = $contacts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $ct['name']; ?></td>
                            <td><?php echo $ct['relationship']; ?></td>
                            <td><?php echo $ct['phone']; ?></td>
                            <td><?php echo $ct['address']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <hr>
                <h6>Thêm người thân</h6>
                <form method="POST" class="row g-3 bg-light p-3 border rounded">
                    <div class="col-md-3"><input type="text" name="contact_name" class="form-control" placeholder="Họ tên" required></div>
                    <div class="col-md-2"><input type="text" name="relationship" class="form-control" placeholder="Quan hệ" required></div>
                    <div class="col-md-3"><input type="text" name="contact_phone" class="form-control" placeholder="SĐT Khẩn cấp" required></div>
                    <div class="col-md-4"><input type="text" name="contact_address" class="form-control" placeholder="Địa chỉ"></div>
                    <div class="col-12 text-end"><button type="submit" name="add_contact" class="btn btn-success">Thêm</button></div>
                </form>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>