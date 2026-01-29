window.addEventListener('load', startup);

let language = "telugu";
let table;

function startup() {
    console.log('Analyzer startup initiated...');
    registerSelectLanguage();
    
    // Check if jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }
    
    // Check if DataTable already exists (shouldn't on first load, but just in case)
    if ($.fn.DataTable.isDataTable('#parsed-table')) {
        $('#parsed-table').DataTable().destroy();
    }
    
    try {
        // Enhanced DataTable with search, sort, and export functionality
        table = $('#parsed-table').DataTable({
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[1, 'desc']], // Sort by length column descending
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip<"row"<"col-md-12"B>>',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-1"></i>CSV',
                    className: 'btn btn-success btn-sm me-2',
                    filename: 'analysis-results'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-1"></i>Excel',
                    className: 'btn btn-primary btn-sm me-2',
                    filename: 'analysis-results'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                    className: 'btn btn-danger btn-sm me-2',
                    filename: 'analysis-results',
                    orientation: 'portrait',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1"></i>Print',
                    className: 'btn btn-secondary btn-sm',
                    title: 'Text Analysis Results'
                }
            ],
            columnDefs: [
                { type: 'num', targets: 1, className: 'text-center' }, // Length column is numeric
                { type: 'num', targets: 2, className: 'text-center' }  // Frequency column is numeric
            ],
            language: {
                emptyTable: "Enter text above and click 'Process Text' to see results",
                search: "Search words:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ words",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            searching: true,
            ordering: true,
            paging: true,
            info: true
        });
        console.log('DataTable initialized successfully');
    } catch (error) {
        console.error('Error initializing DataTable:', error);
        // Fallback: just use regular table
        table = null;
    }
}

function registerSelectLanguage() {
    const language_selector = document.querySelector('#language-select');

    if(language_selector) {
        language_selector.addEventListener('change', (e) => updateLanguageSelection(e));
    }
}

function rebuildTable() {
    console.log('Rebuilding table...');
    
    // Check if DataTable exists on this element
    if ($.fn.DataTable.isDataTable('#parsed-table')) {
        try {
            $('#parsed-table').DataTable().clear().destroy();
            console.log('Existing DataTable destroyed');
        } catch (error) {
            console.warn('Error destroying existing DataTable:', error);
        }
    }
    
    // Clear the table body HTML
    $('#parsed-table tbody').empty();
    
    // Reset the table variable
    table = null;
    
    try {
        // Initialize new DataTable
        table = $('#parsed-table').DataTable({
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[1, 'desc']], // Sort by length column descending
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip<"row"<"col-md-12"B>>',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-1"></i>CSV',
                    className: 'btn btn-success btn-sm me-2',
                    filename: 'analysis-results'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-1"></i>Excel',
                    className: 'btn btn-primary btn-sm me-2',
                    filename: 'analysis-results'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                    className: 'btn btn-danger btn-sm me-2',
                    filename: 'analysis-results',
                    orientation: 'portrait',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1"></i>Print',
                    className: 'btn btn-secondary btn-sm',
                    title: 'Text Analysis Results'
                }
            ],
            columnDefs: [
                { type: 'num', targets: 1, className: 'text-center' }, // Length column is numeric
                { type: 'num', targets: 2, className: 'text-center' }  // Frequency column is numeric
            ],
            language: {
                emptyTable: "Enter text above and click 'Process Text' to see results",
                search: "Search words:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ words",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            searching: true,
            ordering: true,
            paging: true,
            info: true
        });
        console.log('Table rebuilt successfully');
    } catch (error) {
        console.error('Error rebuilding DataTable:', error);
        table = null;
    }
}

