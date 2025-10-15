<?php
// Start session to access image data
session_start();

/**
 * Board Game Generator - Final Display Phase (Step 5/5)
 * 
 * Purpose: This file handles the final step in the board game creation process,
 * displaying the completed board with all customizations and providing print functionality.
 * It provides:
 * 1. A visual representation of the complete board with all customizations
 * 2. Color-coded tiles with custom text and font sizing
 * 3. Custom uploaded images as tile backgrounds
 * 4. Wall boundaries between tiles as specified
 * 5. Print optimization for physical game creation
 * 
 * Workflow Position:
 * - This is the FINAL STEP (STEP 5) in the board creation process
 * - It receives complete grid data from walls.php (STEP 4)
 * - Users can print their board or go back to edit previous aspects
 * 
 * Data Handling:
 * - Receives comprehensive grid data via POST from walls.php including:
 *   - Grid dimensions, active cells, coordinates
 *   - Cell colors, text content, and text colors
 *   - START/FINISH positions
 *   - Wall placements between cells
 * - Renders both screen and print versions of the board
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
    <meta name="format-detection" content="date=no">
    <meta name="format-detection" content="address=no">
    <meta name="format-detection" content="telephone=no">
    <title>Board Game Generator - Final Board</title>
    <link rel="icon" type="image/png" href="images/selt_logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php echo $chat['styles']; ?>
    <style>
        /* Enhanced styling for non-editable cell-text */
        .cell .cell-text {
            width: 100%;
            height: 100%;
            max-height: 80px; /* Match cell height */
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            overflow: hidden !important;
            word-break: break-word !important;
            padding: 4px !important;
            margin: 0 !important;
            line-height: 1.2 !important;
            position: relative !important;
            z-index: 1 !important; /* Ensure text stays above background image */
        }
        
        /* Cell image styling */
        .cell-image {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            z-index: 0 !important;
        }
        
        /* Override any inline styles that might affect containment */
        .cell .cell-text * {
            max-width: 100% !important;
            overflow: hidden !important;
            text-align: center !important;
            margin: 0 !important;
        }
        
        /* Ensure START and FINISH cells are properly styled */
        .cell.start .cell-text, .cell.finish .cell-text {
            font-weight: bold !important;
        }
        
        /* Page layout for information boxes - specific to result.php */
        .page-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            width: 100%;
            max-width: 1700px;
            margin: 2rem auto;
        }
        
        /* Title form styling */
        #title-form {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }
        
        #board_title {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            flex-grow: 1;
            max-width: 400px;
        }
        
        #update-title-btn {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        #update-title-btn:hover {
            background-color: #45a049;
        }
        
        #board-title-display {
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        /* Wall styles for displaying impassable boundaries */
        .wall-button {
            position: absolute;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: #000000;
            z-index: 30;
            border-radius: 8px;
            transition: all 0.3s ease;
            /* No background in result.php */
        }

        /* Horizontal and vertical wall positioning */
        .wall-h {
            height: 18px;
            width: 70px;
            transform: translateX(-50%);
            left: 50%;
        }

        .wall-v {
            width: 18px;
            height: 70px;
            transform: translateY(-50%);
            top: 50%;
        }

        /* Wall position modifiers */
        .wall-top {
            top: -10px;
        }

        .wall-right {
            right: -10px;
        }

        .wall-bottom {
            bottom: -10px;
        }

        .wall-left {
            left: -10px;
        }

        /* Wall SVG styling */
        .wall-svg {
            width: 14px;
            height: 14px;
            margin: 0 2px;
            fill: rgba(0, 0, 0, 0.6);
        }

        /* Vertical wall specific styles - center SVGs vertically */
        .wall-v {
            display: flex;
            flex-direction: column;
        }

        .wall-v .wall-svg {
            margin: 2px 0;
        }
        
        .cell {
            position: relative;
        }

        /* Active wall styling - no background in result.php */
        .wall-active {
            background-color: transparent !important;
            box-shadow: none !important;
        }

        .wall-active .wall-svg {
            fill: white !important;
        }
        
        /* Hide screen elements when printing */
        @media print {
            .page-wrapper {
                display: none !important;
            }
            
            /* Make sure print elements are visible */
            .print-only-container {
                display: block !important;
            }
            
            /* Style print title */
            .print-title {
                display: block !important;
                text-align: center;
                font-size: 32px;
                font-weight: bold;
                margin-top: 20px;
                margin-bottom: 30px;
                color: black;
                page-break-after: avoid;
            }

            /* Wall styling for print - no background */
            .wall-button.wall-active {
            background-color: transparent !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
            position: absolute !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            z-index: 30 !important;
            visibility: visible !important;
            opacity: 1 !important;
            box-shadow: none !important;
            }

            /* Enhanced print SVG styling to ensure visibility */
            img.wall-svg {
                display: inline-block !important;
                width: 14px !important;
                height: 14px !important;
                visibility: visible !important;
                opacity: 1 !important;
                margin: 0 2px !important;
            }

            /* Ensure horizontal walls are correctly positioned and sized */
            .wall-h {
                height: 18px !important;
                width: 70px !important;
            }

            /* Ensure vertical walls are correctly positioned and sized */
            .wall-v {
                width: 18px !important;
                height: 70px !important;
            }
            
            /* Hide chat elements when printing */
            .chat-button, .chat-container {
                display: none !important;
            }
        }
        
        /* Watermark logo styles */
        .watermark-logo {
            position: absolute;
            bottom: 5mm;
            left: 0;
            width: 100%;
            text-align: center;
            z-index: 10;
        }
        
        .watermark-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .watermark-logo img {
            max-width: 40px;
            height: auto;
            margin: 0 0px;
        }
        
        .watermark-name {
            font-size: 12px;
            color: #333;
            font-weight: bold;
        }
        
        @media print {
            .watermark-logo {
                display: block;
            }
        }
    </style>
