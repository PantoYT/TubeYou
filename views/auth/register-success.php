<?php $title = 'Check your email'; ?>
<?php if (!empty($mailWarn)): ?>
    <p style="color:orange"><?= htmlspecialchars($mailWarn) ?></p>
<?php endif; ?>

<div class="auth-container">
    <h1 style="color:var(--primary);">Check your email</h1>
    <div class="alert alert-success">
        We sent a verification link to <strong><?= htmlspecialchars($email) ?></strong>.
        Click it to activate your account.
    </div>
    <p><a href="/login">Back to login</a></p>
</div>