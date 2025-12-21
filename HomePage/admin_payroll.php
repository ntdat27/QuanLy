<?php
session_start();
require_once 'db_connect.php';

// Check quyền: Phải là Admin HOẶC có quyền quản lý lương
if (!isset($_SESSION['user_id']) || (!hasPermission('salary.manage') && $_SESSION['role_id'] != 1)) {
    header("Location: index.php");
    exit();
}

$message = "";
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- 1. LẤY CẤU HÌNH TỪ DATABASE ---
$ALLOWANCE_DEGREE_BACHELOR = getSetting('allowance_bachelor');
$ALLOWANCE_DEGREE_MASTER   = getSetting('allowance_master');
$ALLOWANCE_DEGREE_PHD      = getSetting('allowance_phd');
$ALLOWANCE_SENIORITY_1Y    = getSetting('allowance_sen_1y');
$ALLOWANCE_SENIORITY_3Y    = getSetting('allowance_sen_3y');
$ALLOWANCE_SENIORITY_5Y    = getSetting('allowance_sen_5y');
$ALLOWANCE_IELTS_6         = getSetting('allowance_ielts_6');
$ALLOWANCE_IELTS_7         = getSetting('allowance_ielts_7');
$ALLOWANCE_IELTS_8         = getSetting('allowance_ielts_8');
$STANDARD_WORK_DAYS        = getSetting('standard_work_days') ?: 26;

// --- XỬ LÝ LƯU LƯƠNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $month = $_POST['month'];
    
    // Lấy dữ liệu an toàn
    $base = $_POST['base_salary'] ?? 0;
    $al_degree = $_POST['allowance_degree'] ?? 0;
    $al_seniority = $_POST['allowance_seniority'] ?? 0;
    $al_lang = $_POST['allowance_language'] ?? 0;
    $work_days = $_POST['work_days'] ?? 0;
    $ot_hours = $_POST['overtime_hours'] ?? 0;
    $bonus = $_POST['bonus'] ?? 0;
    $tax_percent = $_POST['tax_percent'] ?? 0;
    $late_count = $_POST['late_count'] ?? 0;
    $total_fine = $_POST['total_fine'] ?? 0;
    $note = $_POST['note'] ?? '';
    
    // Tính toán
    $actual_salary = ($base / $STANDARD_WORK_DAYS) * $work_days;
    $ot_money = ($base / $STANDARD_WORK_DAYS / 8) * 1.5 * $ot_hours;
    
    $gross = $actual_salary + $al_degree + $al_seniority + $al_lang + $bonus + $ot_money;
    $tax_money = $gross * ($tax_percent / 100);
    $total = $gross - $tax_money - $total_fine;

    // Làm tròn
    $ot_money = round($ot_money, -3);
    $tax_money = round($tax_money, -3);
    $total = round($total, -3);

    // Lưu DB
    $check = $conn->query("SELECT id FROM payroll WHERE user_id = $user_id AND month = '$month'");
    
    if ($check->num_rows > 0) {
        $sql = "UPDATE payroll SET base_salary=?, allowance_degree=?, allowance_seniority=?, allowance_language=?, work_days=?, overtime_hours=?, overtime_money=?, bonus=?, tax=?, tax_percent=?, late_count=?, total_fine=?, note=?, total_salary=?, status='paid' WHERE user_id=? AND month=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddddiddddddisss", $base, $al_degree, $al_seniority, $al_lang, $work_days, $ot_hours, $ot_money, $bonus, $tax_money, $tax_percent, $late_count, $total_fine, $note, $total, $user_id, $month);
    } else {
        $sql = "INSERT INTO payroll (user_id, month, base_salary, allowance_degree, allowance_seniority, allowance_language, work_days, overtime_hours, overtime_money, bonus, tax, tax_percent, late_count, total_fine, note, total_salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isddddiddddddisd", $user_id, $month, $base, $al_degree, $al_seniority, $al_lang, $work_days, $ot_hours, $ot_money, $bonus, $tax_money, $tax_percent, $late_count, $total_fine, $note, $total);
    }

    if ($stmt->execute()) $message = "<div class='alert alert-success'>Đã lưu lương thành công!</div>";
    else $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
}

// --- LẤY DANH SÁCH NHÂN VIÊN ---
$sql_users = "SELECT u.id, u.username, u.full_name, u.role_id, 
                     r.name as role_name, 
                     ed.start_date, ed.education_level, ed.certificate_type, ed.certificate_score 
              FROM users u 
              LEFT JOIN roles r ON u.role_id = r.id
              LEFT JOIN employee_details ed ON u.id = ed.user_id 
              WHERE u.role_id != 1"; 

if (!empty($search_query)) {
    $sql_users .= " AND u.full_name LIKE '%$search_query%'";
}

