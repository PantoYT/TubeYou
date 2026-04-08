<?php $title = 'Settings'; ?>

<div class="auth-container" style="max-width:520px;">
    <h1 style="color:var(--primary);margin-bottom:1.5rem;">Settings</h1>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Saved!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/settings" enctype="multipart/form-data" class="auth-form">
        <?= csrfField() ?>
        
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:0.5rem;">
            <?= renderAvatar($user['avatar'] ?? null, '64px') ?>
            <div class="form-group" style="flex:1;margin:0;">
                <label for="avatar">Change avatar</label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
            </div>
        </div>

        <div class="form-group">
            <label for="displayName">Display name</label>
            <input type="text" id="displayName" name="displayName"
                   value="<?= htmlspecialchars($user['displayName']) ?>" required>
        </div>

        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4"
                      style="padding:.75rem;border:1.5px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:1rem;resize:vertical;"
            ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>

        <hr style="margin:2rem 0;border-color:var(--border);">
        <h3 style="color:#c0392b;margin-bottom:1rem;">Delete account</h3>
        <form method="POST" action="/account/delete" class="auth-form">
            <?= csrfField() ?>
            <div class="form-group">
                <label>Confirm password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Are you sure? This cannot be undone.')">
                Delete my account
            </button>
        </form>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>