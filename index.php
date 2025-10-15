<?php
// Start session
session_start();

// Always clear image data when on index.php to reset images
if (isset($_SESSION['cellImages'])) {
    $_SESSION['cellImages'] = [];
}

// Store grid data in session if returning from edit.php
if (isset($_POST['returnFromEdit']) && isset($_POST['gridData'])) {
    $_SESSION['returnedGridData'] = $_POST['gridData'];
    if (isset($_POST['cell_text'])) {
        $_SESSION['cellTextContent'] = $_POST['cell_text'];
    }
    if (isset($_POST['cellTextColors'])) {
        $_SESSION['cellTextColors'] = $_POST['cellTextColors'];
    }
}

include 'chat.php';
$chat = setupChat();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Game Generator - Create</title>
    <link rel="stylesheet" href="style.css">
    <?php echo $chat['styles']; ?>
</head>
<body>
    <?php echo $chat['ui']; ?>
    <nav class="navbar">
        <a href="index.php">Simple Board Game Generator</a>
        <a href="explore.php">Explore</a>
        <a href="play_on_screen.php">Play on a Screen</a>
    </nav>
    
    <div class="stepper-container">
        <p>Step 1: Board Setup</p>
    </div>

    <div class="page-wrapper">
        <div class="info-box">
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
            
            <h4>Start and Finish</h4>
            <div class="start-finish-buttons">
                <button id="start-button">Start</button>
                <button id="finish-button">Finish</button>
            </div>
        </div>
        
        <div class="container">
            <h1>Board Game Generator</h1>
            <div class="controls">
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
                <button id="save-grid">Go to the Next Step: Add Text</button>
            </div>
            
            <div class="grid-container">
                <div id="grid"></div>
            </div>
            <p id="validation-message" class="validation-message hidden">You must add at least one Start tile and one Finish tile to proceed.</p>
        </div>
    </div>
    
    <script src="puzzles.js"></script>
    <script src="grid.js"></script>
    
    <?php if (isset($_SESSION['returnedGridData'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const returnedGridData = <?php echo $_SESSION['returnedGridData']; ?>;
                setTimeout(function() {
                    if (window.loadReturnedGridData) {
                        window.loadReturnedGridData(returnedGridData);
                        const puzzleSelect = document.getElementById('puzzle-select');
                        if (puzzleSelect) {
                            puzzleSelect.value = '';
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
    unset($_SESSION['returnedGridData']);
    unset($_SESSION['cellTextContent']);
    unset($_SESSION['cellTextColors']);
    endif; 
    ?>
    
    <?php echo $chat['script']; ?>
</body>
</html>