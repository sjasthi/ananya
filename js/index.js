/*Set local = true for local api endpoints, else set local = false to use thisisjava.com domain endpoints */
var local = true;

if (local == true) {
    apiURL = "http://localhost/ananya/";
} else {
    apiURL = "https://ananya.thisisjava.com/";
}

const apiEndpoints = {
    // api/auth/
    authLogin: "api/auth/login.php",
    authUserExists: "api/auth/user-exists.php",

    // api/analysis/
    areHeadAndTailWords: "api/analysis/heads-tails-words.php",
    areLadderWords: "api/analysis/ladder-words.php",
    canMakeAllWords: "api/analysis/can-make-all-words.php",
    canMakeWord: "api/analysis/can-make-word.php",
    getIntersectingRank: "api/analysis/intersecting-rank.php",
    getMatchIdString: "api/analysis/get-match-id-string.php",
    getUniqueIntersectingLogicalChars: "api/analysis/unique-intersecting-chars.php",
    getUniqueIntersectingRank: "api/analysis/unique-intersecting-rank.php",
    getWordLevel: "api/analysis/level.php",
    getWordStrength: "api/analysis/strength.php",
    getWordWeight: "api/analysis/weight.php",
    isAnagram: "api/analysis/is-anagram.php",
    isPalindrome: "api/analysis/is-palindrome.php",
    parseToLogicalCharacters: "api/analysis/parse-to-logical-characters.php",
    splitInto15Chunks: "api/analysis/split-into-chunks.php",

    // api/characters/
    addCharacterAt: "api/characters/add-at.php",
    addCharacterAtEnd: "api/characters/add-end.php",
    baseConsonants: "api/characters/base-consonants.php",
    getBaseCharacters: "api/characters/base.php",
    getCodePointLength: "api/characters/codepoints-length.php",
    getCodePoints: "api/characters/codepoints.php",
    getFillerCharacters: "api/characters/filler-characters.php",
    getLogicalChars: "api/characters/logical.php",
    logicalCharAt: "api/characters/logical-at.php",
    randomLogicalChars: "api/characters/random-logical-chars.php",

    // api/comparison/
    compareTo: "api/comparison/compare-to.php",
    compareToIgnoreCase: "api/comparison/compare-ignore-case.php",
    equals: "api/comparison/equals.php",
    isIntersecting: "api/comparison/is-intersecting.php",
    reverseEquals: "api/comparison/reverse-equals.php",

    // api/ (legacy root)
    getLength: "api/getLength.php",
    getLength2: "api/getLength2.php",
    getLogicalChars2: "api/getLogicalChars2.php",
    parseToLogicalChars: "api/parseToLogicalChars2.php",

    // api/text/
    replace: "api/text/replace.php",
    reverse: "api/text/reverse.php",
    splitWord: "api/text/split.php",

    // api/utility/
    getLengthNoSpaces: "api/utility/length-no-spaces.php",
    getLengthNoSpacesNoCommas: "api/utility/length-no-spaces-commas.php",
    indexOf: "api/utility/index-of.php",

    // api/validation/
    containsAllLogicalChars: "api/validation/contains-all-logical-chars.php",
    containsChar: "api/validation/contains-char.php",
    containsLogicalCharSequence: "api/validation/contains-logical-sequence.php",
    containsLogicalChars: "api/validation/contains-logical-chars.php",
    containsSpace: "api/validation/contains-space.php",
    containsString: "api/validation/contains-string.php",
    endsWith: "api/validation/ends-with.php",
    startsWith: "api/validation/starts-with.php",
    charConstant: "api.php/validation/is-consonant",
    charVowel: "api.php/validation/is-vowel"
};

function getMethodEndpoint(methodName) {
    return apiEndpoints[methodName] || ("api/" + methodName + ".php");
}

/**
 * Toggles entire page theme between dark and light mode (work in progress)
 * @param {} objButton 
 */
