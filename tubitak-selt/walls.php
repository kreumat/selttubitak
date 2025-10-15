<?php
// Start session to access image data
session_start();

/**
 * Board Game Generator - Wall Addition Phase (Step 4/5)
 * 
 * Purpose: This file handles the fourth step in the board game creation process,
 * allowing users to add walls between adjacent tiles to make them impassable.
 * It provides functionality to:
 * 1. Display the active grid cells with their text and images from previous steps
 * 2. Allow clicking on borders between cells to toggle walls
 * 3. Store wall information in the grid data structure
 * 
 * Workflow Position:
 * - This is STEP 4 in the board creation process
 * - It receives grid data from images.php (STEP 3) including cell text and images
 * - After adding walls, users proceed to result.php (STEP 5) for the completed board
 * 
 * Data Handling:
 * - Receives grid data via POST from images.php including:
 *   - Grid dimensions, active cells, coordinates
 *   - Cell colors, text content, and images
 *   - START/FINISH positions
 * - Adds wall information to this data
 * - Passes the enriched data to result.php
 * 
 * Wall System:
 * - Walls are represented by coordinates in format: "x,y,direction"
 * - Direction can be: "top", "right", "bottom", or "left"
 * - Walls are only placed between adjacent active cells
 * - Walls indicate impassable boundaries in the game
 */

// Include the chat functionality
include 'chat.php';

