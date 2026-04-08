<?php $title = 'Settings'; ?>

<div style="max-width:560px;margin:2rem auto;">

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Saved!</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <!-- PROFILE -->
    <form method="POST" action="/settings" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div style="background:white;border:1.5px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1rem;">
            <p style="font-weight:600;font-size:0.95rem;margin-bottom:1.25rem;color:var(--text);">Profile</p>

            <!-- Avatar -->
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:1.25rem;">
                <?= renderAvatar($user['avatar'] ?? null, '56px') ?>
                <div style="flex:1;">
                    <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Avatar</label>
                    <input type="file" name="avatar" accept="image/*"
                           style="font-size:0.85rem;color:var(--text-muted);">
                </div>
            </div>

            <!-- Banner -->
            <div style="margin-bottom:1.25rem;">
                <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Banner</label>
                <?php if ($user['banner'] ?? null): ?>
                    <div style="width:100%;height:80px;border-radius:var(--radius);overflow:hidden;margin-bottom:8px;border:1.5px solid var(--border);">
                        <img src="<?= htmlspecialchars($user['banner']) ?>"
                             style="width:100%;height:100%;object-fit:cover;">
                    </div>
                <?php endif; ?>
                <input type="file" name="banner" accept="image/*"
                       style="font-size:0.85rem;color:var(--text-muted);">
            </div>

            <!-- Display name -->
            <div style="margin-bottom:1.25rem;">
                <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Display name</label>
                <input type="text" name="displayName" class="settings-input"
                       value="<?= htmlspecialchars($user['displayName']) ?>" required>
            </div>

            <!-- Bio -->
            <div style="margin-bottom:1.25rem;">
                <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Bio</label>
                <textarea name="bio" class="settings-input"
                          style="height:100px;padding:8px 12px;resize:vertical;"
                ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Save changes</button>
        </div>
    </form>

    <!-- DELETE -->
    <div style="background:white;border:1.5px solid var(--border);border-radius:var(--radius);padding:1.5rem;">
        <p style="font-weight:600;font-size:0.95rem;margin-bottom:4px;color:#c0392b;">Delete account</p>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:1.25rem;">
            This action is permanent and cannot be undone.
        </p>
        <form method="POST" action="/account/delete">
            <?= csrfField() ?>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.85rem;font-weight:600;display:block;margin-bottom:4px;">Confirm password</label>
                <input type="password" name="password" class="settings-input" required>
            </div>
            <button type="submit" class="btn btn-danger" style="width:100%;"
                    onclick="return confirm('Are you sure? This cannot be undone.')">
                Delete my account
            </button>
        </form>
    </div>

</div>