function changeTheme(objButton) {
    button = document.getElementById("theme");
    navigation = document.getElementById("navigation");
    table = document.getElementById("testSuite")
    inputCells = document.getElementsByClassName("inputText");
    var element = document.body;
    element.classList.toggle("dark-mode");

    if (objButton.value == "light") {
        //changing to Light Mode but making button stay Dark
        button.value = "dark";

        btnDark = document.querySelectorAll(".btn-dark");
        btnDark.forEach(function (btn) {
            btn.className = btn.className.replace("btn-dark", "btn-light");
        });

        textDark = document.querySelectorAll(".text-white, .bg-dark")
        textDark.forEach(function (txt) {
            txt.className = txt.className.replace("text-white", "text-dark")
            txt.className = txt.className.replace("bg-dark", "bg-light")
        })

        button.className = button.className.replace("btn-light", "btn-dark")
        navigation.className = navigation.className.replace("navbar-dark bg-dark", "navbar-light bg-light")
        table.className = table.className.replace("table-dark", "");
        $("#theme").html("Dark Mode");
    } else {
        //changing to Dark Mode but making button stay Light
        button.value = "light";

        btnLight = document.querySelectorAll(".btn-light");
        btnLight.forEach(function (btn) {
            btn.className = btn.className.replace("btn-light", "btn-dark");
        });

        textLight = document.querySelectorAll(".text-dark, .bg-light")
        textLight.forEach(function (txt) {
            txt.className = txt.className.replace("text-dark", "text-white")
            txt.className = txt.className.replace("bg-light", "bg-dark")
        })

        button.className = button.className.replace("btn-dark", "btn-light")
        navigation.className = navigation.className.replace("navbar-light bg-light", "navbar-dark bg-dark")
        table.className += " table-dark";
        $("#theme").html("Light Mode");
    }
}

/**
 * Function to update "Input" column of TestSuite table with universal input text field
 */
function updateInputs() {
    var input = document.getElementById("universalInput").value;
    var inputCells = document.getElementsByClassName("inputText");
    for (var i = 0; i < inputCells.length; i++) {
        // Skip authentication input fields - they have their own test credentials
        if (inputCells[i].classList.contains("auth-input")) {
            continue;
        }
        inputCells[i].value = input;
    }
}


/**
 * When an option is selected, remove the table headers and data.
 * Repopulate new header and data.
 */
$('#apiChoice').on('change', function (e) {
    $('#apiHeader .header-data').remove();
    $('#apiTable .table-data').remove();

    jQuery.get('getHeaders.php?apiChoice=' + $('#apiChoice').val()).done(function (data) {
        $('#apiHeader').append(data);
    });

    jQuery.get('getAPIs.php?apiChoice=' + $('#apiChoice').val()).done(function (data) {
        $('#apiTable').append(data);
        methods = document.querySelectorAll("th.methodCell");
        if (document.getElementById("testForm").style.display == "none") {
            document.getElementById("testForm").style.display = "block";
            document.getElementById("testSuite").style.display = "table";
        }
    });
});

/*Grabs the form*/
const form = document.querySelector("#form");

/*Adds event listener on form submit*/
form.addEventListener("submit", async (e) => {
    e.preventDefault();
    await runTests();

})

/*Grabs all the method names from the methods column*/
var methods = document.querySelectorAll(".methodURL");

function renderRunSummary(summary, isRunning) {
    var summaryEl = document.getElementById("testRunSummary");
    if (!summaryEl) {
        return;
    }

    summaryEl.classList.remove("alert-secondary", "alert-info", "alert-success", "alert-danger", "alert-warning");

    if (isRunning) {
        summaryEl.classList.add("alert-info");
        summaryEl.textContent = "Running tests...";
        return;
    }

    if (!summary) {
        summaryEl.classList.add("alert-secondary");
        summaryEl.textContent = "Status: Not run yet";
        return;
    }

    if (summary.automatedTotal === 0) {
        summaryEl.classList.add("alert-warning");
        summaryEl.textContent = "No automated tests found in the current table.";
        return;
    }

    if (summary.failed === 0) {
        summaryEl.classList.add("alert-success");
        summaryEl.textContent = "All automated tests passed. " + summary.passed + "/" + summary.automatedTotal + " passed" + (summary.notAutomated > 0 ? " | Not automated: " + summary.notAutomated : "");
    } else {
        summaryEl.classList.add("alert-danger");
        summaryEl.textContent = "Some tests failed. Passed: " + summary.passed + ", Failed: " + summary.failed + ", Total: " + summary.automatedTotal + (summary.notAutomated > 0 ? " | Not automated: " + summary.notAutomated : "");
    }
}

