<?php


function generate_ai_tips($event_title, $event_description, $event_location) {
    if (AI_API_KEY === 'YOUR_SECRET_AI_KEY_HERE') {
        return "AI is not configured. Please add your API key to **ai_functions.php**.";
    }

    // This is the prompt that guides the AI's output
    $prompt = "As a local expert in Lebanon, generate 3 practical, concise tips for an event titled '{$event_title}' in '{$event_location}' with this description: '{$event_description}'. Format the response as a markdown list starting with a bold title like '**Top Tips**'. The tips should cover practical advice like dress code, parking, or local transport.";

    // Data payload for the AI API (example for a generic text API)
    $data = [
        // 'model' and 'prompt' fields will vary based on the provider
        'model' => 'gemini-pro', 
        'contents' => [['parts' => [['text' => $prompt]]]],
    ];

    $ch = curl_init(AI_API_ENDPOINT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        // Example: The API key is passed in the header or the URL, depending on the service
        'X-API-KEY: ' . AI_API_KEY 
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return "AI Service Error: Could not connect to API.";
    }

    $result = json_decode($response, true);
    
    // !!! This line must be adjusted to correctly parse the specific AI API's response
    // E.g., for some APIs, the content might be deep inside 'candidates[0].content.parts[0].text'
    $ai_text = $result['generated_text'] ?? "AI tip generation failed: Could not parse response.";
    
    return $ai_text;
}
?>