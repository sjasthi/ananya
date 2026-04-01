<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Testing - Ananya</title>
    
    <!-- Bootstrap 5 for consistency -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <style>
        #testSuite .methodURL {
            color: #e2e8f0;
            text-decoration: none;
            font-weight: 600;
            cursor: default;
        }

        #testSuite .methodURL:hover,
        #testSuite .methodURL:focus {
            color: #e2e8f0;
            text-decoration: none;
        }

        .config-label {
            display: block;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.4rem;
            padding-left: 0.45rem;
        }

        #languageInput {
            width: 12ch;
            min-width: 10ch;
            max-width: 100%;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 2rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%232563eb' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.65rem center;
            background-size: 10px 6px;
        }

        #testSuite tr.section-header-row > th {
            background-color: #1f2937 !important;
            color: #f8fafc !important;
            border: 1px solid #374151 !important;
            font-size: 1.15rem;
            font-weight: 700;
            text-align: left;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
            padding-left: 1rem;
        }

        #testSuite thead#apiHeader th {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.2;
        }

        #testSuite tr.section-header-row.section-has-failures > th {
            background-color: #7f1d1d !important;
            border-color: #991b1b !important;
            box-shadow: inset 0 0 0 2px #dc2626;
        }

        #testSuite .section-toggle-btn {
            min-width: 2rem;
            line-height: 1;
            font-weight: 700;
            padding: 0.1rem 0.45rem;
            margin-left: -0.35rem;
        }

        .section-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.65rem;
            padding: 0.6rem 0.8rem;
            border: 1px solid #374151;
            border-radius: 0.375rem;
            background-color: #111827;
            color: #f8fafc;
        }

        .section-controls-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #e5e7eb;
        }

        .section-controls-actions {
            display: flex;
            gap: 0.5rem;
        }

        .section-controls .btn {
            font-weight: 600;
        }

        #tableDiv .table-responsive {
            overflow-x: auto;
        }

        #testSuite {
            min-width: 1400px;
        }

        /* Keep key columns visible while horizontally scrolling wide tables */
        #testSuite .methodHeader,
        #testSuite .methodCell {
            position: sticky;
            left: 0;
            z-index: 3;
            background-color: #212529 !important;
            box-shadow: 2px 0 0 #374151;
            min-width: 180px;
            max-width: 220px;
            white-space: normal;
            word-break: break-word;
            vertical-align: top;
        }

        #testSuite .input1Header,
        #testSuite .input2Header,
        #testSuite .inputCell,
        #testSuite .inputCountCell,
        #testSuite .input2Cell {
            min-width: 120px;
            max-width: 135px;
            white-space: normal;
            word-break: break-word;
            vertical-align: top;
        }

        #testSuite .input3Header,
        #testSuite .input3Cell {
            min-width: 92px;
            max-width: 110px;
            white-space: normal;
            word-break: break-word;
            vertical-align: top;
        }

        #testSuite .inputCell input,
        #testSuite .inputCountCell input,
        #testSuite .input2Cell input,
        #testSuite .input3Cell input {
            width: 100%;
            min-width: 0;
        }

        #testSuite .jsonHeader,
        #testSuite .jsonCell {
            position: sticky;
            right: 0;
            z-index: 3;
            background-color: #212529 !important;
            box-shadow: -2px 0 0 #374151;
            width: 480px;
            min-width: 480px;
            max-width: 480px;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
            vertical-align: top;
        }

        #testSuite .jsonHeader {
            z-index: 4;
        }

        #testSuite .expectedHeader,
        #testSuite .expectedCell,
        #testSuite .actualHeader,
        #testSuite .actualCell {
            min-width: 180px;
            max-width: 220px;
            white-space: normal;
            word-break: break-word;
            vertical-align: top;
        }

        /* Row expansion controls */
        .row-expand-btn {
            display: inline-block;
            min-width: 1.5rem;
            height: 1.5rem;
            line-height: 1.3;
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0;
            margin-left: 0.5rem;
            margin-right: 0.5rem;
            border: 1px solid #6b7280;
            border-radius: 0.25rem;
            background-color: #374151;
            color: #f8fafc;
            cursor: pointer;
            vertical-align: middle;
        }

        .row-expand-btn:hover {
            background-color: #4b5563;
            border-color: #9ca3af;
        }

        /* Hide detailed columns When row is collapsed */
        .section-item-row.row-collapsed .inputCell,
        .section-item-row.row-collapsed .inputCountCell,
        .section-item-row.row-collapsed .input2Cell,
        .section-item-row.row-collapsed .input3Cell,
        .section-item-row.row-collapsed .expectedCell,
        .section-item-row.row-collapsed .actualCell {
            max-width: 120px !important;
            max-height: 2rem !important;
            overflow: hidden !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
            word-break: normal !important;
            vertical-align: middle !important;
        }

        .section-item-row.row-collapsed .jsonCell {
            max-height: 2rem !important;
            overflow: hidden !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
            font-size: 0.8rem;
        }

        .section-item-row.row-collapsed .inputCell input,
        .section-item-row.row-collapsed .inputCountCell input,
        .section-item-row.row-collapsed .input2Cell input,
        .section-item-row.row-collapsed .input3Cell input,
        .section-item-row.row-collapsed .expectedCell input {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            pointer-events: none;
            opacity: 0.6;
        }

        .section-item-row.row-collapsed .actualCell {
            max-width: 180px !important;
        }
    </style>
    
    <!-- Include Ananya Header Component -->
    <?php include 'includes/header.php'; ?>
