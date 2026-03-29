<?php
// Errors only for development
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");

include "db_connection.php"; 

$apiKey= ""
$data = json_decode(file_get_contents("php://input"), true);
$userMessage = trim($data["message"] ?? "");

if (empty($userMessage)) {
    echo json_encode(["reply" => "How can I help you find a great spot in Lebanon today?"]);
    exit;
}

// --- STEP 1: FETCH BUSINESSES/EVENTS FROM DB ---
// We pull location and description (vibe) to give the AI data to work with
$businessContext = "No specific spots found in the database yet.";
$sql = "SELECT title, location, description, type FROM events LIMIT 10";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $businessContext = "Here are some top spots currently on our website:\n";
    while ($row = $res->fetch_assoc()) {
        // We include the 'description' because that's where the 'vibe' usually is
        $businessContext .= "- {$row['title']} in {$row['location']}. Vibe: {$row['description']} (Type: {$row['type']})\n";
    }
}

// --- STEP 2: BUILD THE SYSTEM PROMPT ---
$systemPrompt = "You are the Explore Lebanon AI. 
Task: Help users find businesses based on LOCATION and VIBE. 
Website Info: Users can book directly via the 'Book Now' button on any business page. They can filter results on the Home Screen.
Data from our website:
$businessContext";

// --- STEP 3: CALL OPENAI (Corrected for GPT-4o-mini) ---
$payload = [
    "model" => "gpt-4o-mini", // Use this model for speed and cost-efficiency
    "messages" => [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $userMessage]
    ],
    "temperature" => 0.7
];

$ch = curl_init("https://api");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true 
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

// --- STEP 4: CLEAN OUTPUT ---
$aiReply = $result["choices"][0]["message"]["content"] ?? "I'm having a little trouble connecting to the Lebanon guide right now. Please try again!";

echo json_encode([
    "reply" => $aiReply,
    "status" => "success"
], JSON_UNESCAPED_UNICODE);