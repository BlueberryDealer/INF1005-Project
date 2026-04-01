<?php
// -------------------------------------------------------
// chatbot_api.php — QUENCH AI Assistant (Claude API proxy)
// -------------------------------------------------------

require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../config/db_connect.php';
$session = new SessionManager();

header('Content-Type: application/json');

// ── API Key (loaded from db-config.ini) ──
$config = parse_ini_file(db_config_path());
$apiKey = $config['anthropic_api_key'] ?? '';

if (empty($apiKey)) {
    echo json_encode(['error' => 'Chatbot is not configured.']);
    exit;
}

// ── Validate request ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage) || mb_strlen($userMessage) > 500) {
    echo json_encode(['error' => 'Message must be between 1 and 500 characters.']);
    exit;
}

// ── Rate limiting (simple session-based) ──
if (!isset($_SESSION['chatbot_count'])) {
    $_SESSION['chatbot_count'] = 0;
    $_SESSION['chatbot_reset'] = time();
}

// Reset counter every 10 minutes
if (time() - $_SESSION['chatbot_reset'] > 600) {
    $_SESSION['chatbot_count'] = 0;
    $_SESSION['chatbot_reset'] = time();
}

$_SESSION['chatbot_count']++;

if ($_SESSION['chatbot_count'] > 20) {
    echo json_encode(['error' => 'Too many messages. Please wait a few minutes.']);
    exit;
}

// ── System prompt — scoped to QUENCH ──
$systemPrompt = <<<PROMPT
You are the QUENCH AI Assistant — a friendly, helpful customer service chatbot for QUENCH, an online drinks store based in Singapore.

About QUENCH:
- We are a premium online drinks store delivering across Singapore
- We offer same-day delivery across Singapore
- New customers get 10% off with code QUENCH10 (subscribe to our newsletter or sign up to receive it)
- We accept major credit/debit cards at checkout
- Orders can be tracked in the "Order History" section after logging in
- For returns or issues, customers should email quench.store.sg@gmail.com
- Customers must create an account to place an order

Our Products:
ENERGY DRINKS:
- Blueberry Blast — $5.00 (Blue Raspberry energy drink, bold berry flavour)
- Raspberry Blast — $5.00 (Raspberry energy drink, fruity and refreshing)

SODAS:
- Apple Fizz — $4.50 (Crisp apple soda, caffeine-free, sparkling)
- Grape Fizz — $4.00 (Grape-flavoured sparkling soda)

PREMIUM:
- QUENCH Deluxe — $9,999.00 (Our ultra-premium limited edition collector's bottle)

Your rules:
- Keep responses SHORT (2-3 sentences max) and conversational
- Only answer questions related to QUENCH, drinks, orders, delivery, or account help
- If asked about something unrelated, politely redirect to QUENCH topics
- Only mention products listed above — never make up product names or prices
- Be warm, friendly, and use a casual tone
- If you don't know something specific, suggest the customer check the website or contact support
- When recommending drinks, use the actual product names and prices above
PROMPT;

// ── Call Claude API ──
$payload = [
    'model' => 'claude-haiku-4-5-20251001',
    'max_tokens' => 256,
    'system' => $systemPrompt,
    'messages' => [
        ['role' => 'user', 'content' => $userMessage]
    ]
];

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    error_log('Chatbot curl error: ' . $curlError);
    echo json_encode(['error' => 'Unable to reach AI service. Please try again.']);
    exit;
}

if ($httpCode !== 200) {
    error_log('Chatbot API error (' . $httpCode . '): ' . $response);
    echo json_encode(['error' => 'AI service temporarily unavailable.']);
    exit;
}

$data = json_decode($response, true);
$reply = '';

if (!empty($data['content'])) {
    foreach ($data['content'] as $block) {
        if ($block['type'] === 'text') {
            $reply .= $block['text'];
        }
    }
}

if (empty($reply)) {
    $reply = "Sorry, I couldn't process that. Could you try rephrasing your question?";
}

echo json_encode(['reply' => $reply]);