<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ghostthread";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = file_get_contents(__DIR__ . '/database/ghostthread.sql');

$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "<h2 style='color: #6c63ff;'>GhostThread Setup</h2><hr>";

foreach ($statements as $statement) {
    if (!empty($statement) && stripos($statement, 'INSERT') !== 0) {
        if ($conn->query($statement) === TRUE) {
            $preview = substr($statement, 0, 60);
            echo "<p style='color: #2ed573;'>✓ " . htmlspecialchars($preview) . "...</p>";
        } else {
            echo "<p style='color: #ff4757;'>✗ Error: " . $conn->error . "</p>";
        }
    }
}

echo "<hr><h3 style='color: #2ed573;'>✓ Database Setup Complete!</h3>";
echo "<h4>Admin Login:</h4>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> password</p>";
echo "<br>";
echo "<a href='../admin/index.php' style='display: inline-block; padding: 12px 25px; background: #6c63ff; color: white; text-decoration: none; border-radius: 25px; margin-right: 10px;'>Go to Admin Panel</a>";
echo "<a href='index.php' style='display: inline-block; padding: 12px 25px; background: #ff6b9d; color: white; text-decoration: none; border-radius: 25px;'>Go to GhostThread</a>";

$conn->close();
?>
