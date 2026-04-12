<?php require_once __DIR__ . '/../../helpers/formatNumber.php'; ?>

<div class="studio-wrap">

  <!-- Header -->
  <div class="studio-header">
    <div>
      <h1 class="studio-title">Studio</h1>
      <p class="studio-sub">Welcome back, <?= htmlspecialchars($_SESSION['user']['displayName']) ?></p>
    </div>
    <a href="/upload" class="btn btn-primary studio-upload-btn">+ Upload video</a>
  </div>

  <!-- Stats row -->
  <div class="studio-stats">
    <div class="studio-stat-card">
      <div class="studio-stat-label">Total views</div>
      <div class="studio-stat-num"><?= formatNumber($totalViews) ?></div>
    </div>
    <div class="studio-stat-card">
      <div class="studio-stat-label">Subscribers</div>
      <div class="studio-stat-num"><?= formatNumber($subCount) ?></div>
    </div>
    <div class="studio-stat-card">
      <div class="studio-stat-label">Total likes</div>
      <div class="studio-stat-num"><?= formatNumber($totalLikes) ?></div>
    </div>
    <div class="studio-stat-card">
      <div class="studio-stat-label">Comments</div>
      <div class="studio-stat-num"><?= formatNumber($totalComments) ?></div>
    </div>
    <div class="studio-stat-card">
      <div class="studio-stat-label">Videos</div>
      <div class="studio-stat-num"><?= count($videos) ?></div>
    </div>
  </div>

  <!-- Video table -->
  <div class="studio-section">
    <div class="studio-section-title">Your videos</div>

    <?php if (empty($videos)): ?>
      <div class="studio-empty">
        <p>No videos yet.</p>
        <a href="/upload" class="btn btn-primary">Upload your first video</a>
      </div>
    <?php else: ?>

    <div class="studio-table">
      <div class="studio-table-header">
        <div class="stcol stcol-thumb"></div>
        <div class="stcol stcol-info">Video</div>
        <div class="stcol stcol-stat">Views</div>
        <div class="stcol stcol-stat">Likes</div>
        <div class="stcol stcol-stat">Comments</div>
        <div class="stcol stcol-date">Date</div>
        <div class="stcol stcol-actions">Actions</div>
      </div>

      <?php foreach ($videos as $v): ?>
      <div class="studio-row" id="row-<?= $v['id'] ?>">

        <!-- Thumbnail -->
        <div class="stcol stcol-thumb">
          <a href="/watch?id=<?= $v['id'] ?>">
            <img src="<?= htmlspecialchars($v['thumbnail']) ?>" alt="" class="studio-thumb">
          </a>
        </div>

        <!-- Info (view mode) -->
        <div class="stcol stcol-info" id="view-<?= $v['id'] ?>">
          <a href="/watch?id=<?= $v['id'] ?>" class="studio-video-title">
            <?= htmlspecialchars($v['title']) ?>
          </a>
          <?php if ($v['tags']): ?>
            <div class="studio-tags"><?= htmlspecialchars($v['tags']) ?></div>
          <?php endif; ?>
          <div class="studio-desc-preview"><?= htmlspecialchars(mb_substr($v['description'], 0, 80)) ?>...</div>
        </div>

        <!-- Stats -->
        <div class="stcol stcol-stat"><?= formatNumber($v['views']) ?></div>
        <div class="stcol stcol-stat"><?= formatNumber($v['likeCount']) ?></div>
        <div class="stcol stcol-stat"><?= formatNumber($v['commentCount']) ?></div>
        <div class="stcol stcol-date"><?= date('M j, Y', strtotime($v['createdAt'])) ?></div>

        <!-- Actions -->
        <div class="stcol stcol-actions">
          <button class="studio-btn-edit" onclick="openEdit(<?= $v['id'] ?>)">Edit</button>
          <a href="/watch?id=<?= $v['id'] ?>" class="studio-btn-view">View</a>
          <form method="POST" action="/video/delete" onsubmit="return confirm('Delete?')" style="display:inline">
            <?= csrfField() ?>
            <input type="hidden" name="videoId" value="<?= $v['id'] ?>">
            <button type="submit" class="studio-btn-delete">Delete</button>
          </form>
        </div>

      </div>

      <!-- Inline edit form (hidden by default) -->
      <div class="studio-edit-panel" id="edit-<?= $v['id'] ?>" style="display:none">
        <form method="POST" action="/studio/quick-edit">
          <?= csrfField() ?>
          <input type="hidden" name="videoId" value="<?= $v['id'] ?>">
          <div class="studio-edit-grid">
            <div class="studio-edit-field">
              <label>Title</label>
              <input type="text" name="title" value="<?= htmlspecialchars($v['title']) ?>" required>
            </div>
            <div class="studio-edit-field">
              <label>Tags</label>
              <input type="text" name="tags" value="<?= htmlspecialchars($v['tags']) ?>" placeholder="tag1, tag2">
            </div>
            <div class="studio-edit-field studio-edit-desc">
              <label>Description</label>
              <textarea name="description" rows="3"><?= htmlspecialchars($v['description']) ?></textarea>
            </div>
          </div>
          <div class="studio-edit-actions">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" onclick="closeEdit(<?= $v['id'] ?>)" class="btn">Cancel</button>
          </div>
        </form>
      </div>

      <?php endforeach; ?>
    </div>

    <?php endif; ?>
  </div>