</head>

<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row page-header">
                <div class="col-12">
                    <h2 class="text-center mb-4">Ananya API Testing Suite</h2>
                    <p class="text-center text-muted">Test all API endpoints with custom inputs and view real-time results</p>
                </div>
            </div>
        
        <form name="form" id="form">
            <div class="row" style="padding: 15px;">
                <div class="col text-center">
                    <div id="testForm" style="display: block">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Test Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="universalInput" class="config-label">Universal Input:</label>
                                        <input type="text" class="form-control" name="word" id="universalInput" placeholder="Enter text to test">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="languageInput" class="config-label">Language:</label>
                                        <select name="languageInput" class="form-control" id="languageInput">
                                            <option value="English">English</option>
                                            <option selected value="Telugu">Telugu</option>
                                            <option value="Hindi">Hindi</option>
                                            <option value="Gujarati">Gujarati</option>
                                            <option value="Malayalam">Malayalam</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>&nbsp;</label>
                                        <input type="button" class="btn btn-secondary form-control" value="Update Inputs" onclick="updateInputs()">
                                    </div>
                                </div>
                                <input name="submit" type="submit" class="btn btn-primary btn-lg btn-block" value="Run All Tests">
                                <div class="mt-2">
                                    <small id="lastRunIndicator" class="text-muted">Last run: not yet run</small>
                                </div>
                                <div id="testRunSummary" class="alert alert-secondary mt-3 mb-0 py-2" role="status" aria-live="polite">
                                    Status: Not run yet
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row" style="padding: 0px;">
                <div class="col" id="tableDiv">
                    <div class="section-controls" aria-label="Section controls">
                        <span class="section-controls-title">Section Controls</span>
                        <div class="section-controls-actions">
                            <button type="button" class="btn btn-success btn-sm" id="expandAllSections">Expand All</button>
                            <button type="button" class="btn btn-secondary btn-sm" id="collapseAllSections">Collapse All</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="testSuite" class="table table-dark table-striped table-sm table-bordered" style="display:table;">
                            <thead id="apiHeader">
                                <tr class="header-data">
                                    <th scope="col" class="methodHeader">Method</th>
                                    <th scope="col" class="input1Header">Input 1</th>
                                    <th scope="col" class="input2Header">Input 2</th>
                                    <th scope="col" class="input3Header">Input 3</th>
                                    <th scope="col" class="expectedHeader">Expected Result</th>
                                    <th scope="col" class="actualHeader">Actual Result</th>
                                    <th scope="col">Pass/Fail</th>
                                    <th scope="col" class="jsonHeader">JSON Output</th>
                                </tr>
                            </thead>
                            <tbody id="apiTable">
                                <?php
                                $output = '';
                                $singleInputAPIs = array("authUserExists", "charConstant", "charVowel", "characterRole", "containsSpace", "detectLanguageLegacy", "getBaseCharacters", "getCodePointLength", "getCodePoints", "getLength", "getLength2", "getLengthNoSpaces", "getLengthNoSpacesNoCommas", "getLogicalChars", "getLogicalChars2", "getWordLevel", "getWordStrength", "getWordWeight", "isPalindrome", "lengthAlternative", "parseToLogicalChars", "parseToLogicalCharacters", "randomLogicalChars", "reverse", "splitInto15Chunks", "textRandomize", "utilityLanguage");
                                $doubleInputAPIs = array("addCharacterAtEnd", "areHeadAndTailWords", "areLadderWords", "authLogin", "baseConsonants", "canMakeAllWords", "canMakeWord", "compareTo", "compareToIgnoreCase", "containsAllLogicalChars", "containsChar", "containsLogicalCharSequence", "containsLogicalChars", "containsString", "endsWith", "equals", "getIntersectingRank", "getMatchIdString", "getUniqueIntersectingLogicalChars", "getUniqueIntersectingRank", "indexOf", "isAnagram", "isIntersecting", "logicalCharAt", "reverseEquals", "splitWord", "startsWith");
                                $tripleInputAPIs = array("addCharacterAt", "replace");
                                $getFillerChars = array("getFillerCharacters");
                                
                                // Organized by section (alphabetically) - matches interactive docs order
                                // Include rows for endpoints without automation wiring yet.
                                $sections = array(
                                    "Authentication" => array("authLogin", "authUserExists"),
                                    "Character Analysis" => array("addCharacterAt", "addCharacterAtEnd", "baseConsonants", "getBaseCharacters", "getCodePointLength", "getCodePoints", "getFillerCharacters", "getLogicalChars", "logicalCharAt", "randomLogicalChars"),
                                    "Character Validation" => array("charConstant", "charVowel", "containsAllLogicalChars", "containsChar", "containsLogicalCharSequence", "containsLogicalChars", "containsSpace", "containsString", "endsWith", "startsWith"),
                                    "String Comparison" => array("compareTo", "compareToIgnoreCase", "equals", "isIntersecting", "reverseEquals"),
                                    "Text Operations" => array("getLength", "replace", "reverse", "splitWord", "textRandomize"),
                                    "Utility" => array("getLength2", "getLengthNoSpaces", "getLengthNoSpacesNoCommas", "indexOf", "lengthAlternative", "utilityLanguage"),
                                    "Word Analysis" => array("areHeadAndTailWords", "areLadderWords", "canMakeAllWords", "canMakeWord", "characterRole", "detectLanguageLegacy", "getIntersectingRank", "getLogicalChars2", "getMatchIdString", "getUniqueIntersectingLogicalChars", "getUniqueIntersectingRank", "getWordLevel", "getWordStrength", "getWordWeight", "isAnagram", "isPalindrome", "parseToLogicalCharacters", "splitInto15Chunks")
                                );

                                foreach ($sections as $sectionName => $apis) {
                                    $sectionKey = strtolower(preg_replace('/[^a-z0-9]+/', '-', $sectionName));
                                    // Add section header
                                    $output .= '<tr class="section-header-row" data-section="' . $sectionKey . '" data-collapsed="false"><th colspan="8" class="py-1"><button type="button" class="btn btn-sm btn-outline-light section-toggle-btn me-2" data-section-toggle="' . $sectionKey . '" aria-expanded="true">-</button>' . $sectionName . '</th></tr>';
                                    
                                    foreach ($apis as $api) {
                                        if (in_array($api, $getFillerChars)) {
                                            $output = $output . '<tr class="table-data section-item-row row-collapsed" data-section="' . $sectionKey . '" data-row-expanded="false"><th scope="row" class="methodCell" id="' . $api . 'Method"><button type="button" class="row-expand-btn" data-row-toggle="' . $api . '" aria-label="Expand row" aria-expanded="false">+</button><span class="methodURL">' . $api . '</span></th><td class="inputCountCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputCountText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputTypeText text-white bg-dark" id="' . $api . 'TypeText"></td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected">-</td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                        } elseif (in_array($api, $tripleInputAPIs)) {
                                            $output = $output . '<tr class="table-data section-item-row row-collapsed" data-section="' . $sectionKey . '" data-row-expanded="false"><th scope="row" class="methodCell" id="' . $api . 'Method"><button type="button" class="row-expand-btn" data-row-toggle="' . $api . '" aria-label="Expand row" aria-expanded="false">+</button><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputText2 text-white bg-dark" id="' . $api . 'InputText2"></td><td class="input3Cell"  id="' . $api . 'Input3"><input type="text" size="12" class="inputText3 text-white bg-dark" id="' . $api . 'InputText3"></td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                        } elseif (in_array($api, $doubleInputAPIs)) {
                                            // Special handling for authentication endpoints
                                            if ($api === 'authLogin') {
                                                $output = $output . '<tr class="table-data section-item-row row-collapsed" data-section="' . $sectionKey . '" data-row-expanded="false"><th scope="row" class="methodCell" id="' . $api . 'Method"><button type="button" class="row-expand-btn" data-row-toggle="' . $api . '" aria-label="Expand row" aria-expanded="false">+</button><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="text-white bg-dark auth-input" id="' . $api . 'InputText" value="test@example.com" placeholder="Email"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="text-white bg-dark auth-input" id="' . $api . 'InputText2" value="password123" placeholder="Password"></td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText" value="{&quot;authenticated&quot;:true,&quot;user_id&quot;:999}"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                            } else {
                                                $output = $output . '<tr class="table-data section-item-row row-collapsed" data-section="' . $sectionKey . '" data-row-expanded="false"><th scope="row" class="methodCell" id="' . $api . 'Method"><button type="button" class="row-expand-btn" data-row-toggle="' . $api . '" aria-label="Expand row" aria-expanded="false">+</button><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputText2 text-white bg-dark" id="' . $api . 'InputText2"></td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                            }
                                        } elseif (in_array($api, $singleInputAPIs)) {
                                            // Special handling for authentication endpoints
                                            if ($api === 'authUserExists') {
                                                $output = $output . '<tr class="table-data section-item-row row-collapsed" data-section="' . $sectionKey . '" data-row-expanded="false"><th scope="row" class="methodCell" id="' . $api . 'Method"><button type="button" class="row-expand-btn" data-row-toggle="' . $api . '" aria-label="Expand row" aria-expanded="false">+</button><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="text-white bg-dark auth-input" id="' . $api . 'InputText" value="test@example.com" placeholder="Email"></td><td class="input2Cell" id="' . $api . 'Input2">-</td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText" value="true"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                            } else {
                                                $output = $output . '<tr class="table-data section-item-row row-collapsed" data-section="' . $sectionKey . '" data-row-expanded="false"><th scope="row" class="methodCell" id="' . $api . 'Method"><button type="button" class="row-expand-btn" data-row-toggle="' . $api . '" aria-label="Expand row" aria-expanded="false">+</button><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText" value=""></td><td class="input2Cell" id="' . $api . 'Input2">-</td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                            }
                                        } else {
                                            $label = ucwords(trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $api)));
                                            $output = $output . '<tr class="table-data section-item-row row-collapsed" data-section="' . $sectionKey . '" data-row-expanded="false"><th scope="row" class="methodCell" id="' . $api . 'Method"><button type="button" class="row-expand-btn" data-row-toggle="' . $api . '" aria-label="Expand row" aria-expanded="false">+</button><span class="text-muted">' . $label . '</span></th><td class="inputCell" id="' . $api . 'Input">-</td><td class="input2Cell" id="' . $api . 'Input2">-</td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected">-</td><td class="actualCell" id="' . $api . 'Actual"><span class="text-muted">Not automated yet</span></td><td class="passFail" id="' . $api . 'PassFail"><span class="text-muted">N/A</span></td><td class="jsonCell" id="' . $api . 'JSON">-</td></tr>';
                                        }
                                    }
                                }

                                echo $output;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>

