<?php $title = 'Login'; ?>

<div class="auth-container">
    <h1>Login</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Registration successful! Please log in.</div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/login" class="auth-form">
        <?= csrfField() ?>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
        <p>Don't have an account? <a href="/register">Register here</a></p>
    </form>
</div>