function updateParseTable() {
    console.log('updateParseTable() called');
    
    const input = document.querySelector('#parsing-input');
    const processButton = document.querySelector('.process-btn');
    
    if (!input) {
        console.error('Input element not found');
        return;
    }
    
    if (!processButton) {
        console.error('Process button not found');
        return;
    }

    const inputValue = input.value.trim();
    console.log('Input value:', inputValue);

    if(inputValue.length > 0) {
        // Show loading state
        processButton.disabled = true;
        processButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        
        console.log('Starting word processing...');
        
        getAnalysisData().then((analysisData) => {
            console.log('Analysis data:', analysisData);
            
            rebuildTable();
            
            if (analysisData.words.length > 0) {
                // Sort by length descending, then by word alphabetically
                analysisData.words.sort((a, b) => {
                    if (b.length !== a.length) {
                        return b.length - a.length; // Sort by length descending
                    }
                    return a.word.localeCompare(b.word); // Then by word alphabetically
                });
                
                for(let wordData of analysisData.words) {
                    if (table) {
                        table.row.add([wordData.word, wordData.length, wordData.frequency]).draw(false);
                    } else {
                        // Fallback: add to table manually
                        const tbody = document.querySelector('#word-entries');
                        const row = tbody.insertRow();
                        row.innerHTML = `<td>${wordData.word}</td><td>${wordData.length}</td><td>${wordData.frequency}</td>`;
                    }
                }
                
                // Redraw the table to apply sorting
                if (table) {
                    table.draw();
                }
            } else {
                if (table) {
                    table.row.add(['No words found', '-', '-']).draw();
                } else {
                    const tbody = document.querySelector('#word-entries');
                    tbody.innerHTML = '<tr><td>No words found</td><td>-</td><td>-</td></tr>';
                }
            }
            
            // Update numerical results display
            updateNumericalDisplay(analysisData.numerical);
            
            // Reset button state
            processButton.disabled = false;
            processButton.innerHTML = '<i class="fas fa-cogs me-2"></i>Process Text';
        }).catch((error) => {
            console.error('Error processing text:', error);
            
            // Reset button state
            processButton.disabled = false;
            processButton.innerHTML = '<i class="fas fa-cogs me-2"></i>Process Text';
            
            // Show error in table
            rebuildTable();
            if (table) {
                table.row.add(['Error processing text', 'Please try again', '-']).draw();
            } else {
                const tbody = document.querySelector('#word-entries');
                tbody.innerHTML = '<tr><td>Error processing text</td><td>Please try again</td><td>-</td></tr>';
            }
        });
    }
    else {
        console.log('No input text provided');
        rebuildTable();
        if (table) {
            table.row.add(['<em>Enter text above to analyze</em>', '-', '-']).draw();
        } else {
            const tbody = document.querySelector('#word-entries');
            tbody.innerHTML = '<tr><td><em>Enter text above to analyze</em></td><td>-</td><td>-</td></tr>';
        }
        
        // Reset numerical results
        const emptyResults = {
            totalWords: 0,
            totalStrength: 0,
            averageStrength: 0,
            totalLetters: 0,
            totalWeight: 0,
            averageWeight: 0,
            totalLines: 0
        };
        updateNumericalDisplay(emptyResults);
    }
}

function updateLanguageSelection(e) {
    const target = e.target;
    const value = target.value;
    const parsingInput = document.querySelector('#parsing-input');

    switch(value) {
        case 'english':
            language = value;
            parsingInput.placeholder = "Enter your English text here for analysis...";
            console.log(language + ' selected');
            break;
        case 'telugu':
            language = value;
            parsingInput.placeholder = "మీ తెలుగు వచనాన్ని ఇక్కడ విశ్లేషణ కోసం నమోదు చేయండి...";
            console.log(language + ' selected');
            break;
        default:
            console.log('Unknown language selected');
    }

    parsingInput.value = "";
    rebuildTable();
    
    // Add placeholder row
    if (table) {
        table.row.add(['<em>Enter text above to analyze</em>', '-', '-']).draw();
    } else {
        const tbody = document.querySelector('#word-entries');
        if (tbody) {
            tbody.innerHTML = '<tr><td><em>Enter text above to analyze</em></td><td>-</td><td>-</td></tr>';
        }
    }
    
    // Reset numerical results when language changes
    const emptyResults = {
        totalWords: 0,
        totalStrength: 0,
        averageStrength: 0,
        totalLetters: 0,
        totalWeight: 0,
        averageWeight: 0,
        totalLines: 0
    };
    updateNumericalDisplay(emptyResults);
}



/**
 * Get complete analysis data (numerical results + word table data) from analyze.php
 */
