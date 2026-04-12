<?php $title = 'Edit Video'; ?>

<div style="max-width:600px;margin:2rem auto;">
    <h2 style="font-size:1.1rem;font-weight:600;margin-bottom:1.5rem;">Edit Video</h2>

    <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius);padding:1.5rem;">
        <form method="POST" action="/video/edit">
            <?= csrfField() ?>
            <input type="hidden" name="videoId" value="<?= (int)$video['id'] ?>">

            <div style="margin-bottom:1.25rem;">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="settings-input"
                       value="<?= htmlspecialchars($video['title']) ?>" required>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label class="form-label">Description</label>
                <textarea name="description" class="settings-input"
                          style="height:120px;padding:8px 12px;resize:vertical;"
                ><?= htmlspecialchars($video['description']) ?></textarea>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label class="form-label">Tags (comma separated)</label>
                <input type="text" name="tags" class="settings-input"
                       value="<?= htmlspecialchars($tags) ?>">
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="/watch?id=<?= (int)$video['id'] ?>" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>