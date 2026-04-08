<?php $title = 'Reset Password'; ?>
<div class="auth-container">
    <h1 style="color:var(--primary);">New Password</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($token): ?>
        <form method="POST" action="/reset" class="auth-form">
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label>New password</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Set new password</button>
        </form>
    <?php endif; ?>
</div>