<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$email = $_POST['email'] ?? trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid email']));
}

// TODO: Replace with your cloud MySQL credentials
// Use PlanetScale (free tier), Railway, or similar
$host = $_ENV['MYSQL_HOST'] ?? '127.0.0.1';
$username = $_ENV['MYSQL_USER'] ?? 'root';
$password = $_ENV['MYSQL_PASSWORD'] ?? 'Lingaraju@123';
$dbname = $_ENV['MYSQL_DATABASE'] ?? 'ngo_website';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Check if already subscribed
    $stmt = $pdo->prepare('SELECT id FROM subscribers WHERE email = ?');
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        exit(json_encode(['message' => 'Already subscribed']));
    }

    // Insert new subscriber
    $stmt = $pdo->prepare('INSERT INTO subscribers (email, subscribed_at) VALUES (?, NOW())');
    $stmt->execute([$email]);
    
    exit(json_encode(['message' => 'Success']));
    
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database error']));
}
?>
