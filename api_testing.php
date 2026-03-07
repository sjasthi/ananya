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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row" style="padding: 0px;">
                <div class="col" id="tableDiv">
                    <div class="table-responsive">
                        <table id="testSuite" class="table table-dark table-striped table-sm table-bordered" style="display:table;">
                            <thead id="apiHeader">
                                <tr class="header-data">
                                    <th scope="col" class="methodHeader">Method</th>
                                    <th scope="col">Input 1</th>
                                    <th scope="col">Input 2</th>
                                    <th scope="col">Input 3</th>
                                    <th scope="col">Expected Result</th>
                                    <th scope="col">Actual Result</th>
                                    <th scope="col">Pass/Fail</th>
                                    <th scope="col" class="jsonHeader">JSON Output</th>
                                </tr>
                            </thead>
                            <tbody id="apiTable">
                                <?php
                                $output = '';
                                $singleInputAPIs = array("containsSpace", "getBaseCharacters", "getCodePointLength", "getCodePoints", "getLength", "getLength2", "getLengthNoSpaces", "getLengthNoSpacesNoCommas", "getLogicalChars", "getLogicalChars2", "getWordLevel", "getWordStrength", "getWordWeight", "isPalindrome", "parseToLogicalChars", "parseToLogicalCharacters", "reverse", "splitInto15Chunks");
                                $doubleInputAPIs = array("addCharacterAtEnd", "areHeadAndTailWords", "areLadderWords", "baseConsonants", "canMakeAllWords", "canMakeWord", "charConstant", "charVowel", "compareTo", "compareToIgnoreCase", "containsAllLogicalChars", "containsChar", "containsLogicalCharSequence", "containsLogicalChars", "containsString", "endsWith", "equals", "getIntersectingRank", "getMatchIdString", "getUniqueIntersectingLogicalChars", "getUniqueIntersectingRank", "indexOf", "isAnagram", "isIntersecting", "logicalCharAt", "reverseEquals", "splitWord", "startsWith");
                                $tripleInputAPIs = array("addCharacterAt", "replace");
                                $getFillerChars = array("getFillerCharacters");
                                $orderedAPIs = array(
                                    "areHeadAndTailWords", "areLadderWords", "canMakeAllWords", "canMakeWord", "charConstant", "charVowel", "getIntersectingRank", "getMatchIdString", "getUniqueIntersectingLogicalChars", "getUniqueIntersectingRank", "getWordLevel", "getWordStrength", "getWordWeight", "isAnagram", "isPalindrome", "parseToLogicalCharacters", "splitInto15Chunks",
                                    "addCharacterAt", "addCharacterAtEnd", "baseConsonants", "getBaseCharacters", "getCodePointLength", "getCodePoints", "getFillerCharacters", "getLogicalChars", "logicalCharAt",
                                    "compareTo", "compareToIgnoreCase", "equals", "isIntersecting", "reverseEquals",
                                    "getLength", "getLength2", "getLogicalChars2", "parseToLogicalChars",
                                    "replace", "reverse", "splitWord",
                                    "getLengthNoSpaces", "getLengthNoSpacesNoCommas", "indexOf",
                                    "containsAllLogicalChars", "containsChar", "containsLogicalCharSequence", "containsLogicalChars", "containsSpace", "containsString", "endsWith", "startsWith"
                                );

                                foreach ($orderedAPIs as $api) {
                                    if (in_array($api, $getFillerChars)) {
                                        $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><span class="methodURL">' . $api . '</span></th><td class="inputCountCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputCountText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputTypeText text-white bg-dark" id="' . $api . 'TypeText"></td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected">-</td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                    } elseif (in_array($api, $tripleInputAPIs)) {
                                        $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputText2 text-white bg-dark" id="' . $api . 'InputText2"></td><td class="input3Cell"  id="' . $api . 'Input3"><input type="text" size="12" class="inputText3 text-white bg-dark" id="' . $api . 'InputText3"></td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                    } elseif (in_array($api, $doubleInputAPIs)) {
                                        $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputText2 text-white bg-dark" id="' . $api . 'InputText2"></td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                    } elseif (in_array($api, $singleInputAPIs)) {
                                        $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><span class="methodURL">' . $api . '</span></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText" value=""></td><td class="input2Cell" id="' . $api . 'Input2">-</td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
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
<script src="js/index.js"></script>
</html>