function collectRunSummary() {
    var passFailCells = Array.from(document.querySelectorAll("#apiTable .passFail"));
    var notAutomated = passFailCells.filter(function (cell) {
        return (cell.textContent || "").trim().toUpperCase() === "N/A";
    }).length;

    var methodLinks = Array.from(document.querySelectorAll(".methodURL"));
    var automatedTotal = methodLinks.length;
    var passed = 0;
    var failed = 0;

    methodLinks.forEach(function (link) {
        var methodName = link.textContent.trim();
        var statusCell = document.getElementById(methodName + "PassFail");
        var statusText = statusCell ? (statusCell.textContent || "").trim().toUpperCase() : "";

        if (statusText === "PASS") {
            passed += 1;
        } else {
            failed += 1;
        }
    });

    return {
        automatedTotal: automatedTotal,
        passed: passed,
        failed: failed,
        notAutomated: notAutomated
    };
}

function highlightSectionsWithFailures() {
    // Get all section headers
    var sectionHeaders = document.querySelectorAll('#testSuite tr.section-header-row[data-section]');
    
    sectionHeaders.forEach(function(header) {
        var sectionKey = header.getAttribute('data-section');
        // Find all rows in this section
        var sectionRows = document.querySelectorAll('#testSuite tr.section-item-row[data-section="' + sectionKey + '"]');
        
        var hasFailures = false;
        sectionRows.forEach(function(row) {
            var passFail = row.querySelector('.passFail');
            if (passFail) {
                var statusText = (passFail.textContent || "").trim().toUpperCase();
                if (statusText === "FAIL") {
                    hasFailures = true;
                }
            }
        });
        
        // Add or remove the failure class
        if (hasFailures) {
            header.classList.add('section-has-failures');
        } else {
            header.classList.remove('section-has-failures');
        }
    });
}

function formatResponseForDisplay(jsonObj, fallbackRaw) {
    if (!jsonObj || typeof jsonObj !== "object") {
        return String(fallbackRaw || "");
    }

    var responseCode = Number.isInteger(jsonObj.response_code)
        ? jsonObj.response_code
        : (jsonObj.success === false ? 500 : 200);

    var normalized = {
        response_code: responseCode,
        message: jsonObj.message || (jsonObj.error ? String(jsonObj.error) : ""),
        data: jsonObj.data !== undefined ? jsonObj.data : (jsonObj.result !== undefined ? jsonObj.result : null),
        success: typeof jsonObj.success === "boolean" ? jsonObj.success : responseCode === 200,
        error: jsonObj.error !== undefined ? jsonObj.error : null
    };

    // Manually format to keep data field compact (including nested arrays) while keeping outer structure readable
    var pretty = "{\n";
    pretty += '  "response_code": ' + normalized.response_code + ',\n';
    pretty += '  "message": ' + JSON.stringify(normalized.message) + ',\n';
    pretty += '  "data": ' + JSON.stringify(normalized.data) + ',\n';
    pretty += '  "success": ' + normalized.success + ',\n';
    pretty += '  "error": ' + JSON.stringify(normalized.error) + '\n';
    pretty += "}";

    return pretty;
}

/*Async function to run the tests*/
async function runTests() {
    const submitButton = form.querySelector('input[type="submit"]');
    const lastRunIndicator = document.getElementById("lastRunIndicator");
    const methodLinks = document.querySelectorAll(".methodURL");

    if (typeof window.collapseAllTestSections === "function") {
        window.collapseAllTestSections();
    }

    methods = methodLinks;

    if (!methodLinks.length) {
        console.warn("No test methods found in the table.");
        if (lastRunIndicator) {
            lastRunIndicator.textContent = "Last run: no methods found";
        }
        renderRunSummary({ automatedTotal: 0, passed: 0, failed: 0, notAutomated: 0 }, false);
        return;
    }

    const originalButtonText = submitButton ? submitButton.value : null;
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.value = "Running tests...";
    }
    if (lastRunIndicator) {
        lastRunIndicator.textContent = "Last run: running...";
    }
    renderRunSummary(null, true);

    try {
        await Promise.allSettled(Array.from(methodLinks).map(function (method) {
            const methodName = method.textContent.trim();
            return callAPI(methodName);
        }));
    } finally {
        renderRunSummary(collectRunSummary(), false);
        highlightSectionsWithFailures();
        if (lastRunIndicator) {
            const completedAt = new Date().toLocaleTimeString();
            lastRunIndicator.textContent = "Last run: " + completedAt;
        }
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.value = originalButtonText || "Run All Tests";
        }
    }
}

