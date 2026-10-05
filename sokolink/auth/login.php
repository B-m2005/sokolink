<?php
require '../config/db.php';
require '../config/constants.php';
require '../config/helpers.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false,'Method not allowed',
                 null,405);
}

$data     = json_decode(
    file_get_contents('php://input'), true
);
$phone    = clean($conn, $data['phone'] ?? '');
$password = $data['password'] ?? '';

if(!$phone || !$password) {
    sendResponse(
        false,
        'Phone and password required',
        null, 400
    );
}

// Find user
$result = mysqli_query($conn,
    "SELECT * FROM users 
     WHERE phone='$phone' 
     AND is_active=1"
);

if(mysqli_num_rows($result) === 0) {
    sendResponse(
        false,
        'Invalid phone or password',
        null, 401
    );
}

$user = mysqli_fetch_assoc($result);

// Verify password
if(!password_verify($password, $user['password'])) {
    sendResponse(
        false,
        'Invalid phone or password',
        null, 401
    );
}

// Generate new token
$token = generateToken($user['id'], $user['role']);

// Update last login and token
mysqli_query($conn,
    "UPDATE users SET 
     jwt_token='$token',
     last_login=NOW()
     WHERE id={$user['id']}"
);

sendResponse(true, 'Login successful', [
    'user_id'   => $user['id'],
    'full_name' => $user['full_name'],
    'phone'     => $user['phone'],
    'role'      => $user['role'],
    'county'    => $user['county'],
    'token'     => $token
]);
?>