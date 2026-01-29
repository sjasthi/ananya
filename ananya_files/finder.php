<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finder - Ananya</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.css">

    <!-- Include Ananya Header Component -->
    <?php include 'includes/header.php'; ?>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
</head>

<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row page-header">
                <div class="col-12">
                    <h2 class="text-center mb-4">Word Finder Tool</h2>
                    <p class="text-center text-muted">Find and analyze words with advanced search capabilities</p>
                </div>
            </div>
            <div class="col">
                <p class="h4 text-center">Please provide your search inputs below:</p>
            <div id="input-header" class="container my-3 d-flex justify-content-center">
                <div class="col-auto mx-3">
                    <label for="language-select">Language:</label>
                    <select id="language-select" class="form-select mt-3">
                        <option value="english">English</option>
                        <option value="telugu" selected>Telugu</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="input-text">Input to search:</label>
                    <input type="text" name="input-text" id="input-text" class="form-control mt-3" value="కండలు">
                </div>
            </div>
            <div id="input-container" class="container my-3">
                <textarea id="text-to-compare" class="form-control" rows="10">"షీరోస్" పుస్తకం భారతదేశంలోని 256 మంది ప్రేరణాత్మక మహిళల జీవితాలను స్ఫూర్తిదాయకంగా పరిచయం చేస్తుంది. 

విజ్ఞానం, కళలు, రాజకీయాలు, క్రీడలు, విద్య, సామాజిక సేవ వంటి విభిన్న రంగాలలో అద్భుతమైన కీర్తి సాధించిన మహిళల కథలు ఈ పుస్తకంలో సమగ్రంగా ఉన్నాయి.

"షీరోస్" పుస్తకం కేవలం జీవితచరిత్రల సమాహారం మాత్రమే కాదు — అది ఒక చైతన్య ఉద్యమం, మహిళా సాధికారతకు అంకితమైన సాహిత్య రూపం. ప్రతి అమ్మాయి ఒక షీరో కావచ్చు, ప్రతి పాఠకుడు మార్పుకు మార్గదర్శకుడు కావచ్చు అనే సందేశాన్ని ఇది అందిస్తుంది.</textarea>
            </div>
            <div id="buttons" class="container mb-4 d-flex justify-content-center">
                <button id="process" class="btn btn-outline-primary w-25">Process</button>
            </div>
            <div id="status" class="container mb-3 text-center">
                <small class="text-muted">Ready to search. Example: "కండలు" should match "క్రీడలు" (same base characters)</small>
            </div>
            <p class="h6 text-center">Matches Found:</p>
            <div id="table-container" class="container">
                <textarea id="finder-matches" class="form-control" rows="10" disabled></textarea>
            </div>
        </div>
    </div>
        </div>
    </div>
    
    <script src="js/finder.js"></script>
</body>
</html>