<?php
require 'config/db.php';
require 'config/constants.php';
require 'config/helpers.php';

$result = mysqli_query($conn,
    "SELECT COUNT(*) as total FROM users"
);
$count = mysqli_fetch_assoc($result);

echo "<h2>✅ Soko Link Backend Test</h2>";
echo "✅ Database connected!<br>";
echo "✅ Users in DB: ".$count['total']."<br>";
echo "✅ App: ".APP_NAME."<br>";
echo "✅ Version: ".APP_VERSION."<br>";
echo "<br><h3>✅ Backend is ready!</h3>";
?>