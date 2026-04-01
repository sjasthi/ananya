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
    finderMatches.value = "Starting search...\n";
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
        finderMatches.value += "Getting base characters for search term...\n";
        
        console.log('=== CALLING API FOR SEARCH TERM ===');
        const normalizedInputTerm = normalizeForComparison(sanitizeWord(inputTextValue));
        const inputTextBaseCharacters = await getBaseCharacters(normalizedInputTerm);
        
        console.log('=== SEARCH TERM RESULTS ===');
        console.log('Base characters received:', inputTextBaseCharacters);
        console.log('Type:', typeof inputTextBaseCharacters);
        console.log('Is array:', Array.isArray(inputTextBaseCharacters));
        
        finderMatches.value += `Search term base characters: [${inputTextBaseCharacters.join(', ')}]\n`;
        finderMatches.value += `Count: ${inputTextBaseCharacters.length}\n\n`;

        if (!inputTextBaseCharacters || inputTextBaseCharacters.length === 0) {
            finderMatches.value += "ERROR: Could not get base characters for search term!\n";
            finderMatches.value += "This might be an API issue or the word is not recognized.\n";
            updateStatus("Error: Could not process search term.");
            processButton.disabled = false;
            processButton.textContent = "Process";
            return;
        }

        // Split text into words and count occurrences for display.
        const words = textToCompareValue.split(/\s+/).filter(word => word.trim() !== '');
        console.log('=== WORD PROCESSING ===');
        console.log('Total words found:', words.length);
        console.log('First 10 words:', words.slice(0, 10));
        
        const occurrenceMap = new Map();
        const positionMap = new Map();
        const displayWordMap = new Map();
        for (let index = 0; index < words.length; index++) {
            const cleanWord = sanitizeWord(words[index]);
            const normalizedWord = normalizeForComparison(cleanWord);
            if (normalizedWord.length > 1) {
                occurrenceMap.set(normalizedWord, (occurrenceMap.get(normalizedWord) || 0) + 1);
                if (!positionMap.has(normalizedWord)) {
                    positionMap.set(normalizedWord, []);
                }
                if (!displayWordMap.has(normalizedWord)) {
                    displayWordMap.set(normalizedWord, cleanWord);
                }
                positionMap.get(normalizedWord).push(index + 1);
            }
        }

        const uniqueWords = Array.from(occurrenceMap.keys());
        finderMatches.value += `Processing ${uniqueWords.length} unique words...\n`;
        updateStatus(`Searching through ${uniqueWords.length} unique words...`);

        const baseCharCache = new Map();
        const matchingUniqueWords = [];
        const inputSignature = getArraySignature(inputTextBaseCharacters);
        const CHUNK_SIZE = 6;
        const CHUNK_PAUSE_MS = 40;
        let processedUniqueWords = 0;

        for (let i = 0; i < uniqueWords.length; i += CHUNK_SIZE) {
            const chunk = uniqueWords.slice(i, i + CHUNK_SIZE);

            const chunkResults = await Promise.all(
                chunk.map(async (word) => {
                    const baseChars = await getBaseCharactersCached(word, baseCharCache);
                    const isMatch = getArraySignature(baseChars) === inputSignature;
                    return { word, isMatch };
                })
            );

            for (const result of chunkResults) {
                if (result.isMatch) {
                    matchingUniqueWords.push(result.word);
                }
            }

            processedUniqueWords += chunk.length;
            updateStatus(`Processed ${processedUniqueWords}/${uniqueWords.length} unique words... Found ${matchingUniqueWords.length} unique matches.`);

            // Brief pause between chunks to avoid overloading the backend.
            if (i + CHUNK_SIZE < uniqueWords.length) {
                await delay(CHUNK_PAUSE_MS);
            }
        }

        if (matchingUniqueWords.length === 0) {
            finderMatches.value = "No matches found. Try a different search term.";
            updateStatus("Search completed - no matches found.");
        } else {
            matchingUniqueWords.sort((a, b) => (occurrenceMap.get(b) || 0) - (occurrenceMap.get(a) || 0));

            let totalMatches = 0;
            const lines = [
                `Found ${matchingUniqueWords.length} unique matching words.`,
                ""
            ];

            for (const word of matchingUniqueWords) {
                const count = occurrenceMap.get(word) || 0;
                const positions = positionMap.get(word) || [];
                const displayWord = displayWordMap.get(word) || word;
                totalMatches += count;
                lines.push(`${displayWord} (occurrences: ${count})`);
                lines.push(`${formatWordPositions(positions)}`);
                lines.push("");
            }

            lines.push("");
            lines.push(`Total matching occurrences: ${totalMatches}`);
            finderMatches.value = lines.join("\n");

            updateStatus(`Search completed - found ${totalMatches} matching occurrences across ${matchingUniqueWords.length} unique words.`);
        }
        
    } catch (error) {
        console.error('Error during processing:', error);
        finderMatches.value = "Error occurred during processing. Check console for details.";
        updateStatus("Error occurred during processing.");
    }
    
    processButton.disabled = false;
    processButton.textContent = "Process";
})

function sanitizeWord(word) {
    return word.replace(/[^\u0900-\u097F\u0A80-\u0AFF\u0C00-\u0C7F\u0D00-\u0D7F\u0020-\u007E]/g, '').trim();
}

function normalizeForComparison(word) {
    return word.toLocaleLowerCase();
}

function toOrdinal(number) {
    const mod10 = number % 10;
    const mod100 = number % 100;

    if (mod10 === 1 && mod100 !== 11) {
        return `${number}st`;
    }
    if (mod10 === 2 && mod100 !== 12) {
        return `${number}nd`;
    }
    if (mod10 === 3 && mod100 !== 13) {
        return `${number}rd`;
    }
    return `${number}th`;
}

function formatWordPositions(positions) {
    if (!positions || positions.length === 0) {
        return "Position in entry unavailable.";
    }

    const ordinalPositions = positions.map(position => toOrdinal(position));

    if (ordinalPositions.length === 1) {
        return `At the ${ordinalPositions[0]} word in the entry.`;
    }

    if (ordinalPositions.length === 2) {
        return `At the ${ordinalPositions[0]} and ${ordinalPositions[1]} words in the entry.`;
    }

    const allButLast = ordinalPositions.slice(0, -1).join(', ');
    const last = ordinalPositions[ordinalPositions.length - 1];
    return `At the ${allButLast}, and ${last} words in the entry.`;
}

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function getArraySignature(array) {
    if (!Array.isArray(array) || array.length === 0) {
        return "";
    }

    return [...array].sort().join('|');
}

async function getBaseCharactersCached(string, cache) {
    if (cache.has(string)) {
        return cache.get(string);
    }

    const baseChars = await getBaseCharacters(string);
    cache.set(string, baseChars);
    return baseChars;
}

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
    return str.replace(/[^\x20-\x7E\u0900-\u097F\u0A80-\u0AFF\u0C00-\u0C7F\u0D00-\u0D7F]/g, '');
}