async function getAnalysisData() {
    const textArea = document.querySelector('#parsing-input');
    const string = textArea.value.trim();
    
    if (!string) {
        return {
            numerical: {
                totalWords: 0,
                totalStrength: 0,
                averageStrength: 0,
                totalLetters: 0,
                totalWeight: 0,
                averageWeight: 0,
                totalLines: 0
            },
            words: []
        };
    }
    
    try {
        // POST to analyze.php for comprehensive text analysis
        const formData = new FormData();
        formData.append('to_parse', string + '\n'); // Adding newline for proper processing
        formData.append('to_language', language);
        
        const response = await fetch('analyze.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('Analyze.php response:', responseText);
        
        // Parse the pipe-separated response from analyze.php
        const parts = responseText.split('|');
        
        if (parts.length >= 7) {
            const totalWords = parseInt(parts[0]) || 0;
            const totalLetters = parseInt(parts[1]) || 0;
            const totalLines = parseInt(parts[2]) || 0;
            const totalStrength = parseFloat(parts[3]) || 0;
            const totalWeight = parseFloat(parts[4]) || 0;
            const averageStrength = parseFloat(parts[5]) || 0;
            const averageWeight = parseFloat(parts[6]) || 0;
            
            // Parse word data from the remaining parts
            const words = [];
            for (let i = 7; i < parts.length; i++) {
                if (parts[i].trim() === '') continue;
                
                const wordParts = parts[i].split(',');
                if (wordParts.length >= 4) {
                    words.push({
                        number: parseInt(wordParts[0]) || 0,
                        word: wordParts[1] || '',
                        length: parseInt(wordParts[2]) || 0,
                        frequency: parseInt(wordParts[3]) || 0
                    });
                }
            }
            
            return {
                numerical: {
                    totalWords: totalWords,
                    totalStrength: totalStrength,
                    averageStrength: averageStrength,
                    totalLetters: totalLetters,
                    totalWeight: totalWeight,
                    averageWeight: averageWeight,
                    totalLines: totalLines
                },
                words: words
            };
        } else {
            throw new Error('Invalid response format from analyze.php');
        }
        
    } catch (error) {
        console.error('Error getting analysis data:', error);
        
        // Fallback calculation
        const words = string.split(/\s+/).filter(word => word.trim() !== '');
        const lines = string.split('\n').length;
        
        return {
            numerical: {
                totalWords: words.length,
                totalStrength: 0,
                averageStrength: 0,
                totalLetters: string.length,
                totalWeight: 0,
                averageWeight: 0,
                totalLines: lines
            },
            words: []
        };
    }
}

/**
 * Calculate numerical results for the input text
 * @deprecated Use getAnalysisData() instead for better performance
 */
async function calculateNumericalResults() {
    const textArea = document.querySelector('#parsing-input');
    const string = textArea.value.trim();
    
    if (!string) {
        return {
            totalWords: 0,
            totalStrength: 0,
            averageStrength: 0,
            totalLetters: 0,
            totalWeight: 0,
            averageWeight: 0,
            totalLines: 0
        };
    }
    
    try {
        // POST to analyze.php for comprehensive text analysis
        const formData = new FormData();
        formData.append('to_parse', string + '\n'); // Adding newline for proper processing
        formData.append('to_language', language);
        
        const response = await fetch('analyze.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('Analyze.php response:', responseText);
        
        // Parse the pipe-separated response from analyze.php
        const parts = responseText.split('|');
        
        if (parts.length >= 7) {
            const totalWords = parseInt(parts[0]) || 0;
            const totalLetters = parseInt(parts[1]) || 0;
            const totalLines = parseInt(parts[2]) || 0;
            const totalStrength = parseFloat(parts[3]) || 0;
            const totalWeight = parseFloat(parts[4]) || 0;
            const averageStrength = parseFloat(parts[5]) || 0;
            const averageWeight = parseFloat(parts[6]) || 0;
            
            return {
                totalWords: totalWords,
                totalStrength: totalStrength,
                averageStrength: averageStrength,
                totalLetters: totalLetters,
                totalWeight: totalWeight,
                averageWeight: averageWeight,
                totalLines: totalLines
            };
        } else {
            throw new Error('Invalid response format from analyze.php');
        }
        
    } catch (error) {
        console.error('Error calculating numerical results:', error);
        
        // Fallback calculation
        const words = string.split(/\s+/).filter(word => word.trim() !== '');
        const lines = string.split('\n').length;
        
        return {
            totalWords: words.length,
            totalStrength: 0,
            averageStrength: 0,
            totalLetters: string.length,
            totalWeight: 0,
            averageWeight: 0,
            totalLines: lines
        };
    }
}



/**
 * Update the numerical results display
 */
function updateNumericalDisplay(results) {
    document.getElementById('total-words').textContent = results.totalWords;
    document.getElementById('total-strength').textContent = results.totalStrength.toFixed(1);
    document.getElementById('average-strength').textContent = results.averageStrength.toFixed(2);
    document.getElementById('total-letters').textContent = results.totalLetters;
    document.getElementById('total-weight').textContent = results.totalWeight.toFixed(1);
    document.getElementById('average-weight').textContent = results.averageWeight.toFixed(2);
    document.getElementById('total-lines').textContent = results.totalLines;
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