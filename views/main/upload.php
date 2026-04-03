<?php $title = 'Upload Video'; ?>

<div class="upload-container">
    <h1>Upload a Video</h1>
    
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/upload" enctype="multipart/form-data" class="upload-form">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="video">Video File:</label>
            <input type="file" id="video" name="video" accept="video/*" required>
        </div>
        
        <div class="form-group">
            <label for="thumbnail">Thumbnail:</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required>
        </div>
        
        <input type="hidden" id="duration" name="duration" value="0">
        <button type="submit" class="btn btn-primary">Upload Video</button>
        <a href="/" class="btn">Cancel</a>

        <script>
        document.getElementById('video').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const vid = document.createElement('video');
            vid.preload = 'metadata';

            vid.onloadedmetadata = function() {
                URL.revokeObjectURL(vid.src);
                document.getElementById('duration').value = Math.round(vid.duration);
            };

            vid.src = URL.createObjectURL(file);
        });
        </script>
    </form>
</div>
