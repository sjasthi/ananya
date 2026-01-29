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
                                        <label for="universalInput">Universal Input:</label>
                                        <input type="text" class="form-control" name="word" id="universalInput" placeholder="Enter text to test">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="languageInput">Language:</label>
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
                                $singleInputAPIs = array("getCodePointLength", "getCodePoints", "getLength", "getLogicalChars", "getWordStrength", "getWordWeight", "isPalindrome", "reverse", "containsSpace", "getWordLevel", "getLengthNoSpaces", "getLengthNoSpacesNoCommas", "parseToLogicalChars", "parseToLogicalCharacters","getBaseCharacters","splitInto15Chunks","getLength2","getLogicalChars2");
                                $doubleInputAPIs = array("isAnagram", "startsWith", "endsWith", "containsString", "containsChar", "containsLogicalChars", "containsAllLogicalChars", "containsLogicalCharSequence", "canMakeWord", "canMakeAllWords", "addCharacterAtEnd", "isIntersecting", "getIntersectingRank", "getUniqueIntersectingRank", "compareTo", "compareToIgnoreCase", "splitWord", "equals", "reverseEquals", "logicalCharAt", "getUniqueIntersectingLogicalChars", "indexOf", "areLadderWords", "areHeadAndTailWords", "baseConsonants","charConstant","charVowel","getMatchIdString");
                                $tripleInputAPIs = array("addCharacterAt", "replace");
                                $getFillerChars = array("getFillerCharacters");

                                foreach ($getFillerChars as $api) {
                                    $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><a href="docs/api_refactored.php/#' . $api . '" class="methodURL">' . $api . '</a></th><td class="inputCountCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputCountText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputTypeText text-white bg-dark" id="' . $api . 'TypeText"></td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected">-</td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                }
                                foreach ($singleInputAPIs as $api) {
                                    $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><a href="docs/api_refactored.php/#' . $api . '" class="methodURL">' . $api . '</a></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText" value=""></td><td class="input2Cell" id="' . $api . 'Input2">-</td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                }
                                foreach ($doubleInputAPIs as $api) {
                                    $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><a href="docs/api_refactored.php/#' . $api . '" class="methodURL">' . $api . '</a></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputText2 text-white bg-dark" id="' . $api . 'InputText2"></td><td class="input3Cell" id="' . $api . 'Input3">-</td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
                                }
                                foreach ($tripleInputAPIs as $api) {
                                    $output = $output . '<tr class="table-data"><th scope="row" class="methodCell" id="' . $api . 'Method"><a href="docs/api_refactored.php/#' . $api . '" class="methodURL">' . $api . '</a></th><td class="inputCell"  id="' . $api . 'Input"><input type="text" size="12" class="inputText text-white bg-dark" id="' . $api . 'InputText"></td><td class="input2Cell"  id="' . $api . 'Input2"><input type="text" size="12" class="inputText2 text-white bg-dark" id="' . $api . 'InputText2"></td><td class="input3Cell"  id="' . $api . 'Input3"><input type="text" size="12" class="inputText3 text-white bg-dark" id="' . $api . 'InputText3"></td><td class="expectedCell" id="' . $api . 'Expected"><input type="text" size="12" class="expectedText text-white bg-dark" id="' . $api . 'ExpectedText"></td><td class="actualCell" id="' . $api . 'Actual"></td><td class="passFail" id="' . $api . 'PassFail"></td><td class="jsonCell" id="' . $api . 'JSON"></td></tr>';
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

</body>
</html>