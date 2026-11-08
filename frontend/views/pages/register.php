<?php
/**
 * FRONT-END VIEW — Guest registration — validated on the client (JS) and the server (PHP).
 * Only HTML output: all data comes from backend/controllers/register.php
 */
require __DIR__ . '/../layout/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-1">Create a guest account</h1>
                    <p class="text-muted mb-4">Register to request bookings and manage your stays.</p>

                    <form method="post" class="js-validate js-register-form" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="name">Full name</label>
                            <input type="text" class="form-control<?= field_state($errors, 'name') ?>" id="name" name="name"
                                   value="<?= e($form['name']) ?>" required minlength="3" maxlength="100" pattern="[A-Za-zÀ-ÿ .'\-]+" autocomplete="name">
                            <div class="invalid-feedback"><?= e($errors['name'] ?? 'Enter your full name (letters only).') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control<?= field_state($errors, 'email') ?>" id="email" name="email"
                                   value="<?= e($form['email']) ?>" required maxlength="150" autocomplete="email">
                            <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a valid email address.') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contact_number">Contact number</label>
                            <input type="tel" class="form-control<?= field_state($errors, 'contact_number') ?>" id="contact_number" name="contact_number"
                                   value="<?= e($form['contact_number']) ?>" required pattern="(\+94|0)[0-9]{9}" placeholder="0771234567" autocomplete="tel">
                            <div class="invalid-feedback"><?= e($errors['contact_number'] ?? 'Use 0771234567 or +94771234567.') ?></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group has-validation">
                                    <input type="password" class="form-control<?= field_state($errors, 'password') ?>" id="password" name="password"
                                           required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password">
                                    <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                                    <div class="invalid-feedback"><?= e($errors['password'] ?? 'At least 8 characters with a letter and a number.') ?></div>
                                </div>
                                <div class="password-meter mt-2" aria-hidden="true"><span class="js-strength-bar"></span></div>
                                <div class="form-text js-strength-text">Use 8+ characters with letters and numbers.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="confirm_password">Confirm password</label>
                                <input type="password" class="form-control<?= field_state($errors, 'confirm_password') ?>" id="confirm_password" name="confirm_password"
                                       required autocomplete="new-password" data-match="password">
                                <div class="invalid-feedback"><?= e($errors['confirm_password'] ?? 'Passwords do not match.') ?></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-ocean w-100 btn-lg">Create account</button>
                    </form>
                    <p class="text-center small mt-4 mb-0">Already registered? <a href="<?= url('login.php') ?>">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
