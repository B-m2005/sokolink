<?php
require '../config/db.php';
require '../config/constants.php';
require '../config/helpers.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed',
                 null, 405);
}

// Get posted data
$data = json_decode(
    file_get_contents('php://input'), true
);

$full_name = clean($conn, $data['full_name'] ?? '');
$phone     = clean($conn, $data['phone'] ?? '');
$email     = clean($conn, $data['email'] ?? '');
$password  = $data['password'] ?? '';
$role      = clean($conn, $data['role'] ?? 'buyer');
$county    = clean($conn, $data['county'] ?? '');

// Validate required fields
if(!$full_name || !$phone || !$password) {
    sendResponse(
        false,
        'Name, phone and password are required',
        null, 400
    );
}

// Validate role
if(!in_array($role, ['buyer','seller'])) {
    $role = 'buyer';
}

// Check phone already exists
$check = mysqli_query($conn,
    "SELECT id FROM users 
     WHERE phone = '$phone'"
);
if(mysqli_num_rows($check) > 0) {
    sendResponse(
        false,
        'Phone number already registered',
        null, 409
    );
}

// Hash password
$hashed = password_hash($password, 
          PASSWORD_DEFAULT);

// Insert user
$sql = "INSERT INTO users 
        (full_name, phone, email, 
         password, role, county)
        VALUES 
        ('$full_name','$phone','$email',
         '$hashed','$role','$county')";

if(mysqli_query($conn, $sql)) {
    $user_id = mysqli_insert_id($conn);
    $token   = generateToken($user_id, $role);

    // Save token
    mysqli_query($conn,
        "UPDATE users SET jwt_token='$token'
         WHERE id=$user_id"
    );

    sendResponse(true, 'Registration successful', [
        'user_id'   => $user_id,
        'full_name' => $full_name,
        'phone'     => $phone,
        'role'      => $role,
        'county'    => $county,
        'token'     => $token
    ], 201);
} else {
    sendResponse(
        false,
        'Registration failed: '.
        mysqli_error($conn),
        null, 500
    );
}
?>