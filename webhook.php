<?php
/**
 * DOOHOON LINE BOT - ALL IN ONE (v2)
 * ไฟล์เดียว - ตอบหุ้น + คำถามทั่วไป
 * 
 * ตั้งค่า ENV:
 * - LINE_CHANNEL_TOKEN
 * - FINNHUB_API_KEY
 * - OPENAI_API_KEY
 */

set_time_limit(30);
error_reporting(E_ALL);
ini_set('display_errors', 0);

// LOG
function logDebug($msg) {
    $ts = date('Y-m-d H:i:s');
    file_put_contents(__DIR__ . '/bot_debug.log', "[{$ts}] {$msg}\n", FILE_APPEND);
}

// HTTP GET
function httpGetJson($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => false]);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res ? json_decode($res, true) : null;
}

// HTTP POST
function httpPostJson($url, $payload, $headers = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res ? json_decode($res, true) : null;
}

// STOCK QUOTE
function getStockQuote($symbol) {
    $key = getenv("FINNHUB_API_KEY");
    if (!$key) return null;
    $url = "https://finnhub.io/api/v1/quote?symbol=" . urlencode($symbol) . "&token=" . urlencode($key);
    return httpGetJson($url);
}

// SEARCH STOCK
function searchStock($query) {
    $key = getenv("FINNHUB_API_KEY");
    if (!$key) return null;
    $url = "https://finnhub.io/api/v1/search?q=" . urlencode($query) . "&token=" . urlencode($key);
    $data = httpGetJson($url);
    if (!$data || empty($data['result'])) return null;
    foreach ($data['result'] as $r) {
        if (!empty($r['symbol']) && strtoupper($r['type'] ?? '') === 'EQUITY') {
            return strtoupper($r['symbol']);
        }
    }
    return strtoupper($data['result'][0]['symbol'] ?? '');
}

// STOCK NEWS
function getStockNews($symbol) {
    $key = getenv("FINNHUB_API_KEY");
    if (!$key) return [];
    $to = date('Y-m-d');
    $from = date('Y-m-d', strtotime("-7 days"));
    $url = "https://finnhub.io/api/v1/company-news?symbol=" . urlencode($symbol) . "&from={$from}&to={$to}&token=" . urlencode($key);
    $data = httpGetJson($url);
    if (!$data) return [];
    $out = [];
    foreach ($data as $n) {
        if (empty($n['headline'])) continue;
        $out[] = $n['headline'];
        if (count($out) >= 3) break;
    }
    return $out;
}

// ANALYZE STOCK WITH AI
function analyzeStock($symbol, $price, $change, $pct, $news) {
    $key = getenv("OPENAI_API_KEY");
    if (!$key) return null;
    $newsText = implode("\n", array_map(fn($n) => "• " . $n, $news));
    if (!$newsText) $newsText = "ไม่มีข่าว";
    $prompt = "สรุปหุ้น {$symbol}: ราคา {$price} USD, เปลี่ยน {$change} ({$pct}%), ข่าว: {$newsText}\nตอบ 5 บรรทัด";
    $payload = [
        "model" => "gpt-4o-mini",
        "temperature" => 0.6,
        "max_tokens" => 300,
        "messages" => [
            ["role" => "system", "content" => "นักวิเคราะห์หลักทรัพย์"],
            ["role" => "user", "content" => $prompt],
        ],
    ];
    $headers = ["Content-Type: application/json", "Authorization: Bearer {$key}"];
    $res = httpPostJson("https://api.openai.com/v1/chat/completions", $payload, $headers);
    return $res && isset($res['choices'][0]['message']['content']) ? trim($res['choices'][0]['message']['content']) : null;
}

// ASK AI
function askAI($q) {
    $key = getenv("OPENAI_API_KEY");
    if (!$key) return null;
    $payload = [
        "model" => "gpt-4o-mini",
        "temperature" => 0.7,
        "max_tokens" => 200,
        "messages" => [
            ["role" => "system", "content" => "AI Assistant"],
            ["role" => "user", "content" => substr($q, 0, 300)],
        ],
    ];
    $headers = ["Content-Type: application/json", "Authorization: Bearer " . getenv("OPENAI_API_KEY")];
    $res = httpPostJson("https://api.openai.com/v1/chat/completions", $payload, $headers);
    return $res && isset($res['choices'][0]['message']['content']) ? trim($res['choices'][0]['message']['content']) : null;
}

// SEND LINE
function sendReply($token, $text) {
    $msg = getenv("LINE_CHANNEL_TOKEN");
    if (!$msg) return;
    $body = ["replyToken" => $token, "messages" => [["type" => "text", "text" => $text]]];
    httpPostJson("https://api.line.me/v2/bot/message/reply", $body, [
        "Content-Type: application/json", "Authorization: Bearer {$msg}",
    ]);
}

// MAIN
http_response_code(200);
header('Content-Type: application/json');

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (empty($data['events'])) {
    echo json_encode(["status" => "ok"]);
    exit;
}

foreach ($data['events'] as $event) {
    if ($event['type'] !== 'message' || ($event['message']['type'] ?? '') !== 'text') continue;
    
    $text = trim($event['message']['text'] ?? '');
    $token = $event['replyToken'] ?? '';
    
    if (!$token) continue;

    try {
        // CHECK STOCK
        $symbol = null;
        if (preg_match('/^([A-Z]{1,6})$/i', $text)) {
            $symbol = strtoupper($text);
        } elseif (strlen($text) > 2) {
            $symbol = searchStock($text);
        }

        if ($symbol) {
            $quote = getStockQuote($symbol);
            if (!$quote) {
                sendReply($token, "❌ ไม่พบหุ้น {$symbol}");
                continue;
            }
            $news = getStockNews($symbol);
            $analysis = analyzeStock($symbol, $quote['c'], $quote['d'], $quote['dp'], $news);
            sendReply($token, $analysis ?? "⚠️ AI ติดขัด");
            continue;
        }

        // GREETING
        if (in_array(mb_strtolower($text), ['สวัสดี', 'hello', 'hi'])) {
            sendReply($token, "สวัสดี! 👋\n📊 พิมพ์ชื่อหุ้น เช่น NVDA\n💬 หรือถามเรื่องอื่น");
            continue;
        }

        // GENERAL AI
        $ans = askAI($text);
        sendReply($token, $ans ?? "⚠️ ติดขัด");

    } catch (Exception $e) {
        sendReply($token, "⚠️ Error");
    }
}

echo json_encode(["status" => "ok"]);
?>
```

---

## 📋 ขั้นตอนใช้งาน:

1. **Copy code ข้างบน** → สร้างไฟล์ `webhook.php`
2. **ตั้งค่า .env** (ใส่ 3 API keys)
3. **Upload ไปที่ server**
4. **ตั้ง Webhook URL ใน LINE Console:**
```
   https://your-domain.com/webhook.php