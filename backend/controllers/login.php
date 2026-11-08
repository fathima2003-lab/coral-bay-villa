<?php
/**
 * BACK-END CONTROLLER
 *
 * Login for guests and administrators (password_verify + PHP sessions).
 */
require_once __DIR__ . '/../core/init.php';

if (is_logged_in()) redirect(is_admin() ? 'admin/index.php' : 'my_bookings.php');

$pageTitle  = 'Log in';
$activePage = 'login';
$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Same message for "no such user" and "wrong password" (doesn't reveal which emails exist)
        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Incorrect email or password.';
        } else {
            // Upgrade the hash automatically if PHP's default algorithm changes
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?')
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $user['user_id']]);
            }
            login_user($user);
            flash('success', 'Welcome back, ' . $user['name'] . '!');

            if ($user['role'] === 'admin') redirect('admin/index.php');

            $next = $_SESSION['redirect_after_login'] ?? '';
            unset($_SESSION['redirect_after_login']);
            if ($next && str_starts_with($next, BASE_URL . '/') && !str_contains($next, '/admin/')) {
                header('Location: ' . $next);
                exit;
            }
            redirect('my_bookings.php');
        }
    }
}

// Data prepared above is rendered by frontend/views/pages/login.php
