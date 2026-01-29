<?php
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Text Analyzer - Ananya</title>

        <!-- CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/searchpanes/2.2.0/css/searchPanes.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">
        
        <style>
            .parser-container {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .input-section {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .results-section {
                background: white;
                border-radius: 10px;
                padding: 2rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            #parsing-input {
                font-family: 'Noto Sans Telugu', sans-serif;
                font-size: 1.1rem;
                line-height: 1.6;
            }
            
            .process-btn {
                background: linear-gradient(45deg, #2563eb, #7c3aed);
                border: none;
                color: white;
                padding: 12px 40px;
                border-radius: 25px;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            .process-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
                color: white;
            }
            
            .process-btn:disabled {
                opacity: 0.7;
                transform: none;
            }
            
            .dataTables_wrapper {
                font-family: 'Inter', sans-serif;
            }
            
            .numerical-results-section {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 15px;
                padding: 2rem;
                margin: 2rem 0;
                color: white;
            }
            
            .numerical-results-section h5 {
                color: white;
                text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            
            .stat-card {
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 12px;
                padding: 1.5rem;
                text-align: center;
                transition: all 0.3s ease;
                height: 100%;
            }
            
            .stat-card:hover {
                transform: translateY(-5px);
                background: rgba(255, 255, 255, 0.25);
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            }
            
            .stat-label {
                font-size: 0.9rem;
                font-weight: 500;
                margin-bottom: 0.5rem;
                opacity: 0.9;
            }
            
            .stat-value {
                font-size: 2rem;
                font-weight: 700;
                color: #ffffff;
                text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            
            /* DataTable styling enhancements */
            .dataTables_wrapper {
                font-family: 'Inter', sans-serif;
            }
            
            .dataTables_filter {
                margin-bottom: 1rem;
            }
            
            .dataTables_filter input {
                border-radius: 8px;
                border: 2px solid #e5e7eb;
                padding: 0.5rem 1rem;
                margin-left: 0.5rem;
            }
            
            .dataTables_filter input:focus {
                border-color: #2563eb;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            }
            
            .dt-buttons {
                margin: 1rem 0;
                text-align: center;
            }
            
            .dt-buttons .btn {
                margin: 0.25rem;
                font-size: 0.875rem;
                border-radius: 8px;
                transition: all 0.2s ease;
            }
            
            .dt-buttons .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            .dataTables_info {
                color: #6b7280;
                font-size: 0.875rem;
                margin-top: 1rem;
            }
            
            .dataTables_paginate {
                margin-top: 1rem;
            }
            
            .dataTables_paginate .paginate_button {
                border-radius: 6px;
                margin: 0 2px;
            }
            
            .table th {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-weight: 600;
                border: none;
            }
            
            .table tbody tr:hover {
                background-color: rgba(102, 126, 234, 0.1);
                transition: background-color 0.2s ease;
            }
        </style>
        
        <!-- Include Ananya Header Component -->
        <?php 
        $root_path = '';
        $css_path = 'css/';
        include 'includes/header.php'; 
        ?>
        
        <!-- JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

        <script src="js/analyzer.js?v=<?php echo time(); ?>"></script>
        
        <!-- Force browser to clear cache -->
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    </head>

    <body>
        <div class="main-content">
            <div class="parser-container">
                <div class="row page-header">
                    <div class="col-12">
                        <h2 class="text-center mb-4">Telugu Text Parser</h2>
                        <p class="text-center text-muted">Parse and analyze text with advanced linguistic processing and word frequency analysis</p>
                    </div>
                </div>
                
                <div class="input-section">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-language me-2"></i>Language Selection</h5>
                            <select id="language-select" class="form-select">
                                <option value="english">English</option>
                                <option value="telugu" selected>Telugu (తెలుగు)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                                                        <button id="process-btn" class="btn btn-primary process-btn" onclick="console.log('Button clicked!'); updateParseTable();">
                                <i class="fas fa-cogs me-2"></i>Process Text
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5 class="mb-3"><i class="fas fa-edit me-2"></i>Text Input</h5>
                        <textarea id="parsing-input" class="form-control" rows="8" 
                                  placeholder="మీ తెలుగు వచనాన్ని ఇక్కడ విశ్లేషణ కోసం నమోదు చేయండి...">"షీరోస్" పుస్తకం భారతదేశంలోని 256 మంది ప్రేరణాత్మక మహిళల జీవితాలను స్ఫూర్తిదాయకంగా పరిచయం చేస్తుంది. 

విజ్ఞానం, కళలు, రాజకీయాలు, క్రీడలు, విద్య, సామాజిక సేవ వంటి విభిన్న రంగాలలో అద్భుతమైన కీర్తి సాధించిన మహిళల కథలు ఈ పుస్తకంలో సమగ్రంగా ఉన్నాయి.


"షీరోస్" పుస్తకం కేవలం జీవితచరిత్రల సమాహారం మాత్రమే కాదు — అది ఒక చైతన్య ఉద్యమం, మహిళా సాధికారతకు అంకితమైన సాహిత్య రూపం. ప్రతి అమ్మాయి ఒక షీరో కావచ్చు, ప్రతి పాఠకుడు మార్పుకు మార్గదర్శకుడు కావచ్చు అనే సందేశాన్ని ఇది అందిస్తుంది.</textarea>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            The parser supports both English and Telugu text. Words will be analyzed for length and frequency.
                        </small>
                    </div>
                </div>
                
                <!-- Numerical Results Section -->
                <div class="numerical-results-section">
                    <h5 class="mb-4"><i class="fas fa-calculator me-2"></i>Numerical Results</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Words</div>
                                <div class="stat-value" id="total-words">--</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Strength</div>
                                <div class="stat-value" id="total-strength">--</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Average Strength</div>
                                <div class="stat-value" id="average-strength">--</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Letters</div>
                                <div class="stat-value" id="total-letters">--</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Weight</div>
                                <div class="stat-value" id="total-weight">--</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Average Weight</div>
                                <div class="stat-value" id="average-weight">--</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Lines</div>
                                <div class="stat-value" id="total-lines">--</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="results-section">
                    <h5 class="mb-4"><i class="fas fa-table me-2"></i>Analysis Results</h5>
                    <div class="table-responsive">
                        <table id="parsed-table" class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-font me-1"></i>Word</th>
                                    <th><i class="fas fa-ruler me-1"></i>Length</th>
                                    <th><i class="fas fa-chart-bar me-1"></i>Frequency</th>
                                </tr>
                            </thead>
                            <tbody id="word-entries">
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        <i class="fas fa-arrow-up me-2"></i>Enter some text above and click "Process Text" to see results
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>