</div>

<style>
.studio-wrap { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem; }

.studio-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; }
.studio-title  { font-size: 1.6rem; font-weight: 700; color: var(--text); }
.studio-sub    { font-size: 0.85rem; color: var(--text-muted); margin-top: 2px; }

.studio-stats {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1px;
  background: var(--border);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  margin-bottom: 2rem;
}
.studio-stat-card { background: var(--surface); padding: 1.2rem 1.5rem; transition: background 0.15s; }
.studio-stat-card:hover { background: var(--surface-hover); }
.studio-stat-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
.studio-stat-num   { font-size: 1.5rem; font-weight: 700; color: var(--text); }

.studio-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.studio-section-title { padding: 1rem 1.25rem; font-size: 0.9rem; font-weight: 600; color: var(--text); border-bottom: 1px solid var(--border); }

.studio-empty { padding: 3rem; text-align: center; color: var(--text-muted); display: flex; flex-direction: column; align-items: center; gap: 1rem; }

.studio-table { width: 100%; }
.studio-table-header,
.studio-row {
  display: grid;
  grid-template-columns: 100px 1fr 80px 80px 90px 100px 150px;
  align-items: center;
  gap: 0;
}
.studio-table-header {
  background: var(--bg);
  border-bottom: 1px solid var(--border);
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.studio-row {
  border-bottom: 1px solid var(--border);
  transition: background 0.12s;
}
.studio-row:last-of-type { border-bottom: none; }
.studio-row:hover { background: var(--bg); }

.stcol { padding: 0.85rem 1rem; }
.stcol-stat, .stcol-date { font-size: 0.82rem; color: var(--text-muted); }
.stcol-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }

.studio-thumb { width: 80px; height: 46px; object-fit: cover; border-radius: 4px; display: block; }

.studio-video-title { font-size: 0.88rem; font-weight: 600; color: var(--text); text-decoration: none; display: block; margin-bottom: 3px; }
.studio-video-title:hover { color: var(--accent); }
.studio-tags { font-size: 0.75rem; color: var(--accent); margin-bottom: 2px; }
.studio-desc-preview { font-size: 0.75rem; color: var(--text-muted); }

.studio-btn-edit,
.studio-btn-view,
.studio-btn-delete {
  font-size: 0.75rem;
  padding: 4px 10px;
  border-radius: 4px;
  border: 1px solid var(--border);
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: background 0.12s, border-color 0.12s;
  background: transparent;
  color: var(--text);
  font-family: inherit;
}
.studio-btn-edit:hover  { background: var(--surface-hover); }
.studio-btn-view:hover  { background: var(--surface-hover); }
.studio-btn-delete { color: #f87171; border-color: #f8717144; }
.studio-btn-delete:hover { background: rgba(248,113,113,0.08); }

/* Inline edit panel */
.studio-edit-panel {
  background: var(--bg);
  border-bottom: 1px solid var(--border);
  padding: 1.25rem 1.25rem 1.25rem calc(100px + 1rem);
}
.studio-edit-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 12px;
}
.studio-edit-desc { grid-column: 1 / -1; }
.studio-edit-field label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
.studio-edit-field input,
.studio-edit-field textarea {
  width: 100%;
  padding: 7px 10px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  color: var(--text);
  font-size: 0.85rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.15s;
}
.studio-edit-field input:focus,
.studio-edit-field textarea:focus { border-color: var(--accent); }
.studio-edit-actions { display: flex; gap: 8px; }

@media (max-width: 900px) {
  .studio-stats { grid-template-columns: repeat(3, 1fr); }
  .studio-table-header { display: none; }
  .studio-row {
    grid-template-columns: 80px 1fr;
    grid-template-rows: auto auto auto;
  }
  .stcol-stat, .stcol-date { display: none; }
  .stcol-actions { grid-column: 2; padding-top: 0; }
  .studio-edit-panel { padding: 1rem; }
  .studio-edit-grid { grid-template-columns: 1fr; }
}
</style>

<script>
function openEdit(id) {
  document.querySelectorAll('.studio-edit-panel').forEach(p => p.style.display = 'none');
  document.getElementById('edit-' + id).style.display = 'block';
  document.getElementById('edit-' + id).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
function closeEdit(id) {
  document.getElementById('edit-' + id).style.display = 'none';
}
</script>