<?php
function sendResponse($success, $message, 
                      $data = null, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

function clean($conn, $data) {
    return mysqli_real_escape_string(
        $conn, trim(strip_tags($data))
    );
}

function generateToken($user_id, $role) {
    $header = base64_encode(json_encode([
        'alg' => 'HS256','typ' => 'JWT'
    ]));
    $payload = base64_encode(json_encode([
        'user_id'   => $user_id,
        'role'      => $role,
        'issued_at' => time(),
        'expires'   => time() + JWT_EXPIRY
    ]));
    $signature = hash_hmac(
        'sha256',"$header.$payload",JWT_SECRET
    );
    return "$header.$payload.$signature";
}

function verifyToken($token) {
    $parts = explode('.', $token);
    if(count($parts) !== 3) return false;
    list($header, $payload, $signature) = $parts;
    $valid = hash_hmac(
        'sha256',"$header.$payload",JWT_SECRET
    );
    if($signature !== $valid) return false;
    $data = json_decode(
        base64_decode($payload), true
    );
    if($data['expires'] < time()) return false;
    return $data;
}

function requireAuth() {
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? '';
    $token   = null;
    if(strpos($auth, 'Bearer ') === 0) {
        $token = substr($auth, 7);
    }
    if(!$token) {
        sendResponse(
            false,'No token provided',null,401
        );
    }
    $user = verifyToken($token);
    if(!$user) {
        sendResponse(
            false,'Invalid or expired token',
            null,401
        );
    }
    return $user;
}

function generateDealRef() {
    return 'SOKO-'.date('Y').'-'.
           strtoupper(substr(md5(uniqid()),0,6));
}
?>