<?php
/**
 * BACK-END CONTROLLER
 *
 * Guest registration — validated on the client (JS) and the server (PHP).
 * Passwords are stored with password_hash().
 */
require_once __DIR__ . '/../core/init.php';

if (is_logged_in()) redirect('index.php');

$pageTitle  = 'Register';
$activePage = 'register';
$form   = ['name' => '', 'email' => '', 'contact_number' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form = [
        'name'           => trim($_POST['name'] ?? ''),
        'email'          => strtolower(trim($_POST['email'] ?? '')),
        'contact_number' => str_replace(' ', '', trim($_POST['contact_number'] ?? '')),
    ];
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (mb_strlen($form['name']) < 3 || mb_strlen($form['name']) > 100 || !preg_match("/^[\p{L} .'-]+$/u", $form['name'])) {
        $errors['name'] = 'Enter your full name (letters only, 3–100 characters).';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || strlen($form['email']) > 150) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (!preg_match('/^(?:\+94|0)\d{9}$/', $form['contact_number'])) {
        $errors['contact_number'] = 'Use a Sri Lankan number such as 0771234567 or +94771234567.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must be at least 8 characters and include a letter and a number.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!isset($errors['email'])) {
        $chk = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $chk->execute([$form['email']]);
        if ($chk->fetch()) $errors['email'] = 'An account with this email already exists. Please log in.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, contact_number, password, role) VALUES (?, ?, ?, ?, 'guest')");
        $stmt->execute([$form['name'], $form['email'], $form['contact_number'], password_hash($password, PASSWORD_DEFAULT)]);

        login_user([
            'user_id' => $pdo->lastInsertId(), 'name' => $form['name'],
            'email'   => $form['email'],       'role' => 'guest',
        ]);
        flash('success', 'Welcome, ' . $form['name'] . '! Your account has been created.');
        $next = $_SESSION['redirect_after_login'] ?? '';
        unset($_SESSION['redirect_after_login']);
        if ($next && str_starts_with($next, BASE_URL . '/')) {   // only local redirects
            header('Location: ' . $next);
            exit;
        }
        redirect('search.php');
    }
}


// Data prepared above is rendered by frontend/views/pages/register.php