/*Takes methodName as argument and does API call to retrieve the appropriate data*/
async function callAPI(methodName) {

    const singleInput = [
        // api/analysis/
        "getWordLevel", "getWordStrength", "getWordWeight", "isPalindrome", "parseToLogicalCharacters", "splitInto15Chunks",
        // api/auth/
        "authUserExists",
        // api/characters/
        "getBaseCharacters", "getCodePointLength", "getCodePoints", "getLogicalChars",
        // api/ (legacy root)
        "getLength", "getLength2", "getLogicalChars2", "parseToLogicalChars",
        // api/text/
        "randomize", "reverse",
        // api/utility/
        "getLengthNoSpaces", "getLengthNoSpacesNoCommas",
        // api/validation/
        "charConstant", "charVowel", "containsSpace"
    ];
    const doubleInput = [
        // api/analysis/
        "areHeadAndTailWords", "areLadderWords", "canMakeAllWords", "canMakeWord", "getIntersectingRank", "getMatchIdString", "getUniqueIntersectingLogicalChars", "getUniqueIntersectingRank", "isAnagram",
        // api/auth/
        "authLogin",
        // api/characters/
        "addCharacterAtEnd", "baseConsonants", "logicalCharAt",
        // api/comparison/
        "compareTo", "compareToIgnoreCase", "equals", "isIntersecting", "reverseEquals",
        // api/text/
        "splitWord",
        // api/utility/
        "indexOf",
        // api/validation/
        "containsAllLogicalChars", "containsChar", "containsLogicalCharSequence", "containsLogicalChars", "containsString", "endsWith", "startsWith"
    ];
    const tripleInput = [
        // api/characters/
        "addCharacterAt",
        // api/text/
        "replace"
    ];
    let result = "";

    if (methodName == "getFillerCharacters") {
        var jsonElement = document.getElementById(methodName + "JSON");
        var actualCell = document.getElementById(methodName + "Actual");
        var passFail = document.getElementById(methodName + "PassFail");

        try {
            var cellInput = document.getElementById(methodName + 'InputText').value;
            var languageInput = document.getElementById("languageInput").value;
            var type = document.getElementById(methodName + 'TypeText').value;
            const endpoint = getMethodEndpoint(methodName);
            await fetch(apiURL + endpoint + '?count=' + encodeURIComponent(cellInput) + '&language=' + encodeURIComponent(languageInput) + '&type=' + encodeURIComponent(type))
                .then(response => response.text())
                .then(data => result = data);
            newResult = remove_non_ascii(result);
            const jsonObj = JSON.parse(newResult);

            jsonElement.textContent = formatResponseForDisplay(jsonObj, result);
            actualCell.innerHTML = jsonObj.data;
            if (jsonObj.response_code != 200) {
                passFail.innerHTML = "FAIL";
                passFail.classList.remove("pass")
                passFail.classList.add("fail")
                passFail.classList.remove("table-success")
                passFail.classList.add("table-danger")
            } else {
                passFail.innerHTML = "PASS";
                passFail.classList.remove("fail")
                passFail.classList.add("pass")
                passFail.classList.remove("table-danger")
                passFail.classList.add("table-success")
            }
        } catch (error) {
            jsonElement.textContent = formatResponseForDisplay({
                response_code: 500,
                message: "Request/parse error",
                data: null,
                success: false,
                error: String(error)
            });
            actualCell.innerHTML = "Request/parse error";
            passFail.innerHTML = "FAIL";
            passFail.classList.remove("pass");
            passFail.classList.add("fail");
            passFail.classList.remove("table-success");
            passFail.classList.add("table-danger");
        }
    } else {
        var languageInput = document.getElementById("languageInput").value;
        var expectedResult = document.getElementById(methodName + "ExpectedText").value;
        var jsonElement = document.getElementById(methodName + "JSON");
        var actualCell = document.getElementById(methodName + "Actual");
        var passFail = document.getElementById(methodName + "PassFail");

        try {
            const endpoint = getMethodEndpoint(methodName);
            
            // Special handling for authentication endpoints
            if (methodName === "authLogin") {
                var email = document.getElementById(methodName + 'InputText').value;
                var password = document.getElementById(methodName + 'InputText2').value;
                await fetch(apiURL + endpoint + '?email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password))
                    .then(response => response.text())
                    .then(data => result = data);
            } else if (methodName === "authUserExists") {
                var email = document.getElementById(methodName + 'InputText').value;
                await fetch(apiURL + endpoint + '?email=' + encodeURIComponent(email))
                    .then(response => response.text())
                    .then(data => result = data);
            } else if (methodName === "randomLogicalChars") {
                // Special handling: uses 'int' parameter instead of 'string'
                var count = document.getElementById(methodName + 'InputText').value;
                await fetch(apiURL + endpoint + '?int=' + encodeURIComponent(count) + '&language=' + encodeURIComponent(languageInput))
                    .then(response => response.text())
                    .then(data => result = data);
            } else if (singleInput.includes(methodName)) {
                var cellInput = document.getElementById(methodName + 'InputText').value;
                await fetch(apiURL + endpoint + '?string=' + encodeURIComponent(cellInput) + '&language=' + encodeURIComponent(languageInput))
                    .then(response => response.text())
                    .then(data => result = data);
            } else if (doubleInput.includes(methodName)) {
                var cellInput = document.getElementById(methodName + 'InputText').value;
                var cellInput2 = document.getElementById(methodName + 'InputText2').value;
                await fetch(apiURL + endpoint + '?input1=' + encodeURIComponent(cellInput) + '&input2=' + encodeURIComponent(languageInput) + '&input3=' + encodeURIComponent(cellInput2))
                    .then(response => response.text())
                    .then(data => result = data);
            } else if (tripleInput.includes(methodName)) {
                var cellInput = document.getElementById(methodName + 'InputText').value;
                var cellInput2 = document.getElementById(methodName + 'InputText2').value;
                var cellInput3 = document.getElementById(methodName + 'InputText3').value;
                await fetch(apiURL + endpoint + '?input1=' + encodeURIComponent(cellInput) + '&input2=' + encodeURIComponent(languageInput) + '&input3=' + encodeURIComponent(cellInput2) + '&input4=' + encodeURIComponent(cellInput3))
                    .then(response => response.text())
                    .then(data => result = data);
            } else {
                jsonElement.textContent = JSON.stringify({
                    response_code: 400,
                    message: "Unsupported method in test runner",
                    data: { method: methodName },
                    success: false,
                    error: "Unsupported method"
                }, null, 2);
                actualCell.innerHTML = "Unsupported method";
                passFail.innerHTML = "FAIL";
                passFail.classList.remove("pass");
                passFail.classList.add("fail");
                passFail.classList.remove("table-success");
                passFail.classList.add("table-danger");
                return;
            }

            newResult = remove_non_ascii(result);
            const jsonObj = JSON.parse(newResult);

            jsonElement.textContent = formatResponseForDisplay(jsonObj, result);

            if (Array.isArray(jsonObj.data)) {
                actualCell.innerHTML = jsonObj.data.toString();
            }
            else if (jsonObj.data?.constructor.name === "Object") {
                actualCell.innerHTML = JSON.stringify(jsonObj.data);
            }
            else {
                actualCell.innerHTML = jsonObj.data;
            }

            if (jsonObj.response_code != 200) {
                passFail.innerHTML = "FAIL";
                passFail.classList.remove("pass");
                passFail.classList.add("fail");
                passFail.classList.remove("table-success");
                passFail.classList.add("table-danger");
            } else if (expectedResult === "") {
                // Empty expected result means non-deterministic/random endpoint
                // Just verify the response code is 200 (already checked above)
                passFail.innerHTML = "PASS";
                passFail.classList.remove("fail");
                passFail.classList.add("pass");
                passFail.classList.remove("table-danger");
                passFail.classList.add("table-success");
            } else if (expectedResult == actualCell.innerHTML) {
                passFail.innerHTML = "PASS";
                passFail.classList.remove("fail");
                passFail.classList.add("pass");
                passFail.classList.remove("table-danger");
                passFail.classList.add("table-success");
            } else {
                passFail.innerHTML = "FAIL";
                passFail.classList.remove("pass");
                passFail.classList.add("fail");
                passFail.classList.remove("table-success");
                passFail.classList.add("table-danger");
            }
        } catch (error) {
            jsonElement.textContent = formatResponseForDisplay({
                response_code: 500,
                message: "Request/parse error",
                data: null,
                success: false,
                error: String(error)
            });
            actualCell.innerHTML = "Request/parse error";
            passFail.innerHTML = "FAIL";
            passFail.classList.remove("pass");
            passFail.classList.add("fail");
            passFail.classList.remove("table-success");
            passFail.classList.add("table-danger");
        }
    }
}


/**
 * Removes NON-ASCII characters from strings 
 */
function remove_non_ascii(str) {
    if ((str === null) || (str === ''))
        return false;
    else
        str = str.toString();

    return str.replace(/[^\x20-\x7E\uC00-\u0C7F]/g, '');
}


window.onload = function () {
    var language = document.getElementById("languageInput").value;
    getLanguageValues(language);
    getDefaultValues(language);
    runTests();

}

/**
 * Generates default values for expected and runs the tests when the language dropdown is changed
 */
$('#languageInput').on('change', function (e) {
    var language = document.getElementById("languageInput").value;
    getLanguageValues(language);
    getDefaultValues(language);
    runTests();
})

function getLanguageValues(language) {
    var input;
    document.getElementById("getFillerCharactersInputText").value = 20;
    document.getElementById("getFillerCharactersTypeText").value = "vowel";
    if (language == "English") {
        input = "hello";
    } else if (language == "Telugu") {
        input = "అమెరికాఆస్ట్రేలియా";
    }
    document.getElementById("universalInput").value = input;
    var inputCells = document.getElementsByClassName("inputText");
    for (var i = 0; i < inputCells.length; i++) {
        inputCells[i].value = input;
    }
    
    // Set specific single-character inputs for character validation tests
    if (language == "English") {
        var charVowelEl = document.getElementById("charVowelInputText");
        var charConstantEl = document.getElementById("charConstantInputText");
        if (charVowelEl) charVowelEl.value = "a";
        if (charConstantEl) charConstantEl.value = "h";
    } else if (language == "Telugu") {
        var charVowelEl = document.getElementById("charVowelInputText");
        var charConstantEl = document.getElementById("charConstantInputText");
        if (charVowelEl) charVowelEl.value = "అ";
        if (charConstantEl) charConstantEl.value = "క";
    }
}


function getDefaultValues(language) {
    if (language == "English") {
        document.getElementById("isAnagramInputText2").value = "llohe";
        document.getElementById("startsWithInputText2").value = "h";
        document.getElementById("endsWithInputText2").value = "o";
        document.getElementById("containsStringInputText2").value = "lo";
        document.getElementById("containsCharInputText2").value = "o";
        document.getElementById("containsLogicalCharsInputText2").value = "l,o";
        document.getElementById("containsAllLogicalCharsInputText2").value = "l,o";
        document.getElementById("containsLogicalCharSequenceInputText2").value = "lo";
        document.getElementById("canMakeWordInputText2").value = "lo";
        document.getElementById("canMakeAllWordsInputText2").value = "hell,lo";
        document.getElementById("addCharacterAtEndInputText2").value = "a";
        document.getElementById("isIntersectingInputText2").value = "el";
        document.getElementById("getIntersectingRankInputText2").value = "el";
        document.getElementById("getUniqueIntersectingRankInputText2").value = "e,l,i";
        document.getElementById("compareToInputText2").value = "hello";
        document.getElementById("compareToIgnoreCaseInputText2").value = "HEL";
        document.getElementById("splitWordInputText2").value = "2";
        document.getElementById("equalsInputText2").value = "hello";
        document.getElementById("reverseEqualsInputText2").value = "olleh";
        document.getElementById("logicalCharAtInputText2").value = "3";
        document.getElementById("getUniqueIntersectingLogicalCharsInputText2").value = "l,l";
        document.getElementById("indexOfInputText2").value = "e";
        document.getElementById("addCharacterAtInputText2").value = "1";
        document.getElementById("replaceInputText2").value = "ell";
        document.getElementById("addCharacterAtInputText3").value = "e";
        document.getElementById("replaceInputText3").value = "i";
        document.getElementById("areLadderWordsInputText2").value = "cello";
        document.getElementById("areHeadAndTailWordsInputText2").value = "oasis";
        document.getElementById("baseConsonantsInputText2").value = "hilla";


        document.getElementById("getCodePointLengthExpectedText").value = "5";
        document.getElementById("getCodePointsExpectedText").value = "104,101,108,108,111";
        document.getElementById("getLengthExpectedText").value = "5";
        document.getElementById("getLogicalCharsExpectedText").value = "h,e,l,l,o";
        document.getElementById("getWordStrengthExpectedText").value = "5";
        document.getElementById("getWordWeightExpectedText").value = "5";
        document.getElementById("isPalindromeExpectedText").value = "false";
        document.getElementById("reverseExpectedText").value = "olleh";
        document.getElementById("containsSpaceExpectedText").value = "false";
        document.getElementById("getWordLevelExpectedText").value = "5";
        document.getElementById("getLengthNoSpacesExpectedText").value = "5";
        document.getElementById("getLengthNoSpacesNoCommasExpectedText").value = "5";
        document.getElementById("parseToLogicalCharsExpectedText").value = "h,e,l,l,o";
        document.getElementById("parseToLogicalCharactersExpectedText").value = "h,e,l,l,o";
        document.getElementById("isAnagramExpectedText").value = "true";
        document.getElementById("startsWithExpectedText").value = "true";
        document.getElementById("endsWithExpectedText").value = "true";
        document.getElementById("containsStringExpectedText").value = "true";
        document.getElementById("containsCharExpectedText").value = "true";
        document.getElementById("containsLogicalCharsExpectedText").value = "true";
        document.getElementById("containsAllLogicalCharsExpectedText").value = "true";
        document.getElementById("containsLogicalCharSequenceExpectedText").value = "true";
        document.getElementById("charVowelExpectedText").value = "true";
        document.getElementById("charConstantExpectedText").value = "true";
        document.getElementById("canMakeWordExpectedText").value = "true";
        document.getElementById("canMakeAllWordsExpectedText").value = "true";
        document.getElementById("addCharacterAtEndExpectedText").value = "helloa";
        document.getElementById("isIntersectingExpectedText").value = "true";
        document.getElementById("getIntersectingRankExpectedText").value = "3";
        document.getElementById("getUniqueIntersectingRankExpectedText").value = "2";
        document.getElementById("compareToExpectedText").value = "0";
        document.getElementById("compareToIgnoreCaseExpectedText").value = "1";
        document.getElementById("splitWordExpectedText").value = `{"0":["h","e"],"2":["l","l"],"4":["o",null]}`;
        document.getElementById("equalsExpectedText").value = "true";
        document.getElementById("reverseEqualsExpectedText").value = "true";
        document.getElementById("logicalCharAtExpectedText").value = "l";
        document.getElementById("getUniqueIntersectingLogicalCharsExpectedText").value = "2";
        document.getElementById("indexOfExpectedText").value = "1";
        document.getElementById("addCharacterAtExpectedText").value = "heello";
        document.getElementById("replaceExpectedText").value = "hio";
        document.getElementById("areLadderWordsExpectedText").value = "true";
        document.getElementById("areHeadAndTailWordsExpectedText").value = "true";
        document.getElementById("baseConsonantsExpectedText").value = "true";
        document.getElementById("getBaseCharactersExpectedText").value = "h,e,l,l,o";
        document.getElementById("randomLogicalCharsInputText").value = "5";
        document.getElementById("randomLogicalCharsExpectedText").value = ""; // Non-deterministic - varies each call
    }

    if (language == "Telugu") {
        document.getElementById("isAnagramInputText2").value = "అఆమెస్ట్రేరిలికాయా";
        document.getElementById("startsWithInputText2").value = "అమె";
        document.getElementById("endsWithInputText2").value = "లియా";
        document.getElementById("containsStringInputText2").value = "అమెరికా";
        document.getElementById("containsCharInputText2").value = "స్ట్రే";
        document.getElementById("containsLogicalCharsInputText2").value = "కా,యా,లి";
        document.getElementById("containsAllLogicalCharsInputText2").value = "కా,యా,లి";
        document.getElementById("containsLogicalCharSequenceInputText2").value = "రి,కా,ఆ";
        document.getElementById("canMakeWordInputText2").value = "అమెరికా";
        document.getElementById("canMakeAllWordsInputText2").value = "అమెరికా,ఆస్ట్రేలియా";
        document.getElementById("addCharacterAtEndInputText2").value = "ల్లో";
        document.getElementById("isIntersectingInputText2").value = "ఇటలి";
        document.getElementById("getIntersectingRankInputText2").value = "కాయాలి";
        document.getElementById("getUniqueIntersectingRankInputText2").value = "కా,యా,లి";
        document.getElementById("compareToInputText2").value = "అమెరికాఆస్ట్రేలియా";
        document.getElementById("compareToIgnoreCaseInputText2").value = "ఆస్ట్రేలియా";
        document.getElementById("splitWordInputText2").value = "2";
        document.getElementById("equalsInputText2").value = "అమెరికాఆస్ట్రేలియా";
        document.getElementById("reverseEqualsInputText2").value = "యాలిస్ట్రేఆకారిమెఅ";
        document.getElementById("logicalCharAtInputText2").value = "6";
        document.getElementById("getUniqueIntersectingLogicalCharsInputText2").value = "కా,యా,లి";
        document.getElementById("indexOfInputText2").value = "లి";
        document.getElementById("addCharacterAtInputText2").value = "3";
        document.getElementById("replaceInputText2").value = "అమెరికా";
        document.getElementById("addCharacterAtInputText3").value = "క్క";
        document.getElementById("replaceInputText3").value = "క్క";
        document.getElementById("areLadderWordsInputText2").value = "అమ్మరికాఆస్ట్రేలియా";
        document.getElementById("areHeadAndTailWordsInputText2").value = "యామాతారాజభానస";
        document.getElementById("baseConsonantsInputText2").value = "అమరకఆసలయ";

        document.getElementById("getCodePointLengthExpectedText").value = "18";
        document.getElementById("getCodePointsExpectedText").value = "3077,3118,3142,3120,3135,3093,3134,3078,3128,3149,3103,3149,3120,3143,3122,3135,3119,3134";
        document.getElementById("getLengthExpectedText").value = "8";
        document.getElementById("getLogicalCharsExpectedText").value = "అ,మె,రి,కా,ఆ,స్ట్రే,లి,యా";
        document.getElementById("getWordStrengthExpectedText").value = "6";
        document.getElementById("getWordWeightExpectedText").value = "18";
        document.getElementById("isPalindromeExpectedText").value = "false";
        document.getElementById("reverseExpectedText").value = "యాలిస్ట్రేఆకారిమెఅ";
        document.getElementById("containsSpaceExpectedText").value = "false";
        document.getElementById("getWordLevelExpectedText").value = "6";
        document.getElementById("getLengthNoSpacesExpectedText").value = "8";
        document.getElementById("getLengthNoSpacesNoCommasExpectedText").value = "8";
        document.getElementById("parseToLogicalCharsExpectedText").value = "అ,మె,రి,కా,ఆ,స్ట్రే,లి,యా";
        document.getElementById("parseToLogicalCharactersExpectedText").value = "అ,మె,రి,కా,ఆ,స్ట్రే,లి,యా";
        document.getElementById("isAnagramExpectedText").value = "true";
        document.getElementById("startsWithExpectedText").value = "true";
        document.getElementById("endsWithExpectedText").value = "true";
        document.getElementById("containsStringExpectedText").value = "true";
        document.getElementById("containsCharExpectedText").value = "true";
        document.getElementById("containsLogicalCharsExpectedText").value = "true";
        document.getElementById("containsAllLogicalCharsExpectedText").value = "true";
        document.getElementById("containsLogicalCharSequenceExpectedText").value = "false";
        document.getElementById("charVowelExpectedText").value = "true";
        document.getElementById("charConstantExpectedText").value = "true";
        document.getElementById("canMakeWordExpectedText").value = "true";
        document.getElementById("canMakeAllWordsExpectedText").value = "true";
        document.getElementById("addCharacterAtEndExpectedText").value = "అమెరికాఆస్ట్రేలియాల్లో";
        document.getElementById("isIntersectingExpectedText").value = "true";
        document.getElementById("getIntersectingRankExpectedText").value = "3";
        document.getElementById("getUniqueIntersectingRankExpectedText").value = "3";
        document.getElementById("compareToExpectedText").value = "0";
        document.getElementById("compareToIgnoreCaseExpectedText").value = "-1";
        document.getElementById("splitWordExpectedText").value = `{"0":["అ","మె"],"2":["రి","కా"],"4":["ఆ","స్ట్రే"],"6":["లి","యా"]}`;
        document.getElementById("equalsExpectedText").value = "true";
        document.getElementById("reverseEqualsExpectedText").value = "true";
        document.getElementById("logicalCharAtExpectedText").value = "లి";
        document.getElementById("getUniqueIntersectingLogicalCharsExpectedText").value = "3";
        document.getElementById("indexOfExpectedText").value = "6";
        document.getElementById("addCharacterAtExpectedText").value = "అమెరిక్కకాఆస్ట్రేలియా";
        document.getElementById("replaceExpectedText").value = "క్కఆస్ట్రేలియా";
        document.getElementById("areLadderWordsExpectedText").value = "true";
        document.getElementById("areHeadAndTailWordsExpectedText").value = "true";
        document.getElementById("baseConsonantsExpectedText").value = "true";
        document.getElementById("getBaseCharactersExpectedText").value = "అ,మ,ర,క,ఆ,స,ల,య";
        document.getElementById("randomLogicalCharsInputText").value = "5";
        document.getElementById("randomLogicalCharsExpectedText").value = ""; // Non-deterministic - varies each call
    }
}
