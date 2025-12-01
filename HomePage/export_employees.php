<?php
session_start();
require_once 'db_connect.php';

// Check quyền
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    exit('Access Denied');
}

$search = $_GET['search'] ?? ''; // Nhận từ khóa tìm kiếm
$filename = "Danh_sach_Nhan_vien_" . date('Y-m-d') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo '<!DOCTYPE html>';
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta charset="utf-8"></head>';
echo '<body>';

echo '<h2 style="text-align:center">DANH SÁCH NHÂN SỰ</h2>';
if($search) echo '<p style="text-align:center">Từ khóa tìm kiếm: "'.htmlspecialchars($search).'"</p>';

echo '<table border="1" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
echo '<thead>
        <tr style="background-color: #f0f0f0; font-weight: bold; text-align: center; height: 40px;">
            <th>ID</th>
            <th>Họ và tên</th>
            <th>Tài khoản</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>SĐT</th>
            <th>Ngày sinh</th>
            <th>Địa chỉ</th>
            <th>Trình độ</th>
            <th>Chuyên ngành</th>
            <th>Chứng chỉ NN</th>
            <th>Điểm số</th>
            <th>Ngày vào làm</th>
            <th>Hợp đồng</th>
            <th>Trạng thái</th>
            <th style="width: 300px;">Tiểu sử / Lai lịch</th>
        </tr>
      </thead>';
echo '<tbody>';

// --- TRUY VẤN DỮ LIỆU CÓ LỌC ---
$sql = "SELECT u.id, u.username, u.full_name, u.email, u.status, r.name as role_name, 
               ed.phone, ed.dob, ed.address, ed.education_level, ed.major, 
               ed.certificate_type, ed.certificate_score, ed.start_date, ed.contract_type, ed.biography
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        LEFT JOIN employee_details ed ON u.id = ed.user_id 
        WHERE u.role_id != 1"; // Không xuất Super Admin

// Thêm điều kiện tìm kiếm
if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.username LIKE '%$search%')";
}

$sql .= " ORDER BY u.id DESC";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $status_text = ($row['status'] == 'active') ? 'Hoạt động' : 'Đã nghỉ';
    $cert_info = ($row['certificate_type'] != 'None') ? $row['certificate_type'] : '';
    
    echo '<tr>';
    echo '<td style="text-align: center;">' . $row['id'] . '</td>';
    echo '<td><strong>' . $row['full_name'] . '</strong></td>';
    echo '<td>' . $row['username'] . '</td>';
    echo '<td>' . $row['email'] . '</td>';
    echo '<td style="text-align: center;">' . $row['role_name'] . '</td>';
    
    echo '<td style="text-align: center;">' . ($row['phone'] ?? '-') . '</td>';
    echo '<td style="text-align: center;">' . ($row['dob'] ? date('d/m/Y', strtotime($row['dob'])) : '-') . '</td>';
    echo '<td>' . ($row['address'] ?? '-') . '</td>';<?php
session_start();
require_once 'db_connect.php';

// Check quyền
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    exit('Access Denied');
}

$search = $_GET['search'] ?? ''; 
$filename = "Danh_sach_Nhan_su_Full_" . date('Y-m-d') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo '<!DOCTYPE html>';
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta charset="utf-8"></head>';
echo '<body>';

echo '<h2 style="text-align:center">HỒ SƠ NHÂN SỰ CHI TIẾT</h2>';
if($search) echo '<p style="text-align:center">Từ khóa: "'.htmlspecialchars($search).'"</p>';

echo '<table border="1" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">';
echo '<thead>
        <tr style="background-color: #0d6efd; color: white; font-weight: bold; text-align: center; height: 40px;">
            <th>ID</th>
            <th>Họ và tên</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            
            <th>SĐT</th>
            <th>Ngày sinh</th>
            <th>Giới tính</th>
            <th>Quốc tịch</th>
            <th>Địa chỉ</th>
            
            <th>Trình độ</th>
            <th>Chuyên ngành</th>
            <th>Chứng chỉ NN</th>
            <th>Điểm số</th>
            <th>Môn dạy chính</th>
            
            <th>Loại HĐ</th>
            <th>Ngày vào làm</th>
            <th>Lương Cứng</th>
            <th>Số TK Ngân hàng</th>
            <th>Mã số thuế</th>
            
            <th>Số CCCD/Passport</th>
            <th>BHXH</th>
            <th>Sổ BHXH</th>
            
            <th>Liên hệ khẩn cấp</th>
            <th style="width: 300px;">Tiểu sử / Ghi chú</th>
        </tr>
      </thead>';
echo '<tbody>';

