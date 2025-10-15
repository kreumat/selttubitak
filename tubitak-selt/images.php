<?php
// Start session to store image data
session_start();

/**
 * Board Game Generator - Image Addition Phase (Step 3/5)
 * 
 * Purpose: This file handles the third step in the board game creation process,
 * allowing users to upload and place images onto the board tiles.
 * It provides functionality to:
 * 1. Upload one or more images from the user's device
 * 2. Display uploaded images as thumbnails in the left panel
 * 3. Drag and drop images onto any tile to set as background
 * 4. Replace or remove images from tiles
 * 
 * Workflow Position:
 * - This is STEP 3 in the board creation process
 * - It receives grid data from edit.php (STEP 2) including cell text content
 * - After adding images, users proceed to walls.php (STEP 4) to add walls
 * - Then finally to result.php (STEP 5) for the completed board
 * 
 * Data Handling:
 * - Receives grid data via POST from edit.php including:
 *   - Grid dimensions, active cells, coordinates
 *   - Cell colors and text content
 *   - START/FINISH positions
 * - Adds image data (base64 encoded) to this data
 * - Passes the enriched data to walls.php
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
    <title>Board Game Generator - Add Images</title>
    <link rel="icon" type="image/png" href="images/selt_logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php echo $chat['styles']; ?>
    <style>
        /* Image upload and thumbnail styles */
        .image-upload-container {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: block;
            cursor: pointer;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .upload-btn {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background-color: white;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .thumbnails-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
            width: 100%;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border: 2px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            cursor: grab;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .thumbnail:active {
            cursor: grabbing;
        }
        
        /* Cell styling for images */
        .cell-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 90%;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 0;
        }
        
        .cell-text {
            position: relative;
            z-index: 1;
        }
        
        .remove-image {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 18px;
            height: 18px;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 12px;
            cursor: pointer;
            z-index: 10;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s, visibility 0.2s;
        }
        
        /* Only show remove image button when hovering over cells that have an image */
        .cell:hover .remove-image {
            visibility: hidden;
            opacity: 0;
        }
        
        /* Use JavaScript to add this class to cells with images */
        .cell.has-image:hover .remove-image {
            visibility: visible;
            opacity: 1;
        }
        
        /* Enhanced styling for non-editable cell-text */
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
            pointer-events: none; /* Make text non-interactable for drag events */
        }
        
        /* Drag-over styling */
        .cell.drag-over {
            box-shadow: inset 0 0 0 3px var(--primary-color);
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
        
        /* Upload indicator styling */
        #upload-indicator {
            display: none;
            margin-top: 10px;
            color: var(--primary-color);
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
            <div class="step active">
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
            <!-- Progress line for completed steps (50% - showing completion through step 2) -->
            <div class="progress-line" style="width: 50%"></div>
        </div>
    </div>
    <div class="page-wrapper">
        <!-- Left information box -->
        <div class="info-box">
            <h3>Image Upload</h3>
            <p>Enhance your board game by adding images to the tiles. Upload images and drag them onto any tile.</p>
            
            <!-- Image upload section -->
            <div class="image-upload-container">
                <div class="upload-btn-wrapper">
                    <button class="upload-btn"><i class="fas fa-upload"></i> Upload Images</button>
                    <input type="file" id="image-upload" accept="image/*" multiple>
                </div>
                <div id="upload-indicator">Uploading...</div>
                
                <!-- Thumbnails will appear here -->
                <div class="thumbnails-container" id="thumbnails-container"></div>
            </div>
            
            <!-- Pre-set images section -->
            <div class="preset-images">
                <h4>Pre-set Images</h4>
                <div class="thumbnails-container">
                    <div class="thumbnail" draggable="true" data-image-src="images/apple.png" style="background-image: url('images/apple.png'); background-size: contain; background-repeat: no-repeat; background-position: center;"></div>
                    <div class="thumbnail" draggable="true" data-image-src="images/banana.png" style="background-image: url('images/banana.png'); background-size: contain; background-repeat: no-repeat; background-position: center;"></div>
                </div>
            </div>
            
            <div class="image-instructions" style="padding-top: 20px;">
                <h4>How to Add Images</h4>
                <ol>
                    <li>Upload one or more images using the button above</li>
                    <li>Drag an image from the thumbnails and drop it onto any tile</li>
                    <li>To replace an image, simply drag a new image to the same tile</li>
                    <li>To remove an image, hover over the tile and click the X that appears</li>
                </ol>
            </div>
        </div>
        
        <!-- Main container with grid for image placement -->
        <div class="container">
        <h1>Add Images to Your Board</h1>
        <p class="instructions">Drag and drop images from the left panel onto your board tiles. Images will fit inside the tiles while maintaining their original proportions.</p>
        
        <?php
        /**
         * Process data from previous step (edit.php)
         * 
         * Extracts grid dimensions, active cells, coordinates, cell colors,
         * and text content from the posted data and generates a grid with
         * image placement functionality.
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
            
            // Initialize cell images array if it doesn't exist
            if (!isset($gridData['cellImages'])) {
                $gridData['cellImages'] = [];
            }
            
            // Always check for images in session, regardless of navigation path
            if (isset($_SESSION['cellImages']) && !empty($_SESSION['cellImages'])) {
                echo '<!-- Restoring ' . count($_SESSION['cellImages']) . ' images from session -->';
                $gridData['cellImages'] = $_SESSION['cellImages'];
                $gridData['hasSessionImages'] = true;
            }
            
            // Store the grid data structure in session for image persistence
            $_SESSION['gridData'] = $gridData;
            
            // Add a "Go Back" button form that will submit to edit.php - must be OUTSIDE the main form
            echo '<form action="edit.php" method="post" id="go-back-form" class="go-back-form">';
            echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
            echo '<input type="hidden" name="returnFromImages" value="true">';
            echo '<button type="submit" class="back-link">Go back</button>';
            echo '</form>';
            
            // Add warning message about image persistence when navigating
            echo '<div class="navigation-warning" style="margin-top: 8px; font-size: 13px; color: #666; font-style: italic; line-height: 1.4; text-align: center; width: 100%; margin-left: auto; margin-right: auto;">';
            echo 'Note: If you go back, images you\'ve added to tiles may temporarily not appear on other pages. Don\'t worry — your images are still saved and will reappear when you move forward again.';
            echo '</div>';
            
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
                        
                        // Store image data via session, not form data
                        if (imagesUpdated) {
                            // Submit all images at once to ensure session is fully updated
                            const allImages = {...gridData.cellImages};
                            fetch("session_handler.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify({
                                    action: "saveAllImages",
                                    images: allImages
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log("All images saved to session:", data);
                                
                                // Mark that we have images in session
                                gridDataForBack.hasSessionImages = true;
                                
                                // Update the gridData input and submit
                                const gridDataInput = goBackForm.querySelector("input[name=\'gridData\']");
                                gridDataInput.value = JSON.stringify(gridDataForBack);
                                goBackForm.submit();
                            })
                            .catch(error => {
                                console.error("Error saving images to session:", error);
                                // Submit anyway
                                gridDataForBack.hasSessionImages = true;
                                const gridDataInput = goBackForm.querySelector("input[name=\'gridData\']");
                                gridDataInput.value = JSON.stringify(gridDataForBack);
                                goBackForm.submit();
                            });
                        } else {
                            // No new images, just submit the form
                            if (Object.keys(gridData.cellImages || {}).length > 0) {
                                gridDataForBack.hasSessionImages = true;
                            }
                            const gridDataInput = goBackForm.querySelector("input[name=\'gridData\']");
                            gridDataInput.value = JSON.stringify(gridDataForBack);
                            goBackForm.submit();
                        }
                    });
                });
            </script>';

            
            // Create form to be submitted to walls.php
            echo '<form action="walls.php" method="post" id="images-form">';
            
            // Create hidden inputs for all the cell text data
            foreach ($cellTexts as $key => $value) {
                echo '<input type="hidden" name="cell_text[' . htmlspecialchars($key) . ']" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false) . '">';
            }
            
            echo '<div class="grid-container">';
            echo '<div class="result-grid image-placement-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';
            
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
                    
                    // Output the cell and content
                    echo '<div class="cell '.$cellClass.'" data-x="'.$x.'" data-y="'.$y.'">';
                    
                    // Add image container (empty initially, will be filled via JavaScript)
                    echo '<div class="cell-image" data-cell-coord="'.$coordKey.'"></div>';
                    
                    // Add the text content
                    echo '<div class="cell-text '.$textColorClass.'" style="font-size: '.$fontSize.';">'.$cellContent.'</div>';
                    
                    // Add remove button (hidden initially, shown on hover)
                    echo '<div class="remove-image" data-cell-coord="'.$coordKey.'"><i class="fas fa-times"></i></div>';
                    
                    echo '</div>';
                }
            }
            
            echo '</div>';
            echo '</div>';
            
            // Hidden input to store image data
            echo '<input type="hidden" name="gridData" id="gridDataInput" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
            
            echo '<input type="submit" id="submit-btn" value="Go to the Next Step: Add Walls" class="submit-btn">';
            echo '</form>';
        } else {
            // Handle case when no grid data is provided
            echo '<p>No grid data found. Please go back and create a board.</p>';
            echo '<a href="index.php" class="back-link">Go Back</a>';
        }
        ?>
        
        <script>
            /**
             * Image Management Script
             * 
             * Handles:
             * 1. Image uploading and thumbnail creation
             * 2. Drag and drop functionality for placing images on tiles
             * 3. Removing images from tiles
             * 4. Storing image data in the grid data structure
             */
            
            // Initialize grid data from PHP
            let gridData = <?php echo json_encode($gridData ?? []); ?>;
            
            // Initialize cellImages array if it doesn't exist
            if (!gridData.cellImages) {
                gridData.cellImages = {};
            }
            
            // Flag to track if images have been updated
            let imagesUpdated = false;
            
            // Track uploaded images
            const uploadedImages = [];
            
            // DOM elements
            const imageUploadInput = document.getElementById('image-upload');
            const thumbnailsContainer = document.getElementById('thumbnails-container');
            const uploadIndicator = document.getElementById('upload-indicator');
            const gridDataInput = document.getElementById('gridDataInput');
            
            // Handle image uploads
            imageUploadInput.addEventListener('change', function(event) {
                const files = event.target.files;
                
                if (files.length > 0) {
                    uploadIndicator.style.display = 'block';
                    
                    // Process each file
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        
                        // Validate file type
                        if (!file.type.startsWith('image/')) {
                            continue;
                        }
                        
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const imageData = e.target.result;
                            
                            // Create a thumbnail
                            const thumbnail = document.createElement('div');
                            thumbnail.className = 'thumbnail';
                            thumbnail.style.backgroundImage = `url(${imageData})`;
                            thumbnail.setAttribute('draggable', 'true');
                            thumbnail.dataset.imageIndex = uploadedImages.length;
                            
                            // Store the image data
                            uploadedImages.push(imageData);
                            
                            // Add drag event listeners
                            thumbnail.addEventListener('dragstart', handleDragStart);
                            
                            // Add to the thumbnails container
                            thumbnailsContainer.appendChild(thumbnail);
                            
                            // Hide the upload indicator if this is the last file
                            if (i === files.length - 1) {
                                uploadIndicator.style.display = 'none';
                            }
                        };
                        
                        reader.readAsDataURL(file);
                    }
                }
            });
            
            // Drag and Drop functionality
            
            // Variables to track the current dragged image
            let draggedImageIndex = null;
            
            // Handle drag start event
            function handleDragStart(event) {
                draggedImageIndex = this.dataset.imageIndex;
                event.dataTransfer.effectAllowed = 'copy';
                event.dataTransfer.setData('text/plain', draggedImageIndex);
            }
            
            // Add event listeners to cells for drop targets
            const cells = document.querySelectorAll('.cell.active');
            cells.forEach(cell => {
                cell.addEventListener('dragover', handleDragOver);
                cell.addEventListener('dragleave', handleDragLeave);
                cell.addEventListener('drop', handleDrop);
                
                // Add event listener to remove button
                const removeBtn = cell.querySelector('.remove-image');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const cellCoord = this.dataset.cellCoord;
                        removeImageFromCell(cellCoord);
                    });
                }
            });
            
            // Handle dragover event
            function handleDragOver(event) {
                event.preventDefault();
                this.classList.add('drag-over');
                event.dataTransfer.dropEffect = 'copy';
            }
            
            // Handle dragleave event
            function handleDragLeave(event) {
                this.classList.remove('drag-over');
            }
            
            // Handle drop event
            function handleDrop(event) {
                event.preventDefault();
                this.classList.remove('drag-over');
                
                const imageIndex = event.dataTransfer.getData('text/plain');
                if (imageIndex !== null && uploadedImages[imageIndex]) {
                    const imageData = uploadedImages[imageIndex];
                    const x = this.dataset.x;
                    const y = this.dataset.y;
                    const cellCoord = `${x},${y}`;
                    
                    // Update the cell's image
                    const cellImage = this.querySelector('.cell-image');
                    if (cellImage) {
                        cellImage.style.backgroundImage = `url(${imageData})`;
                        // Add class to cell to indicate it has an image
                        this.classList.add('has-image');
                    }
                    
                    // Store the image data in gridData
                    gridData.cellImages[cellCoord] = imageData;
                    imagesUpdated = true;
                    
                    // Update the hidden input
                    updateGridDataInput();
                    
                    // Update session via AJAX
                    updateSessionImageData(cellCoord, imageData);
                }
            }
            
            // Remove image from cell
            function removeImageFromCell(cellCoord) {
                // Find the cell image element
                const cellImage = document.querySelector(`.cell-image[data-cell-coord="${cellCoord}"]`);
                if (cellImage) {
                    cellImage.style.backgroundImage = '';
                    
                    // Remove the has-image class from the cell
                    const cell = cellImage.closest('.cell');
                    if (cell) {
                        cell.classList.remove('has-image');
                    }
                }
                
                // Remove from gridData
                if (gridData.cellImages[cellCoord]) {
                    delete gridData.cellImages[cellCoord];
                    imagesUpdated = true;
                    
                    // Update the hidden input
                    updateGridDataInput();
                    
                    // Update session by removing the image
                    removeSessionImageData(cellCoord);
                }
            }
            
            // Update the hidden input with the current gridData
            function updateGridDataInput() {
                console.log("Updating gridData input", gridData);
                console.log("Images count:", Object.keys(gridData.cellImages).length);
                
                // Create a copy of gridData without the large image data for the form
                const gridDataForForm = {...gridData};
                
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
                gridDataForForm.cellTextColors = currentTextColors;
                
                // Store image data via session, not form data
                if (gridDataForForm.cellImages) {
                    // Replace with a placeholder to indicate we have images
                    gridDataForForm.hasSessionImages = true;
                    delete gridDataForForm.cellImages;
                }
                
                gridDataInput.value = JSON.stringify(gridDataForForm);
            }
            
            // Function to update session image data via AJAX
            function updateSessionImageData(cellCoord, imageData) {
                fetch('session_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=setImage&coord=${encodeURIComponent(cellCoord)}&imageData=${encodeURIComponent(imageData)}`
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Session updated:', data);
                })
                .catch(error => {
                    console.error('Error updating session:', error);
                });
            }
            
            // Function to remove session image data via AJAX
            function removeSessionImageData(cellCoord) {
                fetch('session_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=removeImage&coord=${encodeURIComponent(cellCoord)}`
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Session updated (image removed):', data);
                })
                .catch(error => {
                    console.error('Error updating session:', error);
                });
            }
            
            // Initialize image display if there are already images in the gridData
            function initializeImageDisplay() {
                if (gridData.cellImages) {
                    for (const cellCoord in gridData.cellImages) {
                        const imageData = gridData.cellImages[cellCoord];
                        const cellImage = document.querySelector(`.cell-image[data-cell-coord="${cellCoord}"]`);
                        
                        if (cellImage && imageData) {
                            cellImage.style.backgroundImage = `url(${imageData})`;
                            
                            // Add has-image class to cell
                            const cell = cellImage.closest('.cell');
                            if (cell) {
                                cell.classList.add('has-image');
                            }
                        }
                    }
                }
            }
            
            // Initialize preset images
            function initializePresetImages() {
                const presetThumbnails = document.querySelectorAll('.preset-images .thumbnail');
                
                presetThumbnails.forEach((thumbnail, index) => {
                    const imageSrc = thumbnail.dataset.imageSrc;
                    
                    // Create a new Image to load the image file
                    const img = new Image();
                    img.onload = function() {
                        // Create canvas to convert image to data URL
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);
                        const imageData = canvas.toDataURL('image/png');
                        
                        // Add to uploadedImages array
                        const imageIndex = uploadedImages.length;
                        uploadedImages.push(imageData);
                        
                        // Set the image index for drag operations
                        thumbnail.dataset.imageIndex = imageIndex;
                        
                        // Add drag event listeners
                        thumbnail.addEventListener('dragstart', handleDragStart);
                    };
                    img.src = imageSrc;
                });
            }
            
            // Call initialization on page load
            document.addEventListener('DOMContentLoaded', function() {
                initializeImageDisplay();
                initializePresetImages();
                
                // Add form submission handler to ensure image data is included
                const form = document.getElementById('images-form');
                const submitBtn = document.getElementById('submit-btn');
                
                form.addEventListener('submit', function(e) {
                    // Always ensure the latest text colors are collected before submission
                    updateGridDataInput();
                    
                    // Make sure all image data has been saved to session
                    if (imagesUpdated) {
                        // Submit all images at once to ensure session is fully updated
                        const allImages = {...gridData.cellImages};
                        fetch('session_handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'saveAllImages',
                                images: allImages
                            })
                        })
                        .then(response => response.text())
                        .then(data => {
                            console.log('All images saved to session:', data);
                            // Now submit the form
                            form.submit();
                        })
                        .catch(error => {
                            console.error('Error saving images to session:', error);
                            // Submit anyway
                            form.submit();
                        });
                        
                        // Prevent default form submission, we'll submit manually after AJAX
                        e.preventDefault();
                    }
                    // If no images were updated, the form will submit normally with text colors preserved
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