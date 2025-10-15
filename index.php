<?php
// Start session
session_start();

// Always clear image data when on index.php to reset images
// This ensures images are reset when navigating back from any page
if (isset($_SESSION['cellImages'])) {
    $_SESSION['cellImages'] = [];
}

// Store grid data in session if returning from edit.php
if (isset($_POST['returnFromEdit']) && isset($_POST['gridData'])) {
    $_SESSION['returnedGridData'] = $_POST['gridData'];
    
    // Store any text content if available
    if (isset($_POST['cell_text'])) {
        $_SESSION['cellTextContent'] = $_POST['cell_text'];
    }
    
    // Store text colors if available
    if (isset($_POST['cellTextColors'])) {
        $_SESSION['cellTextColors'] = $_POST['cellTextColors'];
    }
}

/**
 * Board Game Generator - Grid Creation Phase (Step 1/5)
 * 
 * Purpose: This is the entry point for the board game generator application.
 * It allows users to:
 * 1. Select tiles on a 9×11 grid to form their game board
 * 2. Choose tile colors from a color palette
 * 3. Designate START and FINISH positions
 * 4. Select from premade templates or create a custom layout
 * 
 * Workflow Position:
 * - This is STEP 1 in the board creation process
 * - After selecting tiles here, users proceed to edit.php (STEP 2) to add text
 * - Then to images.php (STEP 3) to add images, walls.php (STEP 4) to add walls, 
 *   and finally to result.php (STEP 5) for the final board
 * 
 * Dependencies:
 * - puzzles.js: Contains predefined board templates
 * - grid.js: Handles interactive grid creation functionality
 * - style.css: Provides styling for the application
 * - chat.php: Provides AI assistant functionality
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
    <title>Board Game Generator - Create</title>
    <link rel="icon" type="image/png" href="images/selt_logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php echo $chat['styles']; ?>
</head>
<body>
    <?php echo $chat['ui']; ?>
    <nav class="navbar">
        <div class="navbar-brand">
            <span class="navbar-brand-silver">TUBITAK</span>
            <img src="images/selt_logo.png" alt="SELT Logo" class="navbar-brand-logo">
            <span class="navbar-brand-elt">2209 A</span>
        </div>
        <div class="mobile-menu-toggle" id="mobileMenuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="navbar-nav" id="navbarNav">
            <a href="index.php" class="navbar-nav-item">Simple Board Game Generator</a>
            <a href="explore.php" class="navbar-nav-item">Explore</a>
            <a href="play_on_screen.php" class="navbar-nav-item">Play on a Screen</a>
        </div>
    </nav>
    
    <!-- Step Progress Bar -->
    <div class="stepper-container">
        <div class="stepper">
            <div class="step active">
                <div class="step-dot">1</div>
                <div class="step-label">Board Setup</div>
            </div>
            <div class="step">
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
            <!-- Progress line for completed steps (initially hidden as this is step 1) -->
            <div class="progress-line" style="width: 0%"></div>
        </div>
    </div>
    <div class="page-wrapper">
        <!-- Left information box with color palette -->
        <div class="info-box">
            <!-- Color palette for selecting tile colors -->
            <div class="color-palette">
                <div class="color-option color-red" data-color="red" title="Red"></div>
                <div class="color-option color-green" data-color="green" title="Green"></div>
                <div class="color-option color-blue" data-color="blue" title="Blue"></div>
                <div class="color-option color-yellow" data-color="yellow" title="Yellow"></div>
                <div class="color-option color-orange" data-color="orange" title="Orange"></div>
                <div class="color-option color-brown" data-color="brown" title="Brown"></div>
                <div class="color-option color-pink" data-color="pink" title="Pink"></div>
                <div class="color-option color-purple" data-color="purple" title="Purple"></div>
                <div class="color-option color-grey" data-color="grey" title="Grey"></div>
                <div class="color-option color-default selected" data-color="default" title="Silver (Default)"></div>
                <div class="color-option color-cutty-sark" data-color="cutty-sark" title="Cutty Sark"></div>
                <div class="color-option color-lightning-yellow" data-color="lightning-yellow" title="Lightning Yellow"></div>
            </div>
            
            <!-- Start/Finish buttons section -->
            <h4>Start and Finish</h4>
            <div class="start-finish-buttons">
                <button id="start-button" class="special-button start-button">Start</button>
                <button id="finish-button" class="special-button finish-button">Finish</button>
            </div>
        </div>
        
        <!-- Main container with grid and controls -->
        <div class="container">
            <h1>Board Game Generator</h1>
            <div class="controls">
                
                <!-- Puzzle template selector -->
                <div class="puzzle-selector">
                    <label for="puzzle-select">Choose a preset template:</label>
                    <select id="puzzle-select">
                        <option value="">Custom (Draw your own)</option>
                        <option value="snake">Snake Shaped</option>
                        <option value="crossways">Crossways</option>
                        <option value="whirlwind">Whirlwind</option>
                        <option value="piston">Piston</option>
                        <option value="castle">The Castle</option>
                        <option value="erzinRoads">Erzin Roads</option>
                    </select>
                </div>
                
                <!-- Save button to proceed to the next step -->
                <button id="save-grid">Go to the Next Step: Add Text</button>
              
            </div>
            
            <!-- Grid container populated by grid.js -->
            <div class="grid-container">
                <div id="grid"></div>
            </div>
            <p id="validation-message" class="validation-message hidden">You must add at least one Start tile and one Finish tile to proceed.</p>
        </div>
        
    
    <!-- Scripts loaded in order: puzzles first (for templates), then grid.js for functionality -->
    <script src="puzzles.js"></script>
    <script src="grid.js"></script>
    
    <?php if (isset($_SESSION['returnedGridData'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load the returned grid data when DOM is ready
            try {
                const returnedGridData = <?php echo $_SESSION['returnedGridData']; ?>;
                
                // Wait for grid.js to finish initialization
                setTimeout(function() {
                    // Access the gridData object from grid.js
                    if (window.loadReturnedGridData) {
                        window.loadReturnedGridData(returnedGridData);
                        
                        // Set puzzle selector to "Custom" since we're loading a custom grid
                        const puzzleSelect = document.getElementById('puzzle-select');
                        if (puzzleSelect) {
                            puzzleSelect.value = '';
                            
                            // Trigger change event to update internal state
                            const event = new Event('change');
                            puzzleSelect.dispatchEvent(event);
                        }
                    }
                }, 100);
            } catch (error) {
                console.error('Error loading returned grid data:', error);
            }
        });
    </script>
    <?php 
    // Clear the session data after it's been used
    unset($_SESSION['returnedGridData']);
    unset($_SESSION['cellTextContent']);
    unset($_SESSION['cellTextColors']);
    endif; 
    ?>
    
    <!-- Mobile menu toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const navbarNav = document.getElementById('navbarNav');
            
            if (mobileMenuToggle && navbarNav) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileMenuToggle.classList.toggle('active');
                    navbarNav.classList.toggle('show');
                    
                    // Prevent scrolling when menu is open
                    document.body.style.overflow = navbarNav.classList.contains('show') ? 'hidden' : '';
                });
                
                // Close mobile menu when clicking on a nav item
                const navItems = navbarNav.querySelectorAll('.navbar-nav-item');
                navItems.forEach(item => {
                    item.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        navbarNav.classList.remove('show');
                        document.body.style.overflow = '';
                    });
                });
            }
        });
    </script>
    
    <?php echo $chat['script']; ?>
</body>
</html>
