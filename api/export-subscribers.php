<?php
header('Content-Disposition: attachment; filename="subscribers.xlsx"');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

// Database connection
$host = $_ENV['MYSQL_HOST'] ?? 'localhost';
$username = $_ENV['MYSQL_USER'] ?? 'root';
$password = $_ENV['MYSQL_PASSWORD'] ?? 'Lingaraju@123';
$dbname = $_ENV['MYSQL_DATABASE'] ?? 'ngo_website';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Fetch all subscribers
    $stmt = $pdo->query('SELECT id, email, subscribed_at FROM subscribers ORDER BY subscribed_at DESC');
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create simple CSV (Excel-compatible)
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['ID', 'Email', 'Subscribed Date']);
    
    // Data rows
    foreach ($data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['email'],
            $row['subscribed_at']
        ]);
    }
    
    fclose($output);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
