<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit();
}

$code = $_GET['code'];

// Exchange code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$postData = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    header('Location: login.php?error=google_auth_failed');
    exit();
}

// Get user info
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userInfo = curl_exec($ch);
curl_close($ch);

$user = json_decode($userInfo, true);

if (!$user || !isset($user['email'])) {
    header('Location: login.php?error=google_userinfo_failed');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Check if user exists
$query = "SELECT * FROM users WHERE email = :email OR google_id = :google_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $user['email']);
$stmt->bindParam(':google_id', $user['id']);
$stmt->execute();

if ($existingUser = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Update google_id if not set
    if (empty($existingUser['google_id'])) {
        $update = "UPDATE users SET google_id = :google_id WHERE id = :id";
        $stmt = $db->prepare($update);
        $stmt->bindParam(':google_id', $user['id']);
        $stmt->bindParam(':id', $existingUser['id']);
        $stmt->execute();
    }
    
    $_SESSION['user_id'] = $existingUser['id'];
    $_SESSION['username'] = $existingUser['username'];
    $_SESSION['user_role'] = $existingUser['role'];
    $_SESSION['user_email'] = $existingUser['email'];
} else {
    // Create new user
    $username = explode('@', $user['email'])[0];
    $baseUsername = $username;
    $counter = 1;
    
    while (true) {
        $check = "SELECT id FROM users WHERE username = :username";
        $stmt = $db->prepare($check);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if (!$stmt->fetch()) break;
        $username = $baseUsername . $counter;
        $counter++;
    }
    
    $query = "INSERT INTO users (username, email, google_id, email_verified, is_active, role) 
              VALUES (:username, :email, :google_id, 1, 1, 'user')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $user['email']);
    $stmt->bindParam(':google_id', $user['id']);
    $stmt->execute();
    
    $userId = $db->lastInsertId();
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_email'] = $user['email'];
}

// Update last login
$update = "UPDATE users SET last_login = NOW() WHERE id = :id";
$stmt = $db->prepare($update);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();

header('Location: /user/dashboard.php');
exit();
?>