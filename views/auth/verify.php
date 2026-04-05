<?php $title = 'Email Verification'; ?>

<div class="auth-container">
    <h1 style="color:var(--primary);">Email Verification</h1>
    <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <p><a href="/login">Go to login</a></p>
</div>