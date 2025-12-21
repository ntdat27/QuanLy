<?php
session_start();
require_once 'db_connect.php';

// Check quyền: Phải là Admin HOẶC có quyền duyệt đơn
if (!isset($_SESSION['user_id']) || (!hasPermission('leave.approve') && !hasPermission('approve.manage') && $_SESSION['role_id'] != 1)) {
    header("Location: index.php");
    exit();
}

// --- XỬ LÝ 1: DUYỆT/TỪ CHỐI ĐƠN NGHỈ PHÉP ---
if (isset($_POST['action_leave'])) {
    $id = $_POST['req_id'];
    $status = ($_POST['action_leave'] == 'approve') ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

// --- XỬ LÝ 2: DUYỆT/TỪ CHỐI SỬA HỒ SƠ ---
if (isset($_POST['action_profile'])) {
    $req_id = $_POST['req_id'];
    $action = $_POST['action_profile'];
    
    // Lấy thông tin yêu cầu
    $req = $conn->query("SELECT * FROM profile_requests WHERE id = $req_id")->fetch_assoc();

    if ($req) {
        if ($action == 'reject') {
            $conn->query("UPDATE profile_requests SET status = 'rejected' WHERE id = $req_id");
        } elseif ($action == 'approve') {
            $uid = $req['user_id'];
            $data = json_decode($req['data_content'], true);
            $type = $req['type'];

            if ($type == 'personal') {
                // Cập nhật users và employee_details
                if(isset($data['full_name'])) {
                    $conn->query("UPDATE users SET full_name='{$data['full_name']}', email='{$data['email']}' WHERE id=$uid");
                    if(isset($data['avatar'])) $conn->query("UPDATE users SET avatar='{$data['avatar']}' WHERE id=$uid");
                }
                
                // [FIX] Thêm ?? '' để tránh lỗi nếu JSON thiếu trường dữ liệu
                $phone = $data['phone'] ?? '';
                $dob = $data['dob'] ?? '';
                $gender = $data['gender'] ?? '';
                $nationality = $data['nationality'] ?? '';
                $marital = $data['marital_status'] ?? '';
                $zalo = $data['zalo'] ?? '';
                $addr = $data['current_address'] ?? '';
                $home = $data['hometown'] ?? '';
                $bio = $data['biography'] ?? '';

                $sql = "UPDATE employee_details SET phone=?, dob=?, gender=?, nationality=?, marital_status=?, zalo=?, current_address=?, hometown=?, biography=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssi", $phone, $dob, $gender, $nationality, $marital, $zalo, $addr, $home, $bio, $uid);
                $stmt->execute();

            } elseif ($type == 'teaching') {
                // [FIX] Thêm ?? ''
                $edu = $data['education_level'] ?? '';
                $major = $data['major'] ?? '';
                $c_type = $data['certificate_type'] ?? '';
                $c_score = $data['certificate_score'] ?? 0;

                $sql = "UPDATE employee_details SET education_level=?, major=?, certificate_type=?, certificate_score=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdi", $edu, $major, $c_type, $c_score, $uid);
                $stmt->execute();
                
                if(isset($data['edu_proof'])) $conn->query("UPDATE employee_details SET edu_proof='{$data['edu_proof']}' WHERE user_id=$uid");
                if(isset($data['cert_proof'])) $conn->query("UPDATE employee_details SET cert_proof='{$data['cert_proof']}' WHERE user_id=$uid");

                // [FIX] Thêm ?? ''
                $sub = $data['main_subject'] ?? '';
                $band = $data['teaching_band'] ?? '';
                $demo = $data['demo_video_link'] ?? '';

                $check = $conn->query("SELECT user_id FROM teaching_profile WHERE user_id=$uid");
                if($check->num_rows > 0) {
                    $stmt = $conn->prepare("UPDATE teaching_profile SET main_subject=?, teaching_band=?, demo_video_link=? WHERE user_id=?");
                    $stmt->bind_param("sssi", $sub, $band, $demo, $uid);
                } else {
                    $stmt = $conn->prepare("INSERT INTO teaching_profile (main_subject, teaching_band, demo_video_link, user_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $sub, $band, $demo, $uid);
                }
                $stmt->execute();

            } elseif ($type == 'legal') {
                $stmt = $conn->prepare("INSERT INTO legal_documents (user_id, doc_type, doc_number, issue_date, expiry_date, place_of_issue, doc_file_front, doc_file_back) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $f = $data['doc_file_front'] ?? ''; 
                $b = $data['doc_file_back'] ?? '';
                $d_type = $data['doc_type'] ?? '';
                $d_num = $data['doc_number'] ?? '';
                $d_iss = $data['issue_date'] ?? '';
                $d_exp = $data['expiry_date'] ?? '';
                $d_place = $data['place_of_issue'] ?? '';
                
                $stmt->bind_param("isssssss", $uid, $d_type, $d_num, $d_iss, $d_exp, $d_place, $f, $b);
                $stmt->execute();

            } elseif ($type == 'insurance') {
                // [FIX] Thêm ?? ''
                $s_stat = $data['social_status'] ?? '';
                $s_book = $data['social_book_number'] ?? '';
                $h_card = $data['health_card_number'] ?? '';
                $h_reg = $data['hospital_reg'] ?? '';

                $check = $conn->query("SELECT user_id FROM insurance WHERE user_id=$uid");
                if($check->num_rows > 0) {
                    $stmt = $conn->prepare("UPDATE insurance SET social_status=?, social_book_number=?, health_card_number=?, hospital_reg=? WHERE user_id=?");
                    $stmt->bind_param("ssssi", $s_stat, $s_book, $h_card, $h_reg, $uid);
                } else {
                    $stmt = $conn->prepare("INSERT INTO insurance (social_status, social_book_number, health_card_number, hospital_reg, user_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $s_stat, $s_book, $h_card, $h_reg, $uid);
                }
                $stmt->execute();

            } elseif ($type == 'contact') {
                $c_name = $data['name'] ?? '';
                $c_rel = $data['relationship'] ?? '';
                $c_phone = $data['phone'] ?? '';
                $c_addr = $data['address'] ?? '';

                $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, name, relationship, phone, address) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $uid, $c_name, $c_rel, $c_phone, $c_addr);
                $stmt->execute();
            }

            $conn->query("UPDATE profile_requests SET status = 'approved' WHERE id = $req_id");
        }
    }
}

