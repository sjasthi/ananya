<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header Test - Ananya</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #f8f9fa;
        }
        .test-content {
            padding: 2rem;
            margin: 2rem 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php 
    $root_path = '../';
    $css_path = '../css/';
    include '../includes/header.php'; 
    ?>
    
    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="test-content">
                        <h1>Header Test Page</h1>
                        <p class="lead">This page is for testing if the header is loading correctly.</p>
                        
                        <h3>Debug Information:</h3>
                        <ul>
                            <li><strong>Root Path:</strong> <?php echo isset($root_path) ? $root_path : 'Not set'; ?></li>
                            <li><strong>CSS Path:</strong> <?php echo isset($css_path) ? $css_path : 'Not set'; ?></li>
                            <li><strong>Current File:</strong> <?php echo basename($_SERVER['PHP_SELF']); ?></li>
                            <li><strong>Header File Path:</strong> <?php echo realpath('../includes/header.php'); ?></li>
                            <li><strong>CSS File Path:</strong> <?php echo realpath('../css/ananya-header.css'); ?></li>
                        </ul>
                        
                        <h3>Expected Header Elements:</h3>
                        <p>You should see:</p>
                        <ul>
                            <li>A dark navbar at the top with "Ananya API" branding</li>
                            <li>Navigation links including Documentation, Interactive Docs, etc.</li>
                            <li>The navbar should be fixed at the top</li>
                            <li>This content should be properly spaced below the navbar</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>