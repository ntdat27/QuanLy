<?php
// Tắt hiển thị lỗi để tránh hỏng JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// 1. KẾT NỐI DATABASE
require_once 'db_connect.php';

// 2. CẤU HÌNH API KEY
$apiKey = "AIzaSyBtbgBVud3imn6gpqXMb-9Mphq4jV88i2w"; 

// Nhận dữ liệu từ khách
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Xin chào! Tôi là trợ lý ảo DDMQ. Bạn cần tìm hiểu thông tin gì về trung tâm?']);
    exit;
}
if (empty($apiKey) || strpos($apiKey, 'DÁN_MÃ') !== false) {
    echo json_encode(['reply' => 'Lỗi hệ thống: Admin chưa cấu hình API Key.']);
    exit;
}

// --- BƯỚC QUAN TRỌNG: NẠP DỮ LIỆU VÀO "BỘ NÃO" BOT ---

// A. Lấy danh sách Phòng ban (Để trả lời câu hỏi "Có bao nhiêu phòng ban?")
$dept_info = "";
$sql_dept = "SELECT name, description FROM departments";
$res_dept = $conn->query($sql_dept);
if ($res_dept->num_rows > 0) {
    $dept_info .= "Trung tâm hiện có " . $res_dept->num_rows . " phòng ban:\n";
    while($row = $res_dept->fetch_assoc()) {
        $dept_info .= "- {$row['name']}: {$row['description']}\n";
    }
}

// B. Lấy thông tin Lương & Vị trí (Để trả lời "Lương giáo viên thế nào?")
$salary_info = "";
$sql_role = "SELECT name, default_salary, description FROM roles WHERE id != 1"; // Trừ Admin ra
$res_role = $conn->query($sql_role);
if ($res_role->num_rows > 0) {
    $salary_info .= "Cơ chế lương cứng tham khảo (chưa bao gồm phụ cấp & thưởng):\n";
    while($row = $res_role->fetch_assoc()) {
        $sal = number_format($row['default_salary']);
        $salary_info .= "- Vị trí {$row['name']}: {$sal} VNĐ/tháng ({$row['description']}).\n";
    }
    $salary_info .= "Ngoài ra còn có phụ cấp bằng cấp (Thạc sĩ, Tiến sĩ, IELTS), phụ cấp thâm niên và thưởng hiệu quả công việc.";
}

// C. Lấy danh sách Lớp học (Giữ nguyên logic cũ)
$class_info = "";
$sql_classes = "SELECT c.class_name, c.schedule, c.room, u.full_name as teacher_name 
                FROM classes c 
                JOIN users u ON c.teacher_id = u.id";
$res_classes = $conn->query($sql_classes);
if ($res_classes->num_rows > 0) {
    while($row = $res_classes->fetch_assoc()) {
        $class_info .= "- Lớp {$row['class_name']}: Học {$row['schedule']} tại {$row['room']}, GV: {$row['teacher_name']}.\n";
    }
} else {
    $class_info = "Hiện chưa có lịch mở lớp mới.";
}

// D. Lấy Tin tức mới nhất
$news_info = "";
$sql_news = "SELECT title, summary FROM news ORDER BY created_at DESC LIMIT 3";
$res_news = $conn->query($sql_news);
if ($res_news->num_rows > 0) {
    while($row = $res_news->fetch_assoc()) {
        $news_info .= "- {$row['title']}: {$row['summary']}\n";
    }
}

// --- 3. TẠO NGỮ CẢNH (CONTEXT) ---
$systemInstruction = "
Bạn là Trợ lý ảo của Trung tâm Anh ngữ DDMQ.
Nhiệm vụ: Trả lời câu hỏi của khách hàng và nhân viên dựa trên dữ liệu thực tế dưới đây.

--- DỮ LIỆU HỆ THỐNG (KNOWLEDGE BASE) ---
1. THÔNG TIN CƠ CẤU TỔ CHỨC (PHÒNG BAN):
$dept_info

2. CHÍNH SÁCH LƯƠNG & VỊ TRÍ:
$salary_info

3. CÁC LỚP HỌC ĐANG MỞ:
$class_info

4. TIN TỨC & SỰ KIỆN MỚI:
$news_info

5. LIÊN HỆ:
   - Hotline: 0862 7516 189
   - Email: ddmq@gmail.com
   - Địa chỉ: Đại học Tài nguyên và Môi trường Hà Nội.
--------------------------------------------

YÊU CẦU TRẢ LỜI:
- Trả lời ngắn gọn, chính xác theo dữ liệu trên.
- Nếu hỏi về lương, hãy đưa ra mức lương cứng tham khảo và nhắc đến các khoản phụ cấp.
- Giọng điệu thân thiện, chuyên nghiệp.
";

// --- 4. GỬI YÊU CẦU ĐẾN GOOGLE GEMINI ---
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemInstruction . "\n\nNgười dùng hỏi: " . $userMessage]
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix lỗi SSL trên XAMPP
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

// --- 5. TRẢ VỀ KẾT QUẢ ---
if ($curlError) {
    echo json_encode(['reply' => "Lỗi kết nối: $curlError"]);
} else {
    $result = json_decode($response, true);
    if (isset($result['error'])) {
        $msg = $result['error']['message'] ?? 'Lỗi API';
        echo json_encode(['reply' => "Xin lỗi, hệ thống đang bảo trì ($msg)."]);
    } else {
        $botReply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, mình chưa hiểu câu hỏi. Bạn có thể nói rõ hơn không?';
        // Xóa markdown
        $botReply = str_replace(['**', '*', '#'], '', $botReply); 
        echo json_encode(['reply' => $botReply]);
    }
}
?>