// --- LẤY DỮ LIỆU ---
$leaves = $conn->query("SELECT lr.*, u.full_name FROM leave_requests lr JOIN users u ON lr.user_id = u.id WHERE lr.status = 'pending' ORDER BY lr.created_at ASC");
$reqs = $conn->query("SELECT pr.*, u.full_name FROM profile_requests pr JOIN users u ON pr.user_id = u.id WHERE pr.status = 'pending' ORDER BY pr.request_date ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trung tâm Phê duyệt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-check-double text-success"></i> Trung tâm Phê duyệt</h2>
            <a href="<?php echo ($_SESSION['role_id'] == 1) ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="approvalTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" id="req-tab" data-bs-toggle="tab" data-bs-target="#reqs" type="button">
                            Yêu cầu Cập nhật <span class="badge bg-warning text-dark rounded-pill"><?php echo $reqs->num_rows; ?></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leaves" type="button">
                            Đơn Nghỉ phép <span class="badge bg-danger rounded-pill"><?php echo $leaves->num_rows; ?></span>
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body tab-content">
                
                <div class="tab-pane fade show active" id="reqs">
                    <?php if ($reqs->num_rows > 0): ?>
                        <?php while($r = $reqs->fetch_assoc()): 
                            $data = json_decode($r['data_content'], true);
                            $type_map = ['personal'=>'Cá nhân', 'teaching'=>'Chuyên môn', 'legal'=>'Pháp lý', 'insurance'=>'Bảo hiểm', 'contact'=>'Người thân'];
                            $type_name = $type_map[$r['type']] ?? $r['type'];
                        ?>
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary-subtle d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo $r['full_name']; ?></strong>
                                    <span class="badge bg-primary ms-2"><?php echo $type_name; ?></span>
                                </div>
                                <small class="text-muted"><?php echo date('H:i d/m/Y', strtotime($r['request_date'])); ?></small>
                            </div>
                            <div class="card-body">
                                <div class="bg-light p-2 rounded mb-3 small font-monospace border">
                                    <?php 
                                    if(is_array($data)) {
                                        foreach($data as $k => $v) {
                                            if(strpos($k, 'img')===false && strpos($k, 'proof')===false && strpos($k, 'file')===false) 
                                                echo "<strong>$k:</strong> $v<br>";
                                        }
                                        if(isset($data['avatar'])) echo "<span class='text-success'>[Có Ảnh đại diện mới]</span><br>";
                                        if(isset($data['edu_proof'])) echo "<span class='text-success'>[Có Ảnh bằng cấp]</span><br>";
                                        if(isset($data['doc_file_front'])) echo "<span class='text-success'>[Có Ảnh giấy tờ]</span><br>";
                                    } else {
                                        echo "Lỗi dữ liệu";
                                    }
                                    ?>
                                </div>
                                <div class="text-end">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="req_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" name="action_profile" value="reject" class="btn btn-sm btn-outline-danger me-2">Từ chối</button>
                                        <button type="submit" name="action_profile" value="approve" class="btn btn-sm btn-success fw-bold"><i class="fas fa-check"></i> Duyệt & Cập nhật</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">Không có yêu cầu cập nhật hồ sơ nào.</p>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="leaves">
                    <?php if ($leaves->num_rows > 0): ?>
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nhân viên</th>
                                    <th>Thời gian</th>
                                    <th>Lý do</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $leaves->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['full_name']; ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo date('d/m', strtotime($row['start_date'])); ?> 
                                            <i class="fas fa-arrow-right small"></i> 
                                            <?php echo date('d/m', strtotime($row['end_date'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['reason']; ?></td>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action_leave" value="approve" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Duyệt</button>
                                            <button type="submit" name="action_leave" value="reject" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Từ chối</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">Không có đơn nghỉ phép nào chờ duyệt.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>