$users = $conn->query($sql_users);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tính Lương Nâng Cao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* CSS Tinh chỉnh giao diện bảng lương */
        .table-salary th {
            vertical-align: middle;
            text-align: center;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .table-salary td {
            vertical-align: middle; /* Căn giữa chiều dọc cho mọi ô */
            padding: 8px 5px !important;
        }
        .form-control-sm { 
            font-size: 0.9rem; 
            padding: 4px 8px;
            height: 32px; /* Chiều cao cố định cho input */
        }
        .input-money { 
            text-align: right; 
            font-weight: 500;
        }
        .readonly-input { 
            background-color: #f8f9fa; 
            border: 1px solid #e9ecef; 
            color: #495057; 
            font-weight: 600;
        }
        
        /* Cột Phụ cấp gọn gàng hơn */
        .allowance-group {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }
        .allowance-label {
            width: 30px;
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: bold;
        }
        .allowance-input {
            flex: 1;
        }
        
        /* Màu nền phân biệt */
        .col-income { background-color: #f0f9ff; } /* Xanh nhạt cho khoản cộng */
        .col-deduct { background-color: #fff5f5; } /* Đỏ nhạt cho khoản trừ */
        .col-total  { background-color: #fff3cd; } /* Vàng nhạt cho tổng */

        textarea.form-control-sm {
            resize: none;
            height: 70px; /* Khớp chiều cao với các ô phức tạp */
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-calculator text-success"></i> Bảng Lương (Theo Hợp Đồng)</h3>
            <a href="<?php echo ($_SESSION['role_id'] == 1) ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <?php echo $message; ?>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body py-3 bg-white rounded">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="fw-bold me-2 text-muted">Tháng:</label>
                        <input type="month" name="month" class="form-control d-inline-block border-primary" style="width: 160px;" value="<?php echo $selected_month; ?>" onchange="this.form.submit()">
                    </div>
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control" placeholder="Tìm nhân viên..." value="<?php echo $search_query; ?>">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary px-4" type="submit"><i class="fas fa-search"></i> Tìm</button>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="export_payroll.php?month=<?php echo $selected_month; ?>&search=<?php echo $search_query; ?>" class="btn btn-success fw-bold shadow-sm"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-salary mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width: 180px;">Nhân viên</th>
                                <th width="120">Lương HĐ</th>
                                <th width="70">Công</th>
                                <th class="col-income" width="160">Phụ cấp (VNĐ)</th>
                                <th class="col-income" width="100">OT (Giờ)</th>
                                <th class="col-income" width="120">Thưởng</th>
                                <th class="col-deduct" width="120">Phạt</th>
                                <th class="col-deduct" width="90">Thuế %</th>
                                <th>Ghi chú</th>
                                <th class="col-total" width="140">Thực lĩnh</th>
                                <th width="60">Lưu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users && $users->num_rows > 0): ?>
                            <?php while($u = $users->fetch_assoc()): 
                                $p_sql = "SELECT * FROM payroll WHERE user_id = " . $u['id'] . " AND month = '$selected_month'";
                                $p_data = $conn->query($p_sql)->fetch_assoc();

                                // --- 1. LẤY HỢP ĐỒNG ---
                                $contract_base_salary = 0;
                                $month_start = $selected_month . '-01';
                                $month_end = date("Y-m-t", strtotime($month_start));
                                
                                $c_sql = "SELECT base_salary FROM labor_contracts 
                                          WHERE user_id = {$u['id']} 
                                          AND start_date <= '$month_end' 
                                          AND (end_date >= '$month_start' OR end_date IS NULL) 
                                          ORDER BY start_date DESC LIMIT 1";
                                $c_result = $conn->query($c_sql);
                                if ($c_row = $c_result->fetch_assoc()) {
                                    $contract_base_salary = $c_row['base_salary'];
                                }

                                // --- 2. TÍNH TOÁN AUTO ---
                                $auto_sen = 0; $sen_text = "";
                                $auto_edu = 0; $edu_text = "";
                                $auto_lang = 0; $lang_text = "";

                                if ($contract_base_salary > 0) {
                                    // Thâm niên
                                    if (!empty($u['start_date'])) {
                                        $years = (new DateTime($u['start_date']))->diff(new DateTime())->y;
                                        if ($years >= 5) { $auto_sen = $ALLOWANCE_SENIORITY_5Y; $sen_text = ">5y"; }
                                        elseif ($years >= 3) { $auto_sen = $ALLOWANCE_SENIORITY_3Y; $sen_text = ">3y"; }
                                        elseif ($years >= 1) { $auto_sen = $ALLOWANCE_SENIORITY_1Y; $sen_text = ">1y"; }
                                    }
                                    // Bằng cấp
                                    $edu_level = $u['education_level'] ?? '';
                                    if ($edu_level == 'Đại học') { $auto_edu = $ALLOWANCE_DEGREE_BACHELOR; $edu_text="ĐH"; } 
                                    elseif ($edu_level == 'Thạc sĩ') { $auto_edu = $ALLOWANCE_DEGREE_MASTER; $edu_text="ThS"; } 
                                    elseif ($edu_level == 'Tiến sĩ') { $auto_edu = $ALLOWANCE_DEGREE_PHD; $edu_text="TS"; }
                                    // Ngoại ngữ
                                    $type = $u['certificate_type'] ?? 'None';
                                    $score = $u['certificate_score'] ?? 0;
                                    if ($type == 'IELTS') {
                                        $lang_text = "IELTS $score";
                                        if ($score >= 8.0) $auto_lang = $ALLOWANCE_IELTS_8;
                                        elseif ($score >= 7.0) $auto_lang = $ALLOWANCE_IELTS_7;
                                        elseif ($score >= 6.0) $auto_lang = $ALLOWANCE_IELTS_6;
                                    } elseif ($type == 'TOEIC') {
                                        $lang_text = "TOEIC $score";
                                        if ($score >= 800) $auto_lang = $ALLOWANCE_IELTS_7;
                                        elseif ($score >= 600) $auto_lang = $ALLOWANCE_IELTS_6;
                                    }
                                }

                                // --- 3. GÁN GIÁ TRỊ ---
                                $att_sql = "SELECT COUNT(*) as days FROM attendance WHERE user_id = " . $u['id'] . " AND date LIKE '$selected_month%'";
                                $work_days_count = $conn->query($att_sql)->fetch_assoc()['days'];

                                $base = ($p_data['base_salary'] ?? 0) > 0 ? $p_data['base_salary'] : $contract_base_salary;
                                $val_edu = ($p_data['allowance_degree'] ?? 0) > 0 ? $p_data['allowance_degree'] : $auto_edu;
                                $val_sen = ($p_data['allowance_seniority'] ?? 0) > 0 ? $p_data['allowance_seniority'] : $auto_sen;
                                $val_lang = ($p_data['allowance_language'] ?? 0) > 0 ? $p_data['allowance_language'] : $auto_lang;
                                $val_days = $p_data['work_days'] ?? $work_days_count;
                                $val_ot_hours = $p_data['overtime_hours'] ?? 0;
                                $val_ot_money = $p_data['overtime_money'] ?? 0;
                                $val_bonus = $p_data['bonus'] ?? 0;
                                $val_tax_percent = $p_data['tax_percent'] ?? 0; 
                                $val_late_count = $p_data['late_count'] ?? 0;
                                $val_total_fine = $p_data['total_fine'] ?? 0;
                                $val_note = $p_data['note'] ?? '';

                                $actual_salary = ($base / $STANDARD_WORK_DAYS) * $val_days;
                                $ot_money = ($base / $STANDARD_WORK_DAYS / 8) * 1.5 * $val_ot_hours;
                                $gross = $actual_salary + $val_edu + $val_sen + $val_lang + $val_bonus + $ot_money;
                                $tax_money = $gross * ($val_tax_percent / 100);
                                $val_total = $gross - $tax_money - $val_total_fine;
                            ?>
                            <tr class="salary-row">
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="month" value="<?php echo $selected_month; ?>">
                                    
                                    <td class="text-start ps-3">
                                        <div class="fw-bold text-dark"><?php echo $u['full_name']; ?></div>
                                        <div class="text-muted small mb-1"><?php echo $u['username']; ?></div>
                                        <div>
                                            <?php if($edu_text) echo "<span class='badge bg-light text-dark border border-secondary me-1'>$edu_text</span>"; ?>
                                            <?php if($sen_text) echo "<span class='badge bg-light text-dark border border-secondary me-1'>$sen_text</span>"; ?>
                                            <?php if($lang_text) echo "<span class='badge bg-info text-dark'>$lang_text</span>"; ?>
                                        </div>
                                        <?php if($contract_base_salary == 0): ?>
                                            <div class="text-danger small fw-bold mt-1"><i class="fas fa-exclamation-triangle"></i> Thiếu HĐ</div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <input type="number" name="base_salary" class="form-control form-control-sm input-money readonly-input" value="<?php echo $base; ?>" readonly>
                                    </td>

                                    <td>
                                        <input type="number" step="0.5" name="work_days" class="form-control form-control-sm text-center fw-bold" value="<?php echo $val_days; ?>" oninput="calculateRow(this)">
                                    </td>

                                    <td class="col-income">
                                        <div class="allowance-group">
                                            <span class="allowance-label">BC</span>
                                            <input type="number" step="1000" name="allowance_degree" class="form-control form-control-sm input-money allowance-input" value="<?php echo $val_edu; ?>" oninput="calculateRow(this)">
                                        </div>
                                        <div class="allowance-group">
                                            <span class="allowance-label">TN</span>
                                            <input type="number" step="1000" name="allowance_seniority" class="form-control form-control-sm input-money allowance-input" value="<?php echo $val_sen; ?>" oninput="calculateRow(this)">
                                        </div>
                                        <div class="allowance-group mb-0">
                                            <span class="allowance-label">NN</span>
                                            <input type="number" step="1000" name="allowance_language" class="form-control form-control-sm input-money allowance-input" value="<?php echo $val_lang; ?>" oninput="calculateRow(this)">
                                        </div>
                                    </td>

                                    <td class="col-income">
                                        <input type="number" step="0.5" name="overtime_hours" class="form-control form-control-sm text-center mb-1" value="<?php echo $val_ot_hours; ?>" oninput="calculateRow(this)">
                                        <div class="text-end small text-success fw-bold ot-money-display"><?php echo number_format($ot_money); ?></div>
                                    </td>

                                    <td class="col-income">
                                        <input type="number" step="1000" name="bonus" class="form-control form-control-sm input-money text-success fw-bold" value="<?php echo $val_bonus; ?>" oninput="calculateRow(this)">
                                    </td>
                                    
                                    <td class="col-deduct">
                                        <input type="hidden" name="late_count" value="<?php echo $val_late_count; ?>">
                                        <input type="number" step="1000" name="total_fine" class="form-control form-control-sm input-money text-danger fw-bold" value="<?php echo $val_total_fine; ?>" oninput="calculateRow(this)">
                                    </td>

                                    <td class="col-deduct">
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.1" name="tax_percent" class="form-control text-center text-danger" value="<?php echo $val_tax_percent; ?>" oninput="calculateRow(this)">
                                            <span class="input-group-text px-1">%</span>
                                        </div>
                                        <div class="text-end small text-muted tax-money-display mt-1">-<?php echo number_format($tax_money); ?></div>
                                    </td>

                                    <td>
                                        <textarea name="note" class="form-control form-control-sm" placeholder="..."><?php echo $val_note; ?></textarea>
                                    </td>
                                    
                                    <td class="col-total text-end">
                                        <span class="fw-bold text-primary total-salary-display fs-6"><?php echo number_format($val_total); ?></span>
                                    </td>

                                    <td>
                                        <button type="submit" class="btn btn-sm btn-success w-100 py-2"><i class="fas fa-save"></i></button>
                                    </td>
                                </form>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">Không tìm thấy nhân viên nào phù hợp.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="alert alert-light border mt-3 small shadow-sm">
            <strong><i class="fas fa-info-circle text-primary"></i> Ghi chú hệ thống:</strong>
            <ul class="mb-0 ps-3">
                <li>Lương cứng được lấy tự động từ Hợp đồng lao động có hiệu lực.</li>
                <li>Các khoản phụ cấp (Bằng cấp, Thâm niên, Ngoại ngữ) chỉ được tính khi nhân viên có Hợp đồng.</li>
            </ul>
        </div>
    </div>

    <script>
        function calculateRow(element) {
            const row = element.closest('tr');
            
            // Hàm helper để lấy giá trị float an toàn
            const getVal = (selector) => parseFloat(row.querySelector(`input[name="${selector}"]`).value) || 0;

            const base = getVal('base_salary');
            const days = getVal('work_days');
            const deg = getVal('allowance_degree');
            const sen = getVal('allowance_seniority');
            const lang = getVal('allowance_language'); 
            const bonus = getVal('bonus');
            const tax_percent = getVal('tax_percent');
            const ot_hours = getVal('overtime_hours');
            const fine = getVal('total_fine');
            
            // Tính toán
            let actual_salary = (base / <?php echo $STANDARD_WORK_DAYS; ?>) * days;
            let ot_money = (base / <?php echo $STANDARD_WORK_DAYS; ?> / 8) * 1.5 * ot_hours;
            let gross = actual_salary + deg + sen + lang + bonus + ot_money;
            let tax_money = gross * (tax_percent / 100);

            // Làm tròn
            ot_money = Math.round(ot_money / 1000) * 1000;
            tax_money = Math.round(tax_money / 1000) * 1000;
            let total = Math.round((gross - tax_money - fine) / 1000) * 1000;

            // Cập nhật giao diện (Format VNĐ)
            const fmt = new Intl.NumberFormat('vi-VN');
            row.querySelector('.ot-money-display').innerText = fmt.format(ot_money);
            row.querySelector('.tax-money-display').innerText = '-' + fmt.format(tax_money);
            row.querySelector('.total-salary-display').innerText = fmt.format(total);
        }
    </script>
</body>
</html>