<?php
// api/detect_mood_api.php
// API Endpoint to bridge PHP Frontend with Python AI Backend
// Handles POST requests for Text, Voice, and Face mood detection.

header('Content-Type: application/json');
session_start();

require_once '../database/connection.php';

// Configuration - Python Backend URL
$valid_python_host = 'http://127.0.0.1:5000';

// 1. Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Use POST.']);
    exit();
}

// 2. Validate User Session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit();
}

// 3. Determine Input Type
$input_type = $_POST['type'] ?? 'text'; // 'text', 'voice', or 'face'

$python_endpoint = "";
$post_fields = [];
$is_multipart = false;
$cleanup_file = null;

if ($input_type === 'text') {
    // --- TEXT MOOD DETECTION ---
    if (!isset($_POST['text']) || empty(trim($_POST['text']))) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No text provided.']);
        exit();
    }

    $python_endpoint = '/detect/text';
    $post_fields = json_encode(['text' => trim($_POST['text'])]);
    $content_type = 'Content-Type: application/json';

} elseif ($input_type === 'voice') {
    // --- VOICE MOOD DETECTION ---
    if (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No valid audio file uploaded.']);
        exit();
    }

    // --- SECURITY: FILE SIZE LIMIT (10MB) ---
    $max_audio_size = 10 * 1024 * 1024; // 10MB
    if ($_FILES['audio_file']['size'] > $max_audio_size) {
        http_response_code(413);
        echo json_encode(['status' => 'error', 'message' => 'Audio file too large. Max limit is 10MB.']);
        exit();
    }

    $python_endpoint = '/detect/voice';
    $is_multipart = true;

    $audio_path = $_FILES['audio_file']['tmp_name'];
    $audio_name = $_FILES['audio_file']['name'];
    $audio_mime = $_FILES['audio_file']['type'];

    $post_fields = [
        'file' => new CURLFile($audio_path, $audio_mime, $audio_name)
    ];

} elseif ($input_type === 'face') {
    // --- FACE MOOD DETECTION ---
    if (!isset($_POST['image_data'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No image data provided.']);
        exit();
    }

    $base64_string = $_POST['image_data'];

    // --- SECURITY: IMAGE SIZE LIMIT (5MB) ---
    // Approximate base64 size calculation: (n * 3) / 4
    $approx_size = (strlen($base64_string) * 3) / 4;
    $max_image_size = 5 * 1024 * 1024; // 5MB

    if ($approx_size > $max_image_size) {
        http_response_code(413);
        echo json_encode(['status' => 'error', 'message' => 'Image data too large. Max limit is 5MB.']);
        exit();
    }

    if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $matches)) {
        $base64_string = substr($base64_string, strpos($base64_string, ',') + 1);
        $type = strtolower($matches[1]);
    } else {
        $type = 'jpeg'; // Default
    }

    $base64_string = str_replace(' ', '+', $base64_string);
    $image_data = base64_decode($base64_string);

    if ($image_data === false) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Base64 decode failed.']);
        exit();
    }

    $temp_filename = 'face_' . uniqid() . '.' . $type;
    $temp_filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $temp_filename;
    file_put_contents($temp_filepath, $image_data);

    $python_endpoint = '/detect/face';
    $is_multipart = true;
    $post_fields = [
        'file' => new CURLFile($temp_filepath, 'image/' . $type, $temp_filename)
    ];

    $cleanup_file = $temp_filepath;

} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid detection type. Use "text", "voice", or "face".']);
    exit();
}

// 4. Send Request to Python Service
$ch = curl_init();
$url = $valid_python_host . $python_endpoint;

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Increased timeout for face/audio

if ($is_multipart) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
} else {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
}

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Cleanup temp file if created
if ($cleanup_file && file_exists($cleanup_file)) {
    unlink($cleanup_file);
}

// 5. Handle Python Response
if ($response === false) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => 'AI Service Unavailable. Is the Python backend running?',
        'debug' => $curl_error
    ]);
    exit();
}

// 6. Process Successful Response
$decoded_response = json_decode($response, true);

if ($http_code === 200 && isset($decoded_response['mood'])) {

    $detected_mood = $decoded_response['mood'];
    $confidence = $decoded_response['confidence'] ?? 0;

    // Save to Session for Recommendation Page
    $_SESSION['last_detected_mood'] = $detected_mood;
    $_SESSION['mood_detection_method'] = $input_type;

    // --- SAVE TO DATABASE (Skip for guests) ---
    $is_guest = $_SESSION['is_guest'] ?? false;
    if (!$is_guest) {
        try {
            $user_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare("INSERT INTO user_mood_history (user_id, mood, input_type) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $detected_mood, $input_type]);
        } catch (Exception $e) {
            error_log("Database Error in detect_mood_api: " . $e->getMessage());
        }
    }

    // Return Success to Frontend
    echo json_encode([
        'status' => 'success',
        'mood' => $detected_mood,
        'confidence' => $confidence,
        'redirect_url' => 'analyzing.php'
    ]);

} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'AI Processing Failed.',
        'details' => $decoded_response
    ]);
}
?>