// Initialize chat and handle requests
$chat = setupChat();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Game Generator - Add Walls</title>
    <link rel="icon" type="image/png" href="images/selt_logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php echo $chat['styles']; ?>
    <style>
        /* Additional styling for non-editable cell-text */
        .cell .cell-text {
            width: 100%;
            height: 100%;
            max-height: 80px; /* Match cell height */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            word-break: break-word;
            padding: 4px;
            margin: 0;
            line-height: 1.2;
            position: relative;
            z-index: 1; /* Ensure text stays above background image */
        }
        
        /* Cell image styling */
        .cell-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 0;
        }
        
        /* Override any inline styles that might affect containment */
        .cell .cell-text span {
            max-width: 100%;
            overflow: hidden;
            display: inline-block;
        }
        
        /* Ensure START and FINISH cells are properly styled */
        .cell.start .cell-text, .cell.finish .cell-text {
            font-weight: bold;
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
            <div class="step completed">
                <div class="step-dot">2</div>
                <div class="step-label">Add Text</div>
            </div>
            <div class="step completed">
                <div class="step-dot">3</div>
                <div class="step-label">Add Images</div>
            </div>
            <div class="step active">
                <div class="step-dot">4</div>
                <div class="step-label">Add Walls</div>
            </div>
            <div class="step">
                <div class="step-dot">5</div>
                <div class="step-label">Print Board</div>
            </div>
            <!-- Progress line for completed steps (75% - showing completion through step 3) -->
            <div class="progress-line" style="width: 75%"></div>
        </div>
    </div>
    <div class="page-wrapper">
        <!-- Left information box -->
        <div class="info-box">
            <h3>Wall Adding Guide</h3>
            <p>To indicate that two adjacent tiles are impassable, you can add walls between them. To mark as impassable, click the Xs between tiles. The selected edges will have blue background and have three Xs on them. In the picture below, the player can't pass to D or E tile from A tile.</p>
            <img src="images/wall_1.jpg" alt="Wall example 1">
            <p>It will look like below</p>
            <img src="images/wall_2.jpg" alt="Wall example 2">
        </div>
        
        <!-- Main container with grid for wall addition -->
        <div class="container">
        <h1>Add Walls to Your Board</h1>
        <p class="instructions">Click on the border between two tiles to add a wall. Walls are shown as four "X" characters and indicate that players cannot move between those tiles. Click again to remove a wall.</p>
        
        <?php
        /**
         * Process data from previous step (edit.php)
         * 
         * Extracts grid dimensions, active cells, coordinates, cell colors,
         * and text content from the posted data and generates a grid with
         * wall placement functionality.
         */
        if (isset($_POST['gridData']) && isset($_POST['cell_text'])) {
            // Decode grid data from JSON
            $gridData = json_decode($_POST['gridData'], true);
            $rows = $gridData['rows'];
            $cols = $gridData['cols'];
            $cells = $gridData['cells']; // Linear indices
            $cellTexts = $_POST['cell_text'];
            
            // Check if we have coordinates in the new format
            $hasCoordinates = isset($gridData['coordinates']);
            $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];
            
            // Initialize walls array if it doesn't exist
            if (!isset($gridData['walls'])) {
                $gridData['walls'] = [];
            }
            
            // Check for images in session
            if (isset($_SESSION['cellImages']) && !empty($_SESSION['cellImages'])) {
                echo '<!-- Session image data is present: ' . count($_SESSION['cellImages']) . ' images -->';
                // Reference in gridData that we're using session images
                $gridData['hasSessionImages'] = true;
            } else {
                echo '<!-- No session image data found -->';
                // Initialize session images array if it doesn't exist
                $_SESSION['cellImages'] = [];
                
                // Check for legacy image data in gridData and move to session
                if (isset($gridData['cellImages']) && !empty($gridData['cellImages'])) {
                    echo '<!-- Moving ' . count($gridData['cellImages']) . ' images from gridData to session -->';
                    $_SESSION['cellImages'] = $gridData['cellImages'];
                    // Remove from gridData to reduce size
                    $gridData['hasSessionImages'] = true;
                    unset($gridData['cellImages']);
                }
            }
            
            // Add a "Go Back" button form that will submit to images.php - must be OUTSIDE the main form
            echo '<form action="images.php" method="post" id="go-back-form" class="go-back-form">';
            echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
            echo '<input type="hidden" name="returnFromWalls" value="true">';
            echo '<button type="submit" class="back-link">Go back</button>';
            echo '</form>';
            
            // JavaScript to handle the "Go Back" button functionality
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const goBackForm = document.getElementById("go-back-form");
                    
                    goBackForm.addEventListener("submit", function(e) {
                        // Prevent the default submission
                        e.preventDefault();
                        
                        // Create a copy of gridData without the large image data for the form
                        const gridDataForBack = {...gridData};
                        
                        // Add text content to the form
                        const cellTexts = {};
                        document.querySelectorAll(".cell").forEach(cell => {
                            const x = cell.dataset.x;
                            const y = cell.dataset.y;
                            if (x && y) {
                                const coordKey = `${x},${y}`;
                                const textElement = cell.querySelector(".cell-text");
                                if (textElement) {
                                    // Create hidden inputs for cell text content
                                    const hiddenInput = document.createElement("input");
                                    hiddenInput.type = "hidden";
                                    hiddenInput.name = `cell_text[${coordKey}]`;
                                    hiddenInput.value = textElement.innerHTML;
                                    goBackForm.appendChild(hiddenInput);
                                    
                                    // Store text color if available
                                    const textClasses = Array.from(textElement.classList);
                                    const textColorClass = textClasses.find(cls => cls.startsWith("text-"));
                                    if (textColorClass) {
                                        const color = textColorClass.replace("text-", "");
                                        if (!gridDataForBack.cellTextColors) {
                                            gridDataForBack.cellTextColors = {};
                                        }
                                        gridDataForBack.cellTextColors[coordKey] = color;
                                    }
                                }
                            }
                        });
                        
                        // Reference to session images
                        gridDataForBack.hasSessionImages = true;
                        
                    // Ensure returnFromWalls flag is present
                    let returnFromWallsInput = goBackForm.querySelector("input[name=\'returnFromWalls\']");
                    if (!returnFromWallsInput) {
                        returnFromWallsInput = document.createElement("input");
                        returnFromWallsInput.type = "hidden";
                        returnFromWallsInput.name = "returnFromWalls";
                        returnFromWallsInput.value = "true";
                        goBackForm.appendChild(returnFromWallsInput);
                    }
                    
                    // Update the gridData input and submit
                    const gridDataInput = goBackForm.querySelector("input[name=\'gridData\']");
                    gridDataInput.value = JSON.stringify(gridDataForBack);
                    goBackForm.submit();
                    });
                });
            </script>';

            // Create form to be submitted to result.php
            echo '<form action="result.php" method="post" id="walls-form" enctype="multipart/form-data">';
            
            // Create hidden inputs for all the cell text data
            foreach ($cellTexts as $key => $value) {
                echo '<input type="hidden" name="cell_text[' . htmlspecialchars($key) . ']" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false) . '">';
            }
            
            // Debug output to verify we have image data
            if (isset($gridData['cellImages']) && !empty($gridData['cellImages'])) {
                echo '<!-- Image data is present in walls.php: ' . count($gridData['cellImages']) . ' images -->';
            } else {
                echo '<!-- No image data found in walls.php -->';
            }
            
            echo '<div class="grid-container">';
            echo '<div class="result-grid wall-selection-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';
            
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
                    $fontSize = '16px'; // Default font size
                    $textColorClass = ''; // Initialize text color class
                    
                    // Convert to 1-based coordinates
                    $x = $j + 1;
                    $y = $i + 1;
                    $coordKey = "{$x},{$y}";
                    
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
                        $cellClass = 'active';
                        
                        // Check both coordinate-based and index-based cell texts
                        if (isset($cellTexts[$coordKey])) {
                            $cellContent = $cellTexts[$coordKey];
                        } elseif (isset($cellTexts[$cellIndex])) {
                            $cellContent = $cellTexts[$cellIndex];
                        }
                        
                        // Apply background color class if available
                        if (isset($gridData['cellColors'][$coordKey])) {
                            $colorClass = 'color-' . $gridData['cellColors'][$coordKey];
                            $cellClass .= ' ' . $colorClass;
                        }
                        
                        // Apply text color class if available - check direct cell_color data first, then gridData
                        if (isset($_POST['cell_color']) && isset($_POST['cell_color'][$coordKey])) {
                            $textColorClass = 'text-' . $_POST['cell_color'][$coordKey];
                            // Also ensure it's in gridData for future steps
                            $gridData['cellTextColors'][$coordKey] = $_POST['cell_color'][$coordKey];
                        } elseif (isset($gridData['cellTextColors'][$coordKey])) {
                            $textColorClass = 'text-' . $gridData['cellTextColors'][$coordKey];
                        }
                        
                        // Determine font size based on text length - matches enhanced edit.php
                        if (!empty($cellContent)) {
                            $textLength = strlen($cellContent);
                            
                            // Calculate uppercase percentage
                            $uppercaseCount = strlen(preg_replace('/[^A-Z]/', '', $cellContent));
                            $uppercasePercentage = ($textLength > 0) ? ($uppercaseCount / $textLength) * 100 : 0;
                            
                            // Base font size on text length
                            if ($textLength > 40) {
                                $fontSize = '12px';
                            } else if ($textLength > 30) {
                                $fontSize = '14px';
                            } else if ($textLength > 20) {
                                $fontSize = '16px';
                            } else if ($textLength > 10) {
                                $fontSize = '17px';
                            } else {
                                $fontSize = '18px'; // Default font size
                            }
                            
                            // Further reduce font size based on uppercase percentage
                            if ($uppercasePercentage > 50) {
                                // Extract numeric part of font size
                                $fontSizeNum = intval($fontSize);
                                $fontSizeNum = max($fontSizeNum - 2, 8); // Reduce by 2px but not below 8px
                                $fontSize = $fontSizeNum . 'px';
                            } else if ($uppercasePercentage > 30) {
                                // Extract numeric part of font size
                                $fontSizeNum = intval($fontSize);
                                $fontSizeNum = max($fontSizeNum - 1, 8); // Reduce by 1px but not below 8px
                                $fontSize = $fontSizeNum . 'px';
                            }
                        } else {
                            $fontSize = '18px'; // Default font size
                        }
						
						$cellHeight = isset($gridData['cellHeight']) ? intval($gridData['cellHeight']) : 100;
						$fontSizeNum = intval($fontSize);
						$lineHeight   = max((int) round($fontSizeNum * 1), 12);
						$explicitLines = substr_count($cellContent ?? '', "\n") + 1;
						$avgCharsPerLine = 12;
						$wrappedLines = (int) ceil(strlen($cellContent ?? '') / max($avgCharsPerLine,1));
						$lineCount = max($explicitLines, $wrappedLines);
						$contentHeight = $lineCount * $lineHeight;
						$padTop = 5;
						$padBottom = 5;

						if ($contentHeight > $cellHeight) {
							$newFont = max($fontSizeNum - 2, 8);
							$fontSize = $newFont . 'px';
							$padTop = 5;
							$padBottom = 5;
						} else {
							$fontSizeNum = $fontSizeNum - 1;
							$fontSize = $fontSizeNum . 'px';
							$padTop = $padBottom = max((int) floor(($cellHeight - $contentHeight) / 2), 5);
						}
                    }
                    
                    // Output the cell with image (if available) and content
                    echo '<div class="cell '.$cellClass.'" data-x="'.$x.'" data-y="'.$y.'">';
                    
                    // Add image if available in session
                    if (isset($_SESSION['cellImages'][$coordKey])) {
                        $imageData = $_SESSION['cellImages'][$coordKey];
                        echo '<div class="cell-image" style="background-image: url('.$imageData.');"></div>';
                    }
                    
                    echo '<div class="cell-text '.$textColorClass.'" style="font-size: '.$fontSize.';">'.$cellContent.'</div>';
                    
                    /**
                     * Wall Element Generation
                     * 
                     * Only add wall elements between active cells
                     * Walls have four possible directions: top, right, bottom, left
                     * Each wall is represented by an array of X characters
                     */
                    $isActive = in_array($cellIndex, $cells) || 
                               ($hasCoordinates && isCoordinateActive($x, $y, $coordinates)) ||
                               isStartInPositions($x, $y, $startPositions) ||
                               isFinishInPositions($x, $y, $finishPositions);
                    
                    if ($isActive) {
                        // Top wall (only if not at top row)
                        if ($y > 1) {
                            // Check if the cell above is active
                            $topCellActive = in_array(($i-1) * $cols + $j, $cells) || 
                                           ($hasCoordinates && isCoordinateActive($x, $y-1, $coordinates)) ||
                                           isStartInPositions($x, $y-1, $startPositions) ||
                                           isFinishInPositions($x, $y-1, $finishPositions);
                            
                            if ($topCellActive) {
                                echo '<div class="wall-button wall-h wall-top" data-wall="'.$x.','.$y.',top" onclick="toggleWall(this);">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                        }
                        
                        // Right wall (only if not at rightmost column)
                        if ($x < $cols) {
                            // Check if the cell to the right is active
                            $rightCellActive = in_array($i * $cols + ($j+1), $cells) || 
                                             ($hasCoordinates && isCoordinateActive($x+1, $y, $coordinates)) ||
                                           isStartInPositions($x+1, $y, $startPositions) ||
                                           isFinishInPositions($x+1, $y, $finishPositions);
                            
                            if ($rightCellActive) {
                                echo '<div class="wall-button wall-v wall-right" data-wall="'.$x.','.$y.',right" onclick="toggleWall(this);">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                        }
                        
                        // Bottom wall (only if not at bottom row)
                        if ($y < $rows) {
                            // Check if the cell below is active
                            $bottomCellActive = in_array(($i+1) * $cols + $j, $cells) || 
                                              ($hasCoordinates && isCoordinateActive($x, $y+1, $coordinates)) ||
                                              isStartInPositions($x, $y+1, $startPositions) ||
                                              isFinishInPositions($x, $y+1, $finishPositions);
                            
                            if ($bottomCellActive) {
                                echo '<div class="wall-button wall-h wall-bottom" data-wall="'.$x.','.$y.',bottom" onclick="toggleWall(this);">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                        }
                        
                        // Left wall (only if not at leftmost column)
                        if ($x > 1) {
                            // Check if the cell to the left is active
                            $leftCellActive = in_array($i * $cols + ($j-1), $cells) || 
                                            ($hasCoordinates && isCoordinateActive($x-1, $y, $coordinates)) ||
                                            isStartInPositions($x-1, $y, $startPositions) ||
                                            isFinishInPositions($x-1, $y, $finishPositions);
                            
                            if ($leftCellActive) {
                                echo '<div class="wall-button wall-v wall-left" data-wall="'.$x.','.$y.',left" onclick="toggleWall(this);">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                        }
                    }
                    
                    echo '</div>';
                }
            }
            
            echo '</div>';
            echo '</div>';
            
            // Hidden input to store wall data
            echo '<input type="hidden" name="gridData" id="gridDataInput" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
            
            echo '<input type="submit" id="submit-btn" value="Go to the Next Step: View Final Board" class="submit-btn">';
            echo '</form>';
        } else {
            // Handle case when no grid data is provided
            echo '<p>No grid data found. Please go back and create a board.</p>';
            echo '<a href="index.php" class="back-link">Go Back</a>';
        }
            
            // Helper function to check if coordinates match a Start position
            function isStartInPositions($x, $y, $startPositions) {
                foreach ($startPositions as $startPos) {
                    if ($startPos[0] === $x && $startPos[1] === $y) {
                        return true;
                    }
                }
                return false;
            }
            
            // Helper function to check if coordinates match a Finish position
            function isFinishInPositions($x, $y, $finishPositions) {
                foreach ($finishPositions as $finishPos) {
                    if ($finishPos[0] === $x && $finishPos[1] === $y) {
                        return true;
                    }
                }
                return false;
            }
        ?>
        
        <script>
            /**
             * Wall Management Script
             * 
             * Handles:
             * 1. Toggling walls between adjacent active cells
             * 2. Tracking wall data in the grid data structure
             * 3. Updating the form data for submission to the next step
             */
            
            // Initialize grid data from PHP - without large image data
            let gridData = <?php echo json_encode($gridData ?? []); ?>;
            
            // Initialize walls array if it doesn't exist
            if (!gridData.walls) {
                gridData.walls = [];
            }
            
            // Add flag to indicate we're using session images
            gridData.hasSessionImages = true;
            
            // Function to toggle wall visibility and update data
            function toggleWall(wallElement) {
                const wallData = wallElement.dataset.wall;
                const wallIndex = gridData.walls.indexOf(wallData);
                
                // Parse the wall data to get coordinates and direction
                const [x, y, direction] = wallData.split(',');
                
                // Determine the opposite wall's data
                let oppositeWallData;
                let oppositeElement;
                
                switch(direction) {
                    case 'top':
                        // The opposite of top is the bottom of the cell above
                        oppositeWallData = `${x},${parseInt(y)-1},bottom`;
                        oppositeElement = document.querySelector(`[data-wall="${oppositeWallData}"]`);
                        break;
                    case 'right':
                        // The opposite of right is the left of the cell to the right
                        oppositeWallData = `${parseInt(x)+1},${y},left`;
                        oppositeElement = document.querySelector(`[data-wall="${oppositeWallData}"]`);
                        break;
                    case 'bottom':
                        // The opposite of bottom is the top of the cell below
                        oppositeWallData = `${x},${parseInt(y)+1},top`;
                        oppositeElement = document.querySelector(`[data-wall="${oppositeWallData}"]`);
                        break;
                    case 'left':
                        // The opposite of left is the right of the cell to the left
                        oppositeWallData = `${parseInt(x)-1},${y},right`;
                        oppositeElement = document.querySelector(`[data-wall="${oppositeWallData}"]`);
                        break;
                }
                
                // Toggle the clicked wall
                if (wallIndex === -1) {
                    // Wall doesn't exist, add it
                    gridData.walls.push(wallData);
                    wallElement.classList.add('wall-active');
                    
                    // Add the opposite wall if it exists and isn't already added
                    if (oppositeElement) {
                        const oppositeWallIndex = gridData.walls.indexOf(oppositeWallData);
                        if (oppositeWallIndex === -1) {
                            gridData.walls.push(oppositeWallData);
                            oppositeElement.classList.add('wall-active');
                        }
                    }
                } else {
                    // Wall exists, remove it
                    gridData.walls.splice(wallIndex, 1);
                    wallElement.classList.remove('wall-active');
                    
                    // Remove the opposite wall if it exists and is added
                    if (oppositeElement) {
                        const oppositeWallIndex = gridData.walls.indexOf(oppositeWallData);
                        if (oppositeWallIndex !== -1) {
                            gridData.walls.splice(oppositeWallIndex, 1);
                            oppositeElement.classList.remove('wall-active');
                        }
                    }
                }
                
                // Update the hidden input
                updateGridDataInput();
            }
            
            // Update hidden input with updated gridData
            function updateGridDataInput() {
                console.log("Updating gridData input with walls", gridData);
                if (gridData.cellImages) {
                    console.log("Images count:", Object.keys(gridData.cellImages).length);
                }
                
                // Collect current text colors from the DOM to ensure they're preserved
                const currentTextColors = {};
                document.querySelectorAll('.cell').forEach(cell => {
                    const x = cell.dataset.x;
                    const y = cell.dataset.y;
                    if (x && y) {
                        const coordKey = `${x},${y}`;
                        const textElement = cell.querySelector('.cell-text');
                        if (textElement) {
                            // Find text color class
                            const textClasses = Array.from(textElement.classList);
                            const textColorClass = textClasses.find(cls => cls.startsWith('text-'));
                            if (textColorClass) {
                                const color = textColorClass.replace('text-', '');
                                currentTextColors[coordKey] = color;
                            }
                        }
                    }
                });
                
                // Ensure text colors are preserved in the form data
                gridData.cellTextColors = currentTextColors;
                
                document.getElementById('gridDataInput').value = JSON.stringify(gridData);
            }
            
            // Initialize wall visibility based on existing data
            document.addEventListener('DOMContentLoaded', function() {
                // All walls are visible by default with light background
                
                // Apply wall-active class to saved walls
                if (gridData.walls && gridData.walls.length > 0) {
                    gridData.walls.forEach(wallData => {
                        const wallElement = document.querySelector(`[data-wall="${wallData}"]`);
                        if (wallElement) {
                            wallElement.classList.add('wall-active');
                        }
                    });
                }
                
                // Add form submission handler to ensure all data is included
                const form = document.getElementById('walls-form');
                const submitBtn = document.getElementById('submit-btn');
                
                form.addEventListener('submit', function(e) {
                    // Always ensure the latest text colors are collected before submission
                    updateGridDataInput();
                    
                    // Debug information
                    console.log("Form submission - gridData prepared for result.php");
                    if (gridData.cellImages) {
                        console.log("Image count being sent:", Object.keys(gridData.cellImages).length);
                    }
                });
                
                submitBtn.addEventListener('click', function(e) {
                    // Ensure the latest gridData is in the hidden input before submitting
                    updateGridDataInput();
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
