<?php $title = 'Playlists'; ?>

<div style="max-width:800px;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <h2 style="font-size:1.1rem;font-weight:600;">Your Playlists</h2>
    </div>

    <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;">
        <p style="font-weight:600;font-size:0.9rem;margin-bottom:1rem;">New Playlist</p>
        <form method="POST" action="/playlist/create" style="display:flex;flex-direction:column;gap:10px;">
            <?= csrfField() ?>
            <input type="text" name="title" class="settings-input" placeholder="Playlist name" required>
            <textarea name="description" class="settings-input"
                      style="height:60px;padding:8px 12px;resize:none;"
                      placeholder="Description (optional)"></textarea>
            <div style="display:flex;align-items:center;gap:10px;">
                <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;cursor:pointer;">
                    <input type="checkbox" name="isPublic" checked> Public
                </label>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>

    <?php if (empty($playlists)): ?>
        <div class="alert alert-info">No playlists yet.</div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($playlists as $pl): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;
                            padding:12px 16px;background:var(--surface);
                            border:1.5px solid var(--border);border-radius:var(--radius);">
                    <div>
                        <a href="/playlist?id=<?= $pl['id'] ?>"
                           style="font-weight:600;text-decoration:none;color:var(--text);">
                            <?= htmlspecialchars($pl['title']) ?>
                        </a>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;">
                            <?= $pl['videoCount'] ?> videos •
                            <?= $pl['isPublic'] ? 'Public' : 'Private' ?>
                        </p>
                    </div>
                    <form method="POST" action="/playlist/delete"
                          onsubmit="return confirm('Delete playlist?')" style="margin:0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="playlistId" value="<?= $pl['id'] ?>">
                        <button type="submit" class="btn btn-danger"
                                style="height:30px;font-size:0.8rem;">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>