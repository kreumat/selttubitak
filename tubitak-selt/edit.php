<?php
/**
 * Board Game Generator - Text Addition Phase (Step 2/5)
 * 
 * Purpose: This file handles the second step in the board game creation process,
 * allowing users to add custom text to each of the active tiles they created in Step 1.
 * It provides functionality to:
 * 1. Display the active grid cells from the previous step
 * 2. Allow text input for each active cell
 * 3. Select text colors from a palette
 * 4. Automatically resize text based on content length
 * 5. Insert random text prompts using dice icons
 * 
 * Workflow Position:
 * - This is STEP 2 in the board creation process
 * - It receives grid data from index.php (STEP 1)
 * - After adding text, users proceed to images.php (STEP 3) to add images
 * - Then to walls.php (STEP 4) to add walls
 * - Then finally to result.php (STEP 5) for the completed board
 * 
 * Data Handling:
 * - Receives grid data via POST from index.php including:
 *   - Grid dimensions (rows, cols)
 *   - Active cell indices and coordinates
 *   - Cell colors
 *   - START/FINISH positions
 * - Adds text content and text colors to this data
 * - Passes the enriched data to walls.php
 */

// Include the chat functionality and random prompts
include 'chat.php';
include 'random_prompts.php';

// Initialize chat and handle requests
$chat = setupChat();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Game Generator - Add Text</title>
    <link rel="icon" type="image/png" href="images/selt_logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php echo $chat['styles']; ?>
    <style>
        /* Dice Icon Styles */
        .dice-icon {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 16px;
            color: #666;
            cursor: pointer;
            z-index: 10;
            background-color: rgba(255, 255, 255, 0.7);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s, color 0.2s, opacity 0.3s ease;
            opacity: 0; /* Initially hidden */
        }
        
        /* Show dice icon on cell hover */
        .cell:hover .dice-icon {
            opacity: 1;
        }
        
        .dice-icon:hover {
            transform: scale(1.2);
            color: #333;
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        /* Typing cursor styles */
        .typing-cursor {
            display: inline-block;
            width: 2px;
            height: 1em;
            background-color: #000;
            animation: blink 0.7s infinite;
            margin-left: 2px;
            vertical-align: middle;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        
        /* Position the dice icon differently in cells with special positioning */
        .cell.start .dice-icon,
        .cell.finish .dice-icon {
            top: auto;
            bottom: 5px;
        }
        
        /* Make sure cell content is positioned relatively so the absolute positioning of the dice works */
        .cell {
            position: relative;
        }
    </style>
</head>
<body>
    <?php echo $chat['ui']; ?>
    <nav class="navbar">
        <div class="navbar-brand">
            <span class="navbar-brand-silver">TUBITAK</span>
            <img src="images/selt_logo.png" alt="SELT Logo" class="navbar-brand-logo">
            <span class="navbar-brand-elt">2209 A</span>
        </div>
        <div class="navbar-nav">
            <a href="index.php" class="navbar-nav-item">Simple Board Game Generator</a>
            <a href="explore.php" class="navbar-nav-item">Explore</a>
            <a href="play_on_screen.php" class="navbar-nav-item">Play on a Screen</a>
        </div>
    </nav>
    
    <!-- Step Progress Bar -->
    <div class="stepper-container">
        <div class="stepper">
            <div class="step completed">
                <div class="step-dot">1</div>
                <div class="step-label">Board Setup</div>
            </div>
            <div class="step active">
                <div class="step-dot">2</div>
                <div class="step-label">Add Text</div>
            </div>
            <div class="step">
                <div class="step-dot">3</div>
                <div class="step-label">Add Images</div>
            </div>
            <div class="step">
                <div class="step-dot">4</div>
                <div class="step-label">Add Walls</div>
            </div>
            <div class="step">
                <div class="step-dot">5</div>
                <div class="step-label">Print Board</div>
            </div>
            <!-- Progress line for completed steps (25% - showing completion of step 1) -->
            <div class="progress-line" style="width: 25%"></div>
        </div>
    </div>
    <div class="page-wrapper">
        <!-- Left information box -->
        <div class="info-box">
            
            <h4>Text Formatting</h4>
            <!-- Text formatting buttons -->
            <div class="text-formatting-controls">
                <button id="decrease-font-size" class="formatting-btn" title="Decrease Font Size">
                    <i class="fas fa-minus"></i> Font Size
                </button>
                <button id="increase-font-size" class="formatting-btn" title="Increase Font Size">
                    <i class="fas fa-plus"></i> Font Size
                </button>
                <button id="toggle-bold" class="formatting-btn" title="Bold Text">
                    <i class="fas fa-bold"></i> Bold
                </button>
            </div>
            
            <h4>Text Colors</h4>
            <!-- Text color palette for selecting text colors -->
            <div class="text-color-palette">
                <div class="text-color-option text-color-yellow" data-text-color="yellow" title="Yellow">A</div>
                <div class="text-color-option text-color-white" data-text-color="white" title="White">A</div>
                <div class="text-color-option text-color-silver" data-text-color="silver" title="Silver">A</div>
                <div class="text-color-option text-color-red" data-text-color="red" title="Red">A</div>
                <div class="text-color-option text-color-blue" data-text-color="blue" title="Blue">A</div>
                <div class="text-color-option text-color-orange" data-text-color="orange" title="Orange">A</div>
                <div class="text-color-option text-color-brown" data-text-color="brown" title="Brown">A</div>
                <div class="text-color-option text-color-green" data-text-color="green" title="Green">A</div>
                <div class="text-color-option text-color-pink" data-text-color="pink" title="Pink">A</div>
                <div class="text-color-option text-color-purple" data-text-color="purple" title="Purple">A</div>
                <div class="text-color-option text-color-grey" data-text-color="grey" title="Grey">A</div>
                <div class="text-color-option text-color-black selected" data-text-color="black" title="Black (Default)">A</div>
            </div>
            
            <h4>Text Preview</h4>
            <div class="tile-preview-container">
                <div class="tile-preview color-red"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-green"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-blue"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-yellow"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-orange"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-brown"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-pink"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-purple"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-grey"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-default"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-cutty-sark"><span class="preview-text text-black">Torun Terzi</span></div>
                <div class="tile-preview color-lightning-yellow"><span class="preview-text text-black">Torun Terzi</span></div>
            </div>
            
        </div>
        
        <!-- Main container with grid and form -->
        <div class="container">
        <h1>Add Text to Your Board</h1>
        <p class="instructions">Enter text into each active cell on your board. The text will automatically resize to fit; however, you can still adjust the font size from the “Text Formatting” menu by highlighting the text.</p>
        
        <?php
        /**
         * Process grid data from previous step (index.php)
         * 
         * Extracts grid dimensions, active cells, coordinates, and cell colors
         * from the posted data and generates a grid with editable text fields.
         */
        if (isset($_POST['gridData'])) {
            // Decode the JSON grid data
            $gridData = json_decode($_POST['gridData'], true);
            $rows = $gridData['rows'];
            $cols = $gridData['cols'];
            $cells = $gridData['cells']; // Linear indices
            
            // Check if we have coordinates in the new format
            $hasCoordinates = isset($gridData['coordinates']);
            $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];
            
            // Initialize cell text colors array if it doesn't exist
            if (!isset($gridData['cellTextColors'])) {
                $gridData['cellTextColors'] = [];
            }
            
            // Check if we're returning from images.php
            $returningFromImages = isset($_POST['returnFromImages']);
            
            // If returning from images and there are images in session, restore them
            if ($returningFromImages && isset($gridData['hasSessionImages']) && $gridData['hasSessionImages']) {
                // Get images from session if available
                if (isset($_SESSION['cellImages'])) {
                    $gridData['cellImages'] = $_SESSION['cellImages'];
                }
            }
            
            // Add a "Go Back" button form that will submit to index.php - must be OUTSIDE the main form
            echo '<form action="index.php" method="post" id="go-back-form" class="go-back-form">';
            echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
            echo '<input type="hidden" name="returnFromEdit" value="true">';
            echo '<input type="hidden" name="cellTextColors" id="cellTextColors-back" value=\'{}\'>';
            echo '<button type="submit" class="back-link">Go back</button>';
            echo '</form>';
            
            // JavaScript to handle the "Go Back" button functionality
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const goBackForm = document.getElementById("go-back-form");
                    const cellTextColorsBackInput = document.getElementById("cellTextColors-back");
                    
                    // Get initial gridData directly from PHP
                    const initialGridData = ' . json_encode($gridData) . ';
                    
                    goBackForm.addEventListener("submit", function(e) {
                        // Get all contenteditable divs
                        const textareas = document.querySelectorAll(".cell-text");
                        
                        // Use existing colors from gridData as base
                        let cellTextColors = {};
                        if (initialGridData && initialGridData.cellTextColors) {
                            cellTextColors = {...initialGridData.cellTextColors};
                        }
                        
                        // Capture text content and colors from each cell
                        textareas.forEach(div => {
                            // Get the coordinate or index key
                            let cellKey;
                            if (div.dataset.x && div.dataset.y) {
                                cellKey = `${div.dataset.x},${div.dataset.y}`;
                            } else if (div.dataset.cellIndex) {
                                cellKey = div.dataset.cellIndex;
                            }
                            
                            if (cellKey) {
                                // Get text color from class
                                const colorClass = Array.from(div.classList)
                                    .find(cls => cls.startsWith("text-"));
                                    
                                if (colorClass) {
                                    const color = colorClass.replace("text-", "");
                                    cellTextColors[cellKey] = color;
                                }
                                
                                // Update hidden form field with cell text content
                                const hiddenInput = document.createElement("input");
                                hiddenInput.type = "hidden";
                                hiddenInput.name = `cell_text[${cellKey}]`;
                                hiddenInput.value = div.innerHTML;
                                goBackForm.appendChild(hiddenInput);
                            }
                        });
                        
                        // Update the hidden input with cell text colors
                        cellTextColorsBackInput.value = JSON.stringify(cellTextColors);
                        
                        // Capture and add any other state data needed
                        const gridDataInput = goBackForm.querySelector("input[name=\'gridData\']");
                        if (gridDataInput) {
                            try {
                                const gridData = JSON.parse(gridDataInput.value);
                                gridData.cellTextColors = cellTextColors;
                                
                                // Add text content to grid data
                                gridData.cellTextContent = {};
                                textareas.forEach(div => {
                                    let cellKey;
                                    if (div.dataset.x && div.dataset.y) {
                                        cellKey = `${div.dataset.x},${div.dataset.y}`;
                                    } else if (div.dataset.cellIndex) {
                                        cellKey = div.dataset.cellIndex;
                                    }
                                    
                                    if (cellKey && div.innerHTML.trim() !== "") {
                                        gridData.cellTextContent[cellKey] = div.innerHTML;
                                    }
                                });
                                
                                gridDataInput.value = JSON.stringify(gridData);
                            } catch (error) {
                                console.error("Error updating gridData:", error);
                            }
                        }
                    });
                });
            </script>';
            
            // Create the form to be submitted to images.php (the new step 3)
            echo '<form action="images.php" method="post" id="text-form">';
            echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
            echo '<input type="hidden" name="cellTextColors" id="cellTextColors" value=\'{}\'>';
            
            echo '<div class="grid-container">';
            echo '<div class="result-grid editable-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';
            
            // Helper function to check if a coordinate is in the coordinates array
            function isCoordinateActive($x, $y, $coordinates) {
                foreach ($coordinates as $coord) {
                    if ($coord[0] === $x && $coord[1] === $y) {
                        return true;
                    }
                }
                return false;
            }
            
            // Get custom Start/Finish positions
            $startPositions = isset($gridData['startPositions']) ? $gridData['startPositions'] : [];
            $finishPositions = isset($gridData['finishPositions']) ? $gridData['finishPositions'] : [];
            
            // Generate grid cells from the data
            for ($i = 0; $i < $rows; $i++) {
                for ($j = 0; $j < $cols; $j++) {
                    $cellIndex = $i * $cols + $j;
                    $cellClass = '';
                    $cellContent = '';
                    $isEditable = false;
                    
                    // Convert to 1-based coordinates
                    $x = $j + 1;
                    $y = $i + 1;
                    
                    // Check if this is a Start position
                    $isStart = false;
                    foreach ($startPositions as $startPos) {
                        if ($startPos[0] === $x && $startPos[1] === $y) {
                            $isStart = true;
                            break;
                        }
                    }
                    
                    // Check if this is a Finish position
                    $isFinish = false;
                    foreach ($finishPositions as $finishPos) {
                        if ($finishPos[0] === $x && $finishPos[1] === $y) {
                            $isFinish = true;
                            break;
                        }
                    }
                    
                    // Handle different cell types - Start, Finish, or regular active
                    // Start cell
                    if ($isStart) {
                        $cellClass = 'start active';
                        $cellContent = 'START';
                    }
                    // Finish cell
                    elseif ($isFinish) {
                        $cellClass = 'finish active';
                        $cellContent = 'FINISH';
                    }
                    // Regular active cell - check both old and new formats
                    elseif (in_array($cellIndex, $cells) || 
                          ($hasCoordinates && isCoordinateActive($x, $y, $coordinates))) {
                        $cellClass = 'active editable-cell';
                        $isEditable = true;
                        
                        // Apply color class if available
                        if (isset($gridData['cellColors'])) {
                            $coordKey = "{$x},{$y}";
                            if (isset($gridData['cellColors'][$coordKey])) {
                                $colorClass = 'color-' . $gridData['cellColors'][$coordKey];
                                $cellClass .= ' ' . $colorClass;
                            }
                        }
                    }
                    
                    echo '<div class="cell ' . $cellClass . '" data-x="' . $x . '" data-y="' . $y . '">';
                    
                    if ($isEditable) {
                        // Use coordinate as the key if available, otherwise use index
                        $textFieldName = $hasCoordinates ? "cell_text[{$x},{$y}]" : "cell_text[{$cellIndex}]";
                        $textFieldData = $hasCoordinates ? "data-x=\"{$x}\" data-y=\"{$y}\"" : "data-cell-index=\"{$cellIndex}\"";
                        
                        // Define the coordinate key for this cell
                        $coordKey = "{$x},{$y}";
                        
                        // Check if we're returning from images.php and have cell content
                        $existingContent = '';
                        $textColorClass = '';
                        
                        if ($returningFromImages && isset($_POST['cell_text'][$coordKey])) {
                            $existingContent = $_POST['cell_text'][$coordKey];
                        }
                        
                        // Get text color if available in gridData
                        if (isset($gridData['cellTextColors'][$coordKey])) {
                            $textColorClass = 'text-' . $gridData['cellTextColors'][$coordKey];
                        }
                        
                        // Use contenteditable div instead of textarea to support rich text formatting
                        echo '<div class="cell-text ' . $textColorClass . '" contenteditable="true" data-name="' . $textFieldName . '" ' . $textFieldData . '" style="overflow: hidden;">' . $existingContent . '</div>';
                        // Hidden input to store the HTML content of the contenteditable div
                        echo '<input type="hidden" name="' . $textFieldName . '" class="cell-text-input" value="' . htmlspecialchars($existingContent, ENT_QUOTES, 'UTF-8', false) . '">';
                        
                        // Add dice icon for random text insertion
                        echo '<div class="dice-icon" title="Insert Random Text" data-cell-key="' . $coordKey . '"><i class="fas fa-dice"></i></div>';
                    } else {
                        echo $cellContent;
                    }
                    
                    echo '</div>';
                }
            }
            
            echo '</div>';
            echo '</div>';
            
            echo '<input type="submit" value="Go to the Next Step: Add Images" class="submit-btn">';
            echo '</form>';
            
        } else {
            // Handle case when no grid data is provided
            echo '<p>No grid data found. Please go back and create a board.</p>';
            echo '<a href="index.php" class="back-link">Go Back</a>';
        }
        ?>
        
        <script>
            /**
             * Text Formatting Scripts
             * 
             * This section handles:
             * 1. Text color selection 
             * 2. Dynamic font resizing based on content length
             * 3. Storing and tracking text colors for each cell
             * 4. Form submission handling to pass data to next step
             */
            
            // Text color handling
            let currentTextColor = 'black'; // Default text color
            let cellTextColors = {}; // Store text colors for each cell
            
            // Text selection tracking
            let currentSelection = {
                textarea: null,
                start: 0,
                end: 0,
                text: ''
            };
            
            // Initialize cellTextColors from gridData if colors exist
            const gridDataJson = <?php echo json_encode($gridData ?? []); ?>;
            if (gridDataJson && gridDataJson.cellTextColors) {
                cellTextColors = {...gridDataJson.cellTextColors};
                console.log("Loaded text colors from gridData:", cellTextColors);
            }
            
            // Get random prompts from PHP
            const randomPrompts = <?php echo getAllPromptsJson(); ?>;
            console.log("Loaded random prompts:", randomPrompts);
            
            // Function to update the hidden input with cell text colors
            function updateCellTextColorsInput() {
                document.getElementById('cellTextColors').value = JSON.stringify(cellTextColors);
            }
            
            // Function to apply text colors from cellTextColors to DOM elements
            function applyTextColorsFromData() {
                console.log("Applying text colors from data to DOM");
                document.querySelectorAll('.cell-text').forEach(cell => {
                    let cellKey;
                    if (cell.dataset.x && cell.dataset.y) {
                        cellKey = `${cell.dataset.x},${cell.dataset.y}`;
                    } else if (cell.dataset.cellIndex) {
                        cellKey = cell.dataset.cellIndex;
                    }
                    
                    if (cellKey && cellTextColors[cellKey]) {
                        // Remove any existing text color classes
                        const colorClasses = Array.from(cell.classList)
                            .filter(cls => cls.startsWith('text-'));
                        colorClasses.forEach(cls => {
                            cell.classList.remove(cls);
                        });
                        
                        // Apply the color from cellTextColors
                        cell.classList.add(`text-${cellTextColors[cellKey]}`);
                    }
                });
            }
            // Set up text color palette event listeners
            document.addEventListener('DOMContentLoaded', function() {
                // Apply text colors from data immediately when DOM is loaded
                applyTextColorsFromData();
                const textColorOptions = document.querySelectorAll('.text-color-option');
                const textareas = document.querySelectorAll('.cell-text');
                const form = document.getElementById('text-form');
                
                // Function to calculate percentage of uppercase characters
                function getUppercasePercentage(text) {
                    if (!text || text.length === 0) return 0;
                    
                    const uppercaseChars = text.replace(/[^A-Z]/g, '').length;
                    return (uppercaseChars / text.length) * 100;
                }
                
                // Save current selection
                function saveSelection() {
                    currentSelection.range = null;
                    
                    if (window.getSelection) {
                        const sel = window.getSelection();
                        if (sel.getRangeAt && sel.rangeCount) {
                            currentSelection.range = sel.getRangeAt(0);
                            
                            // Find parent contenteditable div
                            let node = sel.anchorNode;
                            while (node && (!node.classList || !node.classList.contains('cell-text'))) {
                                node = node.parentNode;
                            }
                            
                            if (node && node.classList.contains('cell-text')) {
                                currentSelection.textarea = node;
                            } else {
                                currentSelection.textarea = null;
                            }
                        }
                    }
                }
                
                // Apply formatting to the selected text
                function applyFormatting(formattingAction) {
                    if (!currentSelection.textarea) return;
                    
                    // Check if there's a valid selection
                    const hasSelection = currentSelection.range && 
                                         currentSelection.range.toString().trim() !== '';
                    
                    if (!hasSelection) {
                        // No selection - select all text in the cell for formatting
                        const range = document.createRange();
                        range.selectNodeContents(currentSelection.textarea);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                        currentSelection.range = range;
                    } else {
                        // Restore existing selection
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(currentSelection.range);
                    }
                    
                    // Apply formatting using execCommand
                    switch (formattingAction) {
                        case 'increase-font':
                            // Get current selection
                            const selectedText = currentSelection.range.toString();
                            if (!selectedText) return;
                            
                            // Get current selection and check if it's within a span (for nested formatting)
                            const range = currentSelection.range;
                            let currentSize = parseInt(getComputedStyle(currentSelection.textarea).fontSize);
                            
                            // Check if selection is inside an existing font-sized span
                            let parentSpan = null;
                            let node = range.commonAncestorContainer;
                            while (node && node !== currentSelection.textarea) {
                                if (node.nodeType === 1 && node.tagName === 'SPAN' && node.style.fontSize) {
                                    parentSpan = node;
                                    // Get the actual size from the span
                                    currentSize = parseInt(window.getComputedStyle(parentSpan).fontSize);
                                    break;
                                }
                                node = node.parentNode;
                            }
                            
                            // Calculate new size (20% increase, for smoother continuous scaling)
                            // Ensure a minimum size for visibility (larger minimum for small text)
                            const newSize = Math.max(Math.round(currentSize * 1.2), 8);
                            
                            // Create span with increased font size
                            const tempSpan = document.createElement('span');
                            tempSpan.style.fontSize = `${newSize}px`;
                            
                            // Replace selection with formatted span
                            range.deleteContents();
                            tempSpan.appendChild(document.createTextNode(selectedText));
                            range.insertNode(tempSpan);
                            
                            // Update selection
                            range.selectNodeContents(tempSpan);
                            break;
                            
                        case 'decrease-font':
                            // Get current selection
                            const selectedText2 = currentSelection.range.toString();
                            if (!selectedText2) return;
                            
                            // Get current selection and check if it's within a span (for nested formatting)
                            const range2 = currentSelection.range;
                            let currentSize2 = parseInt(getComputedStyle(currentSelection.textarea).fontSize);
                            
                            // Check if selection is inside an existing font-sized span
                            let parentSpan2 = null;
                            let node2 = range2.commonAncestorContainer;
                            while (node2 && node2 !== currentSelection.textarea) {
                                if (node2.nodeType === 1 && node2.tagName === 'SPAN' && node2.style.fontSize) {
                                    parentSpan2 = node2;
                                    // Get the actual size from the span
                                    currentSize2 = parseInt(window.getComputedStyle(parentSpan2).fontSize);
                                    break;
                                }
                                node2 = node2.parentNode;
                            }
                            
                            // Calculate new size (17% decrease, for smoother continuous scaling)
                            // Set minimum size of 6px (prevents text from becoming too small to select)
                            const newSize2 = Math.max(Math.round(currentSize2 * 0.83), 6);
                            
                            // Create span with decreased font size
                            const tempSpan2 = document.createElement('span');
                            tempSpan2.style.fontSize = `${newSize2}px`;
                            
                            // Replace selection with formatted span
                            range2.deleteContents();
                            tempSpan2.appendChild(document.createTextNode(selectedText2));
                            range2.insertNode(tempSpan2);
                            
                            // Update selection
                            range2.selectNodeContents(tempSpan2);
                            break;
                            
                        case 'toggle-bold':
                            document.execCommand('bold', false, null);
                            break;
                    }
                    
                    // Update the hidden input with the formatted content
                    updateHiddenInput(currentSelection.textarea);
                    
                    // Adjust cell sizing
                    adjustFontSize(currentSelection.textarea);
                }
                
                // Update hidden input with formatted content
                function updateHiddenInput(cell) {
                    // Find the corresponding hidden input
                    const cellName = cell.dataset.name;
                    const hiddenInput = document.querySelector(`input[name="${cellName}"]`);
                    if (hiddenInput) {
                        hiddenInput.value = cell.innerHTML;
                    }
                }
                
                // Enhanced function to adjust font size considering uppercase characters
                function adjustFontSize(textarea) {
                    // Default starting font size
                    textarea.style.fontSize = '18px';
                    
                    const text = textarea.textContent || '';
                    const textLength = text.length;
                    const uppercasePercentage = getUppercasePercentage(text);
                    const lineCount = (text.match(/\n/g) || []).length + 1;
                    
                    // Base font size on text length
                    let fontSize = 18;
                    if (textLength > 40) {
                        fontSize = 12;
                    } else if (textLength > 30) {
                        fontSize = 14;
                    } else if (textLength > 20) {
                        fontSize = 16;
                    } else if (textLength > 10) {
                        fontSize = 17;
                    }
                    
                    // Further reduce font size based on uppercase percentage
                    if (uppercasePercentage > 50) {
                        fontSize = Math.max(fontSize - 2, 8); // Reduce by 2px but not below 8px
                    } else if (uppercasePercentage > 30) {
                        fontSize = Math.max(fontSize - 1, 8); // Reduce by 1px but not below 8px
                    }
                    
                    // Apply the calculated font size
                    textarea.style.fontSize = `${fontSize}px`;
                    
                    // Force word wrapping to prevent horizontal overflow
                    textarea.style.wordBreak = 'break-word';
                    
                    // Check for overflow and apply centering after font size is applied
                    setTimeout(() => {
                        const contentHeight = textarea.scrollHeight - 
                            (parseInt(getComputedStyle(textarea).paddingTop) + 
                             parseInt(getComputedStyle(textarea).paddingBottom));
                        const cellHeight = textarea.offsetHeight;
                        
                        // If content still doesn't fit, reduce font size more
                        if (contentHeight > cellHeight) {
                            const newFontSize = Math.max(fontSize - 2, 8); // Reduce by 2px more
                            textarea.style.fontSize = `${newFontSize}px`;
                            
                            // Minimal padding for overflow cases
                            textarea.style.paddingTop = '5px';
                            textarea.style.paddingBottom = '5px';
                        } else {
                            // Apply centering if content fits
                            const paddingVal = Math.max((cellHeight - contentHeight) / 2, 5);
                            textarea.style.paddingTop = `${paddingVal}px`;
                            textarea.style.paddingBottom = `${paddingVal}px`;
                        }
                    }, 0);
                }
                
                // Function to center textarea content
                function centerTextarea(textarea) {
                    // Set perfect vertical centering for empty or newly created textareas
                    const cellHeight = textarea.offsetHeight;
                    
                    // Reset font size to default when a cell is emptied
                    textarea.style.fontSize = '18px';
                    
                    // For empty textarea, we want the cursor to be exactly in the middle
                    textarea.style.paddingTop = (cellHeight / 2 - 9) + 'px'; // 9px accounts for approximate line height/2
                    textarea.style.paddingBottom = (cellHeight / 2 - 9) + 'px';
                    
                    // If there's content, adjust based on content
                    if ((textarea.textContent || '').length > 0) {
                        adjustFontSize(textarea);
                    }
                }
                
                // Initialize textareas
                textareas.forEach(textarea => {
                    // Apply perfect initial centering
                    centerTextarea(textarea);
                    
                    // Add input event listener to adjust sizing and handle empty text
                    textarea.addEventListener('input', function() {
                        // If text is completely empty, recenter the cursor
                        if (this.textContent.trim() === '') {
                            centerTextarea(this);
                        } else {
                            adjustFontSize(this);
                        }
                    });
                    
                    // Track text selection
                    textarea.addEventListener('mouseup', function() {
                        // Save the current selection
                        saveSelection();
                    });
                    
                    textarea.addEventListener('keyup', function(e) {
                        // Only save selection on navigation keys
                        if (e.key.startsWith('Arrow') || e.key === 'Home' || e.key === 'End' || 
                            e.key === 'PageUp' || e.key === 'PageDown' || e.key === 'Shift') {
                            saveSelection();
                        }
                    });
                    
                    // Focus event to apply the current text color
                    textarea.addEventListener('focus', function() {
                        // Remove any existing text color classes
                        const colorClasses = Array.from(this.classList)
                            .filter(cls => cls.startsWith('text-'));
                        colorClasses.forEach(cls => {
                            this.classList.remove(cls);
                        });
                        
                        // Apply the current text color class
                        this.classList.add(`text-${currentTextColor}`);
                        
                        // Update the color map
                        let cellKey;
                        if (this.dataset.x && this.dataset.y) {
                            cellKey = `${this.dataset.x},${this.dataset.y}`;
                        } else if (this.dataset.cellIndex) {
                            cellKey = this.dataset.cellIndex;
                        }
                        
                        if (cellKey) {
                            cellTextColors[cellKey] = currentTextColor;
                            updateCellTextColorsInput();
                        }
                    });
                });
                
                // Set up formatting button event listeners
                const decreaseFontBtn = document.getElementById('decrease-font-size');
                const increaseFontBtn = document.getElementById('increase-font-size');
                const toggleBoldBtn = document.getElementById('toggle-bold');
                
                decreaseFontBtn.addEventListener('click', function() {
                    applyFormatting('decrease-font');
                });
                
                increaseFontBtn.addEventListener('click', function() {
                    applyFormatting('increase-font');
                });
                
                toggleBoldBtn.addEventListener('click', function() {
                    applyFormatting('toggle-bold');
                });
                
                // Text color palette click handlers
                textColorOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        // Remove selected class from all options
                        textColorOptions.forEach(opt => opt.classList.remove('selected'));
                        
                        // Add selected class to clicked option
                        this.classList.add('selected');
                        
                        // Update current text color
                        currentTextColor = this.dataset.textColor;
                        
                        // Update all preview texts with the new color
                        const previewTexts = document.querySelectorAll('.preview-text');
                        previewTexts.forEach(previewText => {
                            // Remove any existing text color classes
                            const colorClasses = Array.from(previewText.classList)
                                .filter(cls => cls.startsWith('text-'));
                            colorClasses.forEach(cls => {
                                previewText.classList.remove(cls);
                            });
                            
                            // Apply the new text color class
                            previewText.classList.add(`text-${currentTextColor}`);
                        });
                        
                        // If there's a currently focused textarea, update its color
                        const focusedTextarea = document.activeElement;
                        if (focusedTextarea && focusedTextarea.classList.contains('cell-text')) {
                            // Remove any existing text color classes
                            const colorClasses = Array.from(focusedTextarea.classList)
                                .filter(cls => cls.startsWith('text-'));
                            colorClasses.forEach(cls => {
                                focusedTextarea.classList.remove(cls);
                            });
                            
                            // Apply the new text color class
                            focusedTextarea.classList.add(`text-${currentTextColor}`);
                            
                            // Update the color map
                            let cellKey;
                            if (focusedTextarea.dataset.x && focusedTextarea.dataset.y) {
                                cellKey = `${focusedTextarea.dataset.x},${focusedTextarea.dataset.y}`;
                            } else if (focusedTextarea.dataset.cellIndex) {
                                cellKey = focusedTextarea.dataset.cellIndex;
                            }
                            
                            if (cellKey) {
                                cellTextColors[cellKey] = currentTextColor;
                                updateCellTextColorsInput();
                            }
                        }
                    });
                });
                
                // Enhanced typing animation with centered text and proper cursor positioning
                function typeText(element, text, index = 0, typingSpeed = 60) {
                    // If we've reached the end of the text, finish the animation
                    if (index >= text.length) {
                        // Remove the cursor if it exists
                        const cursor = element.querySelector('.typing-cursor');
                        if (cursor) cursor.remove();
                        
                        // Ensure the hidden input is updated with the final content
                        updateHiddenInput(element);
                        
                        // Remove all custom styling set for typing animation
                        element.style.removeProperty('fontSize');
                        element.style.removeProperty('paddingTop');
                        element.style.removeProperty('paddingBottom');
                        element.style.removeProperty('paddingLeft');
                        element.style.removeProperty('paddingRight');
                        element.style.removeProperty('textAlign');
                        element.style.removeProperty('display');
                        element.style.removeProperty('flexDirection');
                        element.style.removeProperty('justifyContent');
                        element.style.removeProperty('alignItems');
                        element.style.removeProperty('height');
                        
                        // Center the text and adjust font size based on content length
                        element.style.fontSize = '18px';
                        centerTextarea(element);
                        adjustFontSize(element);
                        
                        // Re-enable any disabled dice icons and restore hover behavior
                        const diceIcons = document.querySelectorAll('.dice-icon.disabled');
                        diceIcons.forEach(dice => {
                            dice.classList.remove('disabled');
                            dice.style.removeProperty('opacity');
                            dice.style.removeProperty('pointer-events');
                        });
                        
                        return;
                    }
                    
                    // Get current text without cursor
                    let currentText = '';
                    Array.from(element.childNodes).forEach(node => {
                        if (node.nodeType === Node.TEXT_NODE) {
                            currentText += node.nodeValue;
                        } else if (node.nodeType === Node.ELEMENT_NODE && 
                                  !node.classList.contains('typing-cursor')) {
                            currentText += node.textContent;
                        }
                    });
                    
                    // Add the next character
                    const newChar = text.charAt(index);
                    currentText += newChar;
                    
                    // Clear the element and rebuild it with proper structure for cursor positioning
                    element.innerHTML = '';
                    
                    // Create a wrapper for the text content to maintain proper flow with line breaks
                    const textNode = document.createTextNode(currentText);
                    element.appendChild(textNode);
                    
                    // Create and add cursor directly after the text
                    const cursor = document.createElement('span');
                    cursor.className = 'typing-cursor';
                    element.appendChild(cursor);
                    
                    // Vary the typing speed slightly to make it look more natural (+/- 15ms)
                    const variableSpeed = typingSpeed + (Math.random() * 30 - 15);
                    
                    // Schedule the next character
                    setTimeout(() => {
                        typeText(element, text, index + 1, typingSpeed);
                    }, variableSpeed);
                }
                
                // Handle dice icon clicks for random prompt insertion
                const diceIcons = document.querySelectorAll('.dice-icon');
                diceIcons.forEach(dice => {
                    dice.addEventListener('click', function(e) {
                        // Prevent multiple clicks during animation
                        if (this.classList.contains('disabled')) {
                            return;
                        }
                        
                        // Disable this dice icon during animation
                        this.classList.add('disabled');
                        this.style.opacity = '0.5';
                        this.style.pointerEvents = 'none';
                        
                        // Get the cell key to identify the associated text field
                        const cellKey = this.dataset.cellKey;
                        if (!cellKey) return;
                        
                        // Find the associated text field
                        const cellText = this.parentNode.querySelector('.cell-text');
                        if (!cellText) return;
                        
                        // Select a random prompt
                        const randomIndex = Math.floor(Math.random() * randomPrompts.length);
                        const randomPrompt = randomPrompts[randomIndex];
                        
                        // Set up styling for centered text with small font during typing
                        cellText.style.fontSize = '12px';
                        cellText.style.textAlign = 'center';
                        cellText.style.padding = '5px';
                        
                        // Clear the current text to prepare for typing animation
                        cellText.textContent = '';
                        const cursor = document.createElement('span');
                        cursor.className = 'typing-cursor';
                        cellText.appendChild(cursor);
                        
                        // Apply current text color before starting the animation
                        const colorClasses = Array.from(cellText.classList)
                            .filter(cls => cls.startsWith('text-'));
                        colorClasses.forEach(cls => {
                            cellText.classList.remove(cls);
                        });
                        cellText.classList.add(`text-${currentTextColor}`);
                        
                        // Update the color map
                        cellTextColors[cellKey] = currentTextColor;
                        updateCellTextColorsInput();
                        
                        // Add a short delay before starting the typing animation
                        setTimeout(() => {
                            // Clear the initial cursor
                            cellText.innerHTML = '';
                            // Start the typing animation
                            typeText(cellText, randomPrompt);
                        }, 300); // 300ms delay for dramatic effect
                        
                        // Prevent the event from bubbling up to parent elements
                        e.stopPropagation();
                    });
                });
                
                // Function to collect all text colors from the DOM
                function collectAllTextColors() {
                    const allCells = document.querySelectorAll('.cell-text');
                    allCells.forEach(cell => {
                        let cellKey;
                        if (cell.dataset.x && cell.dataset.y) {
                            cellKey = `${cell.dataset.x},${cell.dataset.y}`;
                        } else if (cell.dataset.cellIndex) {
                            cellKey = cell.dataset.cellIndex;
                        }
                        
                        if (cellKey) {
                            const colorClasses = Array.from(cell.classList)
                                .filter(cls => cls.startsWith('text-'));
                            if (colorClasses.length > 0) {
                                const colorClass = colorClasses[0];
                                const color = colorClass.replace('text-', '');
                                cellTextColors[cellKey] = color;
                            }
                        }
                    });
                }
                
                // Form submission handler to include text colors and formatted content
                form.addEventListener('submit', function(e) {
                    // Collect all text colors from the DOM before submission
                    collectAllTextColors();
                    
                    // Update the hidden input with the final cell text colors
                    updateCellTextColorsInput();
                    
                    // Update all hidden inputs with the current HTML content from contenteditable divs
                    textareas.forEach(div => {
                        // Get the name of the corresponding hidden input
                        const cellName = div.dataset.name;
                        const hiddenInput = document.querySelector(`input[name="${cellName}"]`);
                        if (hiddenInput) {
                            hiddenInput.value = div.innerHTML;
                        }
                    });
                    
                    // Add explicit form fields for cell text colors
                    // First remove any existing color fields to avoid duplicates
                    document.querySelectorAll('input[name^="cell_color["]').forEach(input => input.remove());
                    
                    // Add a hidden input for each cell's text color
                    for (const [cellKey, color] of Object.entries(cellTextColors)) {
                        const colorInput = document.createElement('input');
                        colorInput.type = 'hidden';
                        colorInput.name = `cell_color[${cellKey}]`;
                        colorInput.value = color;
                        form.appendChild(colorInput);
                    }
                    
                    // Merge cellTextColors into gridData
                    const gridDataInput = document.querySelector('input[name="gridData"]');
                    if (gridDataInput) {
                        try {
                            const gridData = JSON.parse(gridDataInput.value);
                            gridData.cellTextColors = cellTextColors;
                            gridDataInput.value = JSON.stringify(gridData);
                        } catch (error) {
                            console.error('Error updating gridData with text colors:', error);
                        }
                    }
                });
            });
        </script>
        </div>
        
        <!-- Right information box with Contact Us Image -->
        <div class="info-box team-info-box">
            <a href="about.php">
                <img src="images/contact_us.png" alt="Contact Us" style="width: 100%; height: auto; border: 3px solid #e5e7eb; border-radius: 8px;">
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-nav">
            <a href="main.php" class="footer-nav-item">Main Page</a>
            <a href="index.php" class="footer-nav-item">Board Game Generator</a>
			<a href="explore.php" class="footer-nav-item">Explore</a>
            <a href="about.php" class="footer-nav-item">About Us</a>
            <a href="play_on_screen.php" class="footer-nav-item">Play on a Screen</a>
        </div>
        <div class="footer-copyright">
            &copy; <?php echo date('Y'); ?> SILVER ELT. All rights reserved.
        </div>
    </footer>
    
    <?php echo $chat['script']; ?>
</body>
</html>
