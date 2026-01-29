//Getting DOM objects
const inputText = document.querySelector("#input-text");
const textToCompare = document.querySelector("#text-to-compare");
const finderMatches = document.querySelector("#finder-matches");
const processButton = document.querySelector("#process");
const languageSelector = document.querySelector("#language-select");
const statusDiv = document.querySelector("#status");
let language = "telugu"; // Default to Telugu

function updateStatus(message) {
    console.log('Status update:', message);
    if (statusDiv) {
        statusDiv.innerHTML = `<small class="text-info">${message}</small>`;
    }
}

// Page load debugging
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAGE LOADED - DEBUGGING INFO ===');
    console.log('Input text element:', inputText);
    console.log('Text to compare element:', textToCompare);
    console.log('Finder matches element:', finderMatches);
    console.log('Process button element:', processButton);
    console.log('Language selector element:', languageSelector);
    console.log('Status div element:', statusDiv);
    console.log('Current language:', language);
    console.log('Input text value:', inputText?.value);
    console.log('Text to compare value length:', textToCompare?.value?.length);
    
    if (processButton) {
        console.log('Process button found and ready for clicks');
    } else {
        console.error('Process button not found!');
    }
});

languageSelector.addEventListener("change", () => {
    language = languageSelector.value;
    console.log('Language changed to:', language);
    // Don't clear the default text when language changes
    finderMatches.value = "";
})

processButton.addEventListener("click", async () => {
    console.log('=== PROCESS BUTTON CLICKED ===');
    console.log('Button element:', processButton);
    console.log('Current language:', language);
    
    processButton.disabled = true;
    processButton.textContent = "Processing...";
    finderMatches.value = "Starting debug process...\n";
    updateStatus("Starting search...");
    
    //getting text values of input box and text area
    const inputTextValue = inputText.value.trim();
    const textToCompareValue = textToCompare.value.trim();
    
    console.log('=== INPUT VALUES ===');
    console.log('Search term:', inputTextValue);
    console.log('Search term length:', inputTextValue.length);
    console.log('Text to search in length:', textToCompareValue.length);
    console.log('Text to search in (first 200 chars):', textToCompareValue.substring(0, 200));
    
    finderMatches.value += `Search term: "${inputTextValue}"\n`;
    finderMatches.value += `Text length: ${textToCompareValue.length} characters\n\n`;

    if(inputTextValue === "" || textToCompareValue === "") {
        finderMatches.value = "Please provide both search term and text to search in.";
        updateStatus("Please provide both search term and text to search in.");
        processButton.disabled = false;
        processButton.textContent = "Process";
        return;
    }

    try {
        //getting base chars and length of input for comparison
        updateStatus(`Getting base characters for "${inputTextValue}"...`);
        finderMatches.value += "=== GETTING BASE CHARACTERS FOR SEARCH TERM ===\n";
        
        console.log('=== CALLING API FOR SEARCH TERM ===');
        const inputTextBaseCharacters = await getBaseCharacters(inputTextValue);
        
        console.log('=== SEARCH TERM RESULTS ===');
        console.log('Base characters received:', inputTextBaseCharacters);
        console.log('Type:', typeof inputTextBaseCharacters);
        console.log('Is array:', Array.isArray(inputTextBaseCharacters));
        
        finderMatches.value += `Search term "${inputTextValue}" base characters: [${inputTextBaseCharacters.join(', ')}]\n`;
        finderMatches.value += `Count: ${inputTextBaseCharacters.length}\n\n`;

        if (!inputTextBaseCharacters || inputTextBaseCharacters.length === 0) {
            finderMatches.value += "ERROR: Could not get base characters for search term!\n";
            finderMatches.value += "This might be an API issue or the word is not recognized.\n";
            updateStatus("Error: Could not process search term.");
            processButton.disabled = false;
            processButton.textContent = "Process";
            return;
        }

        //splitting text into words (not just lines) for better matching
        const words = textToCompareValue.split(/\s+/).filter(word => word.trim() !== '');
        console.log('=== WORD PROCESSING ===');
        console.log('Total words found:', words.length);
        console.log('First 10 words:', words.slice(0, 10));
        
        finderMatches.value += `=== PROCESSING ${words.length} WORDS ===\n`;
        updateStatus(`Searching through ${words.length} words...`);

        let matchCount = 0;
        let processedCount = 0;

        //check each word for matches
        for (const word of words) {
            const cleanWord = word.replace(/[^\u0C00-\u0C7F\u0020-\u007E]/g, '').trim();
            if (cleanWord && cleanWord.length > 1) { // Skip single characters
                processedCount++;
                
                console.log(`=== PROCESSING WORD ${processedCount}: "${cleanWord}" ===`);
                finderMatches.value += `Checking word ${processedCount}: "${cleanWord}"\n`;
                
                if (processedCount % 5 === 0) {
                    updateStatus(`Processed ${processedCount}/${words.length} words... Found ${matchCount} matches so far.`);
                }
                
                const baseChars = await getBaseCharacters(cleanWord);
                console.log('Base characters for "' + cleanWord + '":', baseChars);
                finderMatches.value += `  Base chars: [${baseChars.join(', ')}]\n`;
                
                const isMatch = compareArrays(inputTextBaseCharacters, baseChars);
                console.log('Is match?', isMatch);
                finderMatches.value += `  Match: ${isMatch}\n`;
                
                if (isMatch) {
                    finderMatches.value += `  *** MATCH FOUND: "${cleanWord}" ***\n`;
                    matchCount++;
                    console.log('=== MATCH FOUND ===:', cleanWord);
                }
                
                finderMatches.value += `\n`;
                
                // Only process first 10 words for debugging
                if (processedCount >= 10) {
                    finderMatches.value += `\n=== STOPPING AFTER 10 WORDS FOR DEBUGGING ===\n`;
                    break;
                }
            }
        }
        
        if (matchCount === 0) {
            finderMatches.value = "No matches found. Try a different search term.";
            updateStatus("Search completed - no matches found.");
        } else {
            updateStatus(`Search completed - found ${matchCount} matching words!`);
        }
        
    } catch (error) {
        console.error('Error during processing:', error);
        finderMatches.value = "Error occurred during processing. Check console for details.";
        updateStatus("Error occurred during processing.");
    }
    
    processButton.disabled = false;
    processButton.textContent = "Process";
})