// --- TRUY VẤN DỮ LIỆU TỔNG HỢP ---
$sql = "SELECT u.id, u.username, u.full_name, u.email, u.status, r.name as role_name, 
               ed.*, 
               tp.main_subject,
               ins.social_status, ins.social_book_number,
               lc.contract_type as active_contract_type, lc.base_salary as contract_salary, lc.bank_number, lc.tax_code
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        LEFT JOIN employee_details ed ON u.id = ed.user_id 
        LEFT JOIN teaching_profile tp ON u.id = tp.user_id
        LEFT JOIN insurance ins ON u.id = ins.user_id
        LEFT JOIN labor_contracts lc ON u.id = lc.user_id AND lc.is_active = 1 -- Chỉ lấy HĐ đang hiệu lực
        WHERE u.role_id != 1"; 

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$sql .= " ORDER BY u.id DESC";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $uid = $row['id'];
    
    // Lấy thông tin Giấy tờ pháp lý (Lấy cái đầu tiên tìm thấy)
    $doc_sql = $conn->query("SELECT doc_number, doc_type FROM legal_documents WHERE user_id = $uid LIMIT 1");
    $doc = $doc_sql->fetch_assoc();
    $doc_info = $doc ? ($doc['doc_type'] . ': ' . $doc['doc_number']) : '-';

    // Lấy liên hệ khẩn cấp
    $ec_sql = $conn->query("SELECT name, phone, relationship FROM emergency_contacts WHERE user_id = $uid LIMIT 1");
    $ec = $ec_sql->fetch_assoc();
    $ec_info = $ec ? ($ec['name'] . ' (' . $ec['relationship'] . ') - ' . $ec['phone']) : '-';

    $status_text = ($row['status'] == 'active') ? 'Hoạt động' : 'Đã nghỉ';
    
    // Xử lý dữ liệu hiển thị
    $dob = $row['dob'] ? date('d/m/Y', strtotime($row['dob'])) : '-';
    $start_date = $row['start_date'] ? date('d/m/Y', strtotime($row['start_date'])) : '-';
    $salary = $row['contract_salary'] ? number_format($row['contract_salary']) : '-';
    
    echo '<tr>';
    // Cơ bản
    echo '<td style="text-align: center;">' . $row['id'] . '</td>';
    echo '<td><strong>' . $row['full_name'] . '</strong></td>';
    echo '<td>' . $row['email'] . '</td>';
    echo '<td style="text-align: center;">' . $row['role_name'] . '</td>';
    echo '<td style="text-align: center;">' . $status_text . '</td>';
    
    // Cá nhân
    echo '<td style="text-align: center;">' . ($row['phone'] ?? '-') . '</td>';
    echo '<td style="text-align: center;">' . $dob . '</td>';
    echo '<td style="text-align: center;">' . ($row['gender'] ?? '-') . '</td>';
    echo '<td style="text-align: center;">' . ($row['nationality'] ?? '-') . '</td>';
    echo '<td>' . ($row['current_address'] ?? '-') . '</td>';
    
    // Chuyên môn
    echo '<td style="text-align: center;">' . ($row['education_level'] ?? '-') . '</td>';
    echo '<td>' . ($row['major'] ?? '-') . '</td>';
    echo '<td style="text-align: center;">' . ($row['certificate_type'] != 'None' ? $row['certificate_type'] : '-') . '</td>';
    echo '<td style="text-align: center;">' . ($row['certificate_score'] > 0 ? $row['certificate_score'] : '') . '</td>';
    echo '<td>' . ($row['main_subject'] ?? '-') . '</td>';
    
    // Hợp đồng
    echo '<td style="text-align: center;">' . ($row['active_contract_type'] ?? '-') . '</td>';
    echo '<td style="text-align: center;">' . $start_date . '</td>';
    echo '<td style="text-align: right;">' . $salary . '</td>';
    echo '<td style="text-align: center;">' . ($row['bank_number'] ?? '-') . '</td>';
    echo '<td style="text-align: center;">' . ($row['tax_code'] ?? '-') . '</td>';
    
    // Pháp lý & BH
    echo '<td style="text-align: center;">' . $doc_info . '</td>';
    echo '<td style="text-align: center;">' . ($row['social_status'] ?? '-') . '</td>';
    echo '<td style="text-align: center;">' . ($row['social_book_number'] ?? '-') . '</td>';
    
    // Khác
    echo '<td>' . $ec_info . '</td>';
    echo '<td style="white-space: pre-wrap;">' . ($row['biography'] ?? '') . '</td>';
    
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body></html>';
exit();
?>
    echo '<td style="text-align: center;">' . ($row['education_level'] ?? '-') . '</td>';
    echo '<td>' . ($row['major'] ?? '-') . '</td>';
    
    echo '<td style="text-align: center;">' . $cert_info . '</td>';
    echo '<td style="text-align: center;">' . ($row['certificate_score'] > 0 ? $row['certificate_score'] : '') . '</td>';
    
    echo '<td style="text-align: center;">' . ($row['start_date'] ? date('d/m/Y', strtotime($row['start_date'])) : '-') . '</td>';
    echo '<td style="text-align: center;">' . ($row['contract_type'] ?? '-') . '</td>';
    
    echo '<td style="text-align: center;">' . $status_text . '</td>';
    echo '<td style="white-space: pre-wrap;">' . $row['biography'] . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body></html>';
exit();
?>