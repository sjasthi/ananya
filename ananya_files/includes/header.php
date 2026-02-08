<?php
// Ananya Header Component - Include this on all pages for consistent branding
?>

<!-- Google Fonts for consistent typography -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Telugu:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Ananya Header Styles -->
<link rel="stylesheet" href="<?php echo (isset($css_path) ? $css_path : 'css/') ?>ananya-header.css">

<!-- Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-ananya">
    <div class="container">
        <a class="navbar-brand navbar-brand-custom" href="<?php echo (isset($root_path) ? $root_path : '') ?>index.php">
            <img src="<?php echo (isset($root_path) ? $root_path : '') ?>images/logo.png" 
                 alt="Ananya Logo" class="logo-image">
            <div class="brand-text">
                <strong class="brand-name">Ananya</strong>
                <small class="tagline">Where Indic Languages Meet Technology</small>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'chat.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>chat.php">
                        <i class="fas fa-comments"></i> Chat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'api.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>docs/api.php">
                        <i class="fas fa-book"></i> Documentation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'swagger.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>docs/swagger.php">
                        <i class="fas fa-code"></i> Interactive Docs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'analyzer.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>analyzer.php">
                        <i class="fas fa-chart-line"></i> Analyzer
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'finder.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>finder.php">
                        <i class="fas fa-search"></i> Finder
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'play_with_telugu.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>play_with_telugu.php">
                        <i class="fas fa-gamepad"></i> Play with Telugu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'api_testing.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>api_testing.php">
                        <i class="fas fa-flask"></i> API Testing
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>" href="<?php echo (isset($root_path) ? $root_path : '') ?>about.php">
                        <i class="fas fa-user"></i> About
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>