<script>
    var docWidth = document.documentElement.offsetWidth;

    [].forEach.call(
        document.querySelectorAll('*'),
        function(el) {
            if (el.offsetWidth > docWidth) {
                console.log(el);
            }
        }
    );
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Row-level expand/collapse
        function setRowExpanded(row, expanded) {
            if (!row) {
                return;
            }

            if (expanded) {
                row.classList.remove('row-collapsed');
                row.setAttribute('data-row-expanded', 'true');
            } else {
                row.classList.add('row-collapsed');
                row.setAttribute('data-row-expanded', 'false');
            }

            var toggle = row.querySelector('.row-expand-btn');
            if (toggle) {
                toggle.textContent = expanded ? '-' : '+';
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }
        }

        // Section-level expand/collapse
        function setSectionCollapsed(sectionKey, collapsed) {
            var rows = document.querySelectorAll('#testSuite tr.section-item-row[data-section="' + sectionKey + '"]');
            rows.forEach(function (row) {
                row.style.display = collapsed ? 'none' : '';
                // When section expands, keep rows in collapsed state
                if (!collapsed) {
                    row.classList.add('row-collapsed');
                    row.setAttribute('data-row-expanded', 'false');
                    var toggle = row.querySelector('.row-expand-btn');
                    if (toggle) {
                        toggle.textContent = '+';
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            var header = document.querySelector('#testSuite tr.section-header-row[data-section="' + sectionKey + '"]');
            if (!header) {
                return;
            }

            header.setAttribute('data-collapsed', collapsed ? 'true' : 'false');
            var toggle = header.querySelector('.section-toggle-btn');
            if (toggle) {
                toggle.textContent = collapsed ? '+' : '-';
                toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }
        }

        function setAllSections(collapsed) {
            var headers = document.querySelectorAll('#testSuite tr.section-header-row[data-section]');
            headers.forEach(function (header) {
                var sectionKey = header.getAttribute('data-section');
                setSectionCollapsed(sectionKey, collapsed);
            });
        }

        function expandAllRows() {
            var allRows = document.querySelectorAll('#testSuite tr.section-item-row[data-row-expanded]');
            allRows.forEach(function(row) {
                setRowExpanded(row, true);
            });
        }

        function collapseAllRows() {
            var allRows = document.querySelectorAll('#testSuite tr.section-item-row[data-row-expanded]');
            allRows.forEach(function(row) {
                setRowExpanded(row, false);
            });
        }

        window.collapseAllTestSections = function () {
            setAllSections(true);
            collapseAllRows();
        };

        // Click handlers for row expand buttons
        document.querySelectorAll('#testSuite .row-expand-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var row = btn.closest('tr.section-item-row');
                var isExpanded = row && row.getAttribute('data-row-expanded') === 'true';
                setRowExpanded(row, !isExpanded);
            });
        });

        // Click handlers for section toggle buttons
        document.querySelectorAll('#testSuite .section-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var sectionKey = btn.getAttribute('data-section-toggle');
                var header = document.querySelector('#testSuite tr.section-header-row[data-section="' + sectionKey + '"]');
                var isCollapsed = header && header.getAttribute('data-collapsed') === 'true';
                setSectionCollapsed(sectionKey, !isCollapsed);
            });
        });

        var expandAllBtn = document.getElementById('expandAllSections');
        var collapseAllBtn = document.getElementById('collapseAllSections');

        if (expandAllBtn) {
            expandAllBtn.addEventListener('click', function () {
                setAllSections(false); // Expand all sections
                // Note: rows remain collapsed for scanning; users expand individual rows as needed
            });
        }

        if (collapseAllBtn) {
            collapseAllBtn.addEventListener('click', function () {
                window.collapseAllTestSections();
            });
        }

        // Default behavior: always enter page in collapsed mode.
        window.collapseAllTestSections();
    });
</script>
<script src="js/index.js"></script>
</html>