<?php $title = 'Forgot Password'; ?>
<div class="auth-container">
    <h1 style="color:var(--primary);">Forgot Password</h1>
    <form method="POST" action="/forgot" class="auth-form">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Send reset link</button>
        <p><a href="/login">Back to login</a></p>
    </form>
</div>