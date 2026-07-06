<?php
require_once __DIR__ . '/../config.php';

session_name(ADMIN_SESSION_NAME);
session_start();

// Already logged in? go straight to panel
if (!empty($_SESSION['rktv_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($password !== '' && password_verify($password, ADMIN_PASSWORD_HASH)) {
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['rktv_admin_logged_in'] = true;
        $_SESSION['rktv_admin_login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        $error = 'ভুল পাসওয়ার্ড। আবার চেষ্টা করুন।';
        // Small delay to slow down brute force attempts
        usleep(500000);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP TV — Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0a0a0f;
            color: #fff;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: #111118;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 36px 32px;
            width: 100%;
            max-width: 360px;
        }

        .login-box h1 {
            font-size: 20px;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .login-box .sub {
            color: #a0a0b8;
            font-size: 13px;
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-size: 13px;
            color: #a0a0b8;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            background: #18181f;
            color: #fff;
            font-size: 14px;
            outline: none;
            margin-bottom: 16px;
        }

        input[type="password"]:focus {
            border-color: rgba(255, 255, 255, 0.2);
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #e8003d;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
        }

        button:hover {
            opacity: .9;
        }

        .error {
            background: rgba(232, 0, 61, .12);
            border: 1px solid #e8003d;
            color: #ff6b8a;
            font-size: 13px;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>Admin Panel</h1>
        <div class="sub">চ্যানেল ম্যানেজ করতে লগইন করুন</div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autofocus required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>