//compares if the arrays have the same contents
function compareArrays (array1, array2) {
    console.log('=== COMPARING ARRAYS ===');
    console.log('Array 1:', array1, 'Type:', typeof array1, 'Length:', array1?.length);
    console.log('Array 2:', array2, 'Type:', typeof array2, 'Length:', array2?.length);
    
    if (!array1 || !array2) {
        console.log('One or both arrays are null/undefined');
        return false;
    }
    
    if (array1.length == 0 || array2.length == 0) {
        console.log('One or both arrays are empty');
        return false;
    }
    
    if (array1.length != array2.length) {
        console.log('Arrays have different lengths:', array1.length, 'vs', array2.length);
        return false;
    }
    
    // Create copies to avoid modifying original arrays
    const sorted1 = [...array1].sort();
    const sorted2 = [...array2].sort();
    
    console.log('Sorted array 1:', sorted1);
    console.log('Sorted array 2:', sorted2);
    
    for (let i = 0; i < sorted1.length; i++) {
        if (sorted1[i] != sorted2[i]) {
            console.log(`Difference at index ${i}: "${sorted1[i]}" vs "${sorted2[i]}"`);
            return false;
        }
    }
    
    console.log('=== ARRAYS MATCH! ===');
    return true;
}

//calls the base chars api and returns result
async function getBaseCharacters (string) {    
    console.log('=== getBaseCharacters called ===');
    console.log('Input string:', string);
    console.log('Current language:', language);
    
    if(string !== "") {
        try {
            const apiUrl = `api.php/characters/base?language=${language}&string=${encodeURIComponent(string)}`;
            console.log('Making API call to:', apiUrl);
            
            const response = await fetch(apiUrl);
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.text();
            console.log('Raw API response:', result);
            
            const newResult = remove_non_ascii(result);
            console.log('Cleaned response:', newResult);
            
            const jsonObj = JSON.parse(newResult);
            console.log('Parsed JSON:', jsonObj);
            
            const baseChars = jsonObj["data"] || [];
            console.log('Extracted base characters:', baseChars);
            
            return baseChars;
        } catch (error) {
            console.error('=== ERROR in getBaseCharacters ===');
            console.error('String:', string);
            console.error('Error:', error);
            console.error('Stack:', error.stack);
            return [];
        }
    }
    console.log('Empty string provided, returning empty array');
    return [];
}

/**
 * Removes NON-ASCII characters from strings
 * taken from index.js
 */
function remove_non_ascii(str) {
    if ((str === null) || (str === ''))
        return false;
    else
        str = str.toString();
    return str.replace(/[^\x20-\x7E\uC00-\u0C7F]/g, '');
}