</head>
<body class="result-page">
    <?php echo $chat['ui']; ?>
    <!-- Screen-only content - not shown when printing -->
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
            <div class="step completed">
                <div class="step-dot">4</div>
                <div class="step-label">Add Walls</div>
            </div>
            <div class="step active">
                <div class="step-dot">5</div>
                <div class="step-label">Print Board</div>
            </div>
            <!-- Progress line for completed steps (100% - showing completion of all steps) -->
            <div class="progress-line" style="width: 100%"></div>
        </div>
    </div>
    <div class="page-wrapper screen-only">
        <!-- Left information box -->
        <div class="info-box">
            <h3>Your Completed Board</h3>
            <p>Congratulations! You've successfully created your custom board game. This is the final view of your board before printing.</p>
            <p>All your custom text and wall placements have been applied. Take a moment to review your board and make sure everything looks correct.</p>
            <p>When you're ready, click the "Print Board" button to print your game board. The printed version will be formatted to fit perfectly on a standard A4 page.</p>
        </div>
        
        <!-- Main container with grid and print button -->
        <div class="container">
        <?php
        // Get custom title if set, otherwise use default
        $boardTitle = isset($_POST['board_title']) ? htmlspecialchars($_POST['board_title']) : "Your Generated Board";
        ?>
        
        <!-- Go back button with form to preserve data -->
        <?php if (isset($_POST['gridData']) && isset($_POST['cell_text'])): ?>
        <form action="walls.php" method="post" class="go-back-form">
            <input type="hidden" name="gridData" value='<?php echo htmlspecialchars($_POST['gridData']); ?>'>
            <?php foreach ($_POST['cell_text'] as $key => $value): ?>
                <input type="hidden" name="cell_text[<?php echo htmlspecialchars($key); ?>]" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false); ?>">
            <?php endforeach; ?>
            <button type="submit" class="back-link">Go back</button>
        </form>
        <?php endif; ?>
        <form id="title-form" method="post" action="">
            <input type="text" id="board_title" name="board_title" value="<?php echo $boardTitle; ?>" placeholder="Enter your board title" required>
            <?php
            // Recreate all POST data as hidden fields to preserve when form submits
            if (isset($_POST)) {
                foreach ($_POST as $key => $value) {
                    if ($key !== 'board_title') {
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($k) . ']" value="' . htmlspecialchars($v) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                }
            }
            ?>
        </form>

        <h1 id="board-title-display"><?php echo $boardTitle; ?></h1>
        
        <button onclick="window.print()" class="print-button">Print Board</button>
        
        <div class="grid-container a4-grid">
            <?php
            /**
             * Board Rendering - Screen Version
             * 
             * Processes complete grid data and renders the board for screen display
             * with all customizations including cell colors, text, images, and walls.
             */
            if (isset($_POST['gridData'])) {
                // Debug output to verify we have image data
                $gridDataArr = json_decode($_POST['gridData'], true);
                
                // Check for session images first
                if (isset($_SESSION['cellImages']) && !empty($_SESSION['cellImages'])) {
                    echo '<!-- Session image data is present: ' . count($_SESSION['cellImages']) . ' images -->';
                } 
                // Check for legacy image data in gridData
                else if (isset($gridDataArr['cellImages']) && !empty($gridDataArr['cellImages'])) {
                    echo '<!-- Legacy image data found in gridData: ' . count($gridDataArr['cellImages']) . ' images, moving to session -->';
                    // Move to session for consistency
                    $_SESSION['cellImages'] = $gridDataArr['cellImages'];
                } 
                else {
                    echo '<!-- No image data found in result.php -->';
                    // Initialize session array if not exists
                    if (!isset($_SESSION['cellImages'])) {
                        $_SESSION['cellImages'] = [];
                    }
                }
                $gridData = json_decode($_POST['gridData'], true);
                $rows = $gridData['rows'];
                $cols = $gridData['cols'];
                $cells = $gridData['cells']; // Linear indices for backward compatibility
                $cellTexts = isset($_POST['cell_text']) ? $_POST['cell_text'] : [];
                
                // Check if we have coordinates in the new format
                $hasCoordinates = isset($gridData['coordinates']);
                $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];
                
                // Get wall data if it exists
                $walls = isset($gridData['walls']) ? $gridData['walls'] : [];
                
                // Helper function to check if a coordinate is in the coordinates array
                function isCoordinateActive($x, $y, $coordinates) {
                    foreach ($coordinates as $coord) {
                        if ($coord[0] === $x && $coord[1] === $y) {
                            return true;
                        }
                    }
                    return false;
                }
                
                // Define the start and finish positions using 1-based coordinates
                $startPosition = ['x' => $cols, 'y' => 1]; // Top-right
                $finishPosition = ['x' => 1, 'y' => $rows]; // Bottom-left
                
                echo '<div class="result-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';
                
                // Get custom Start/Finish positions
                $startPositions = isset($gridData['startPositions']) ? $gridData['startPositions'] : [];
                $finishPositions = isset($gridData['finishPositions']) ? $gridData['finishPositions'] : [];
                
                // Generate grid cells with all customizations
                for ($i = 0; $i < $rows; $i++) {
                    for ($j = 0; $j < $cols; $j++) {
                        $cellIndex = $i * $cols + $j;
                        $cellClass = '';
                        $cellText = '';
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
                            $cellText = 'START';
                        }
                        // Finish cell
                        elseif ($isFinish) {
                            $cellClass = 'finish active';
                            $cellText = 'FINISH';
                        }
                        // Regular active cell - check both old and new formats
                        elseif (in_array($cellIndex, $cells) || 
                              ($hasCoordinates && isCoordinateActive($x, $y, $coordinates))) {
                            $cellClass = 'active';
                            
                            // Check both coordinate-based and index-based cell texts
                            if (isset($cellTexts[$coordKey])) {
                                $cellText = $cellTexts[$coordKey];
                            } elseif (isset($cellTexts[$cellIndex])) {
                                $cellText = $cellTexts[$cellIndex];
                            }
                            
                            // Apply background color class if available
                            if (isset($gridData['cellColors'])) {
                                if (isset($gridData['cellColors'][$coordKey])) {
                                    $colorClass = 'color-' . $gridData['cellColors'][$coordKey];
                                    $cellClass .= ' ' . $colorClass;
                                }
                            }
                            
                            // Get text color if available
                            if (isset($gridData['cellTextColors'])) {
                                if (isset($gridData['cellTextColors'][$coordKey])) {
                                    $textColorClass = 'text-' . $gridData['cellTextColors'][$coordKey];
                                }
                            }
                            
                            // Determine font size based on text length - matches enhanced edit.php
                            if (!empty($cellText)) {
                                $textLength = strlen($cellText);
                                
                                // Calculate uppercase percentage
                                $uppercaseCount = strlen(preg_replace('/[^A-Z]/', '', $cellText));
                                $uppercasePercentage = ($textLength > 0) ? ($uppercaseCount / $textLength) * 100 : 0;
                                
                                // Base font size on text length
                                if ($textLength > 40) {
                                    $fontSize = '12px';
                                } elseif ($textLength > 30) {
                                    $fontSize = '14px';
                                } elseif ($textLength > 20) {
                                    $fontSize = '16px';
                                } elseif ($textLength > 10) {
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
                                } elseif ($uppercasePercentage > 30) {
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
							$explicitLines = substr_count($cellText ?? '', "\n") + 1;
							$avgCharsPerLine = 12;
							$wrappedLines = (int) ceil(strlen($cellText ?? '') / max($avgCharsPerLine,1));
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
                        
                        // Render the cell with image (if available) and content
                        echo '<div class="cell '.$cellClass.'" data-x="'.$x.'" data-y="'.$y.'">';
                        
                        // Add image if available in session
                        if (isset($_SESSION['cellImages'][$coordKey])) {
                            $imageData = $_SESSION['cellImages'][$coordKey];
                            echo '<div class="cell-image" style="background-image: url('.$imageData.');"></div>';
                        }
                        // Legacy support for direct gridData images
                        else if (isset($gridData['cellImages']) && isset($gridData['cellImages'][$coordKey])) {
                            $imageData = $gridData['cellImages'][$coordKey];
                            echo '<div class="cell-image" style="background-image: url('.$imageData.');"></div>';
                            // Store in session for future use
                            $_SESSION['cellImages'][$coordKey] = $imageData;
                        }
                        
                        echo '<div class="cell-text '.$textColorClass.'" style="font-size: '.$fontSize.';">'.$cellText.'</div>';
                        
                        // Add walls if they exist for this cell
                        if (!empty($walls)) {
                            // Check for top wall
                            $topWallData = "{$x},{$y},top";
                            if (in_array($topWallData, $walls)) {
                                echo '<div class="wall-button wall-h wall-top wall-active" style="background-color: transparent !important;">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                            
                            // Check for right wall
                            $rightWallData = "{$x},{$y},right";
                            if (in_array($rightWallData, $walls)) {
                                echo '<div class="wall-button wall-v wall-right wall-active" style="background-color: transparent !important;">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                            
                            // Check for bottom wall
                            $bottomWallData = "{$x},{$y},bottom";
                            if (in_array($bottomWallData, $walls)) {
                                echo '<div class="wall-button wall-h wall-bottom wall-active" style="background-color: transparent !important;">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                            
                            // Check for left wall
                            $leftWallData = "{$x},{$y},left";
                            if (in_array($leftWallData, $walls)) {
                                echo '<div class="wall-button wall-v wall-left wall-active" style="background-color: transparent !important;">';
                                echo '<img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg"><img src="images/x.png" class="wall-svg">';
                                echo '</div>';
                            }
                        }
                        
                        echo '</div>';
                    }
                }
                
                echo '</div>';
            } else {
                // Handle case when no grid data is provided
                echo '<p>No grid data found. Please go back and create a board.</p>';
                echo '<a href="index.php">Go Back</a>';
            }
            ?>
        </div>
        
        <div class="back-links">
            <a href="index.php" class="back-link">Create New Board</a>
        </div>
        
        <div class="download-section">
            <h3>Game Pieces</h3>
            <p>Download game pieces to use with your board:</p>
            <div class="download-buttons">
                <a href="images/pawns_and_rules.jpeg" download class="download-button">
                    <span class="download-icon">⬇</span> Download Pawn
                </a>
                <a href="images/foldable_dice_template.jpeg" download class="download-button">
                    <span class="download-icon">⬇</span> Download Dice
                </a>
            </div>
        </div>
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
            <a href="index.php" class="footer-nav-item">Board Game Generator</a>
			<a href="explore.php" class="footer-nav-item">Explore</a>
            <a href="play_on_screen.php" class="footer-nav-item">Play on a Screen</a>
        </div>
        <div class="footer-copyright">
            &copy; <?php echo date('Y'); ?> SILVER ELT. All rights reserved.
        </div>
    </footer>
    </div>
    
    <!-- Print-only content - only visible when printing -->
    <div class="print-only-container" style="display: none;">
        <?php
        // Display board title at the top of the print version with strong inline styling
        if (isset($_POST['board_title'])) {
            echo '<h1 class="print-title">' . htmlspecialchars($_POST['board_title']) . '</h1>';
        } else {
            echo '<h1 class="print-title">Your Generated Board</h1>';
        }
        
        /**
         * Board Rendering - Print Version
         * 
         * Generates a print-optimized version of the board
         * with minimal UI elements and maximized board size.
         */
        if (isset($_POST['gridData'])) {
            $gridData = json_decode($_POST['gridData'], true);
            $rows = $gridData['rows'];
            $cols = $gridData['cols'];
            $cells = $gridData['cells']; // Linear indices for backward compatibility
            $cellTexts = isset($_POST['cell_text']) ? $_POST['cell_text'] : [];
            
            // Check if we have coordinates in the new format
            $hasCoordinates = isset($gridData['coordinates']);
            $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];
            
            // Get wall data if it exists
            $walls = isset($gridData['walls']) ? $gridData['walls'] : [];
            
            // Get custom Start/Finish positions
            $startPositions = isset($gridData['startPositions']) ? $gridData['startPositions'] : [];
            $finishPositions = isset($gridData['finishPositions']) ? $gridData['finishPositions'] : [];
            
            echo '<div class="result-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';
            
            // Debug output for print view
            if (isset($_SESSION['cellImages']) && !empty($_SESSION['cellImages'])) {
                echo '<!-- Print view: Session image data is present: ' . count($_SESSION['cellImages']) . ' images -->';
            } else {
                echo '<!-- Print view: No session image data found -->';
            }
            
            // Generate print version of the grid - essentially duplicates
            // the screen version but with optimizations for printing
            for ($i = 0; $i < $rows; $i++) {
                for ($j = 0; $j < $cols; $j++) {
                    $cellIndex = $i * $cols + $j;
                    $cellClass = '';
                    $cellText = '';
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
                        $cellText = 'START';
                    }
                    // Finish cell
                    elseif ($isFinish) {
                        $cellClass = 'finish active';
                        $cellText = 'FINISH';
                    }
                    // Regular active cell - check both old and new formats
                    elseif (in_array($cellIndex, $cells) || 
                          ($hasCoordinates && isCoordinateActive($x, $y, $coordinates))) {
                        $cellClass = 'active';
                        
                        // Check both coordinate-based and index-based cell texts
                        if (isset($cellTexts[$coordKey])) {
                            $cellText = $cellTexts[$coordKey];
                        } elseif (isset($cellTexts[$cellIndex])) {
                            $cellText = $cellTexts[$cellIndex];
                        }
                        
                        // Apply background color class if available
                        if (isset($gridData['cellColors'])) {
                            if (isset($gridData['cellColors'][$coordKey])) {
                                $colorClass = 'color-' . $gridData['cellColors'][$coordKey];
                                $cellClass .= ' ' . $colorClass;
                            }
                        }
                        
                        // Get text color if available
                        if (isset($gridData['cellTextColors'])) {
                            if (isset($gridData['cellTextColors'][$coordKey])) {
                                $textColorClass = 'text-' . $gridData['cellTextColors'][$coordKey];
                            }
                        }
                        
                        // Determine font size based on text length - matches enhanced edit.php
                        if (!empty($cellText)) {
                            $textLength = strlen($cellText);
                            
                            // Calculate uppercase percentage
                            $uppercaseCount = strlen(preg_replace('/[^A-Z]/', '', $cellText));
                            $uppercasePercentage = ($textLength > 0) ? ($uppercaseCount / $textLength) * 100 : 0;
                            
                            // Base font size on text length
                            if ($textLength > 40) {
                                $fontSize = '12px';
                            } elseif ($textLength > 30) {
                                $fontSize = '14px';
                            } elseif ($textLength > 20) {
                                $fontSize = '16px';
                            } elseif ($textLength > 10) {
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
                            } elseif ($uppercasePercentage > 30) {
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
						$lineHeight   = max((int) round($fontSizeNum * 1.2), 12);
						$explicitLines = substr_count($cellText ?? '', "\n") + 1;
						$avgCharsPerLine = 12;
						$wrappedLines = (int) ceil(strlen($cellText ?? '') / max($avgCharsPerLine,1));
						$lineCount = max($explicitLines, $wrappedLines);
						$contentHeight = $lineCount * $lineHeight;
						$padTop = 5;
						$padBottom = 5;

						if ($contentHeight > $cellHeight) {
							$newFont = max($fontSizeNum - 2, 8);
							$fontSize = $newFont . 'px';
							$padTop = 5;
							$padBottom = 5;
						}  else {
							$fontSizeNum = $fontSizeNum - 1;
							$fontSize = $fontSizeNum . 'px';
							$padTop = $padBottom = max((int) floor(($cellHeight - $contentHeight) / 2), 5);
						}
                    }
                    
                    // Render the cell with image (if available) and content
                    echo '<div class="cell '.$cellClass.'" data-x="'.$x.'" data-y="'.$y.'">';
                    
                    // Add image if available in session
                    if (isset($_SESSION['cellImages'][$coordKey])) {
                        $imageData = $_SESSION['cellImages'][$coordKey];
                        echo '<div class="cell-image" style="background-image: url('.$imageData.');"></div>';
                    }
                    // Legacy support for direct gridData images
                    else if (isset($gridData['cellImages']) && isset($gridData['cellImages'][$coordKey])) {
                        $imageData = $gridData['cellImages'][$coordKey];
                        echo '<div class="cell-image" style="background-image: url('.$imageData.');"></div>';
                    }
                    
                    echo '<div class="cell-text '.$textColorClass.'" style="font-size: '.$fontSize.';">'.$cellText.'</div>';
                    
                    // Add walls if they exist for this cell
                    if (!empty($walls)) {
                        // Check for top wall
                        $topWallData = "{$x},{$y},top";
                        if (in_array($topWallData, $walls)) {
                            echo '<div class="wall-button wall-h wall-top wall-active" style="background-color: transparent !important;">';
                            echo '<img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X">';
                            echo '</div>';
                        }
                        
                        // Check for right wall
                        $rightWallData = "{$x},{$y},right";
                        if (in_array($rightWallData, $walls)) {
                            echo '<div class="wall-button wall-v wall-right wall-active" style="background-color: transparent !important;">';
                            echo '<img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X">';
                            echo '</div>';
                        }
                        
                        // Check for bottom wall
                        $bottomWallData = "{$x},{$y},bottom";
                        if (in_array($bottomWallData, $walls)) {
                            echo '<div class="wall-button wall-h wall-bottom wall-active" style="background-color: transparent !important;">';
                            echo '<img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X">';
                            echo '</div>';
                        }
                        
                        // Check for left wall
                        $leftWallData = "{$x},{$y},left";
                        if (in_array($leftWallData, $walls)) {
                            echo '<div class="wall-button wall-v wall-left wall-active" style="background-color: transparent !important;">';
                            echo '<img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X"><img src="images/x.png" class="wall-svg" alt="X">';
                            echo '</div>';
                        }
                    }
                    
                    echo '</div>';
                }
            }
            
            echo '</div>';
            
            // Add watermark with SELT.SPACE on bottom left, logo and names on bottom right
            echo '<div class="watermark-logo">';
            echo '<div class="watermark-left">';
            echo '<div class="watermark-selt-space">SELT.SPACE</div>';
            echo '</div>';
            echo '<div class="watermark-right">';
            echo '<div class="watermark-names">';
            echo '<div class="watermark-name">METİN TORUN</div>';
            echo '<div class="watermark-name">YUSUF TERZİ</div>';
            echo '</div>';
            echo '<img src="images/selt_logo.png" alt="SELT Logo">';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
    
    <!-- JavaScript for title form functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const titleForm = document.getElementById('title-form');
            const titleInput = document.getElementById('board_title');
            const titleDisplay = document.getElementById('board-title-display');
            const updateButton = document.getElementById('update-title-btn');
            const printTitle = document.querySelector('.print-title');
            
            // Debounce function to limit how often a function can be called
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        func.apply(context, args);
                    }, wait);
                };
            }
            
            // Update displayed title (without form submission)
            function updateDisplayedTitle() {
                // Update both the screen and print versions of the title
                titleDisplay.textContent = titleInput.value;
                if (printTitle) {
                    printTitle.textContent = titleInput.value;
                }
            }
            
            // Submit form to save changes (debounced to avoid too many submissions)
            const debouncedSubmit = debounce(function() {
                titleForm.submit();
            }, 1000); // Wait 1 second after typing stops before submitting
            
            // Update title and submit form
            function updateTitle() {
                updateDisplayedTitle();
                titleForm.submit();
            }
            
            // Update title dynamically as user types
            titleInput.addEventListener('input', function() {
                updateDisplayedTitle();
                debouncedSubmit();
            });
            
            // Keep the existing button functionality as fallback
            if (updateButton) {
                updateButton.addEventListener('click', updateTitle);
            }
            
            // Also submit on Enter key in the input field
            titleInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Prevent default form submission
                    updateTitle();
                }
            });
        });
    </script>
    <?php echo $chat['script']; ?>
</body>
</html>
