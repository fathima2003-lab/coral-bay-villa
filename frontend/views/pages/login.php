<?php
/**
 * FRONT-END VIEW — Login for guests and administrators (password_verify + PHP sessions).
 * Only HTML output: all data comes from backend/controllers/login.php
 */
require __DIR__ . '/../layout/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-1">Welcome back</h1>
                    <p class="text-muted mb-4">Log in to book rooms and view your reservations.</p>

                    <?php if ($error): ?><div class="alert alert-danger py-2" role="alert"><?= e($error) ?></div><?php endif; ?>

                    <form method="post" class="js-validate" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus>
                            <div class="invalid-feedback">Enter your email address.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                                <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                                <div class="invalid-feedback">Enter your password.</div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-ocean w-100 btn-lg">Log in</button>
                    </form>
                    <p class="text-center small mt-4 mb-0">New here? <a href="<?= url('register.php') ?>">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
