<?php
include 'chat.php';
include 'random_prompts.php';

$chat = setupChat();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Game Generator - Add Text</title>
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
        <p>Step 2: Add Text</p>
    </div>

    <div class="page-wrapper">
        <div class="info-box">
            <h4>Text Colors</h4>
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
        </div>
        
        <div class="container">
            <h1>Add Text to Your Board</h1>
        
            <?php
            if (isset($_POST['gridData'])) {
                $gridData = json_decode($_POST['gridData'], true);
                $rows = $gridData['rows'];
                $cols = $gridData['cols'];
                $cells = $gridData['cells'];
                $hasCoordinates = isset($gridData['coordinates']);
                $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];

                if (!isset($gridData['cellTextColors'])) {
                    $gridData['cellTextColors'] = [];
                }

                echo '<form action="index.php" method="post" id="go-back-form">';
                echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
                echo '<input type="hidden" name="returnFromEdit" value="true">';
                echo '<input type="hidden" name="cellTextColors" id="cellTextColors-back" value=\'{}\'>';
                echo '<button type="submit">Go back</button>';
                echo '</form>';
            
                echo '<form action="images.php" method="post" id="text-form">';
                echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
                echo '<input type="hidden" name="cellTextColors" id="cellTextColors" value=\'{}\'>';

                echo '<div class="grid-container">';
                echo '<div class="result-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';

                function isCoordinateActive($x, $y, $coordinates) {
                    foreach ($coordinates as $coord) {
                        if ($coord[0] === $x && $coord[1] === $y) {
                            return true;
                        }
                    }
                    return false;
                }

                $startPositions = isset($gridData['startPositions']) ? $gridData['startPositions'] : [];
                $finishPositions = isset($gridData['finishPositions']) ? $gridData['finishPositions'] : [];

                for ($i = 0; $i < $rows; $i++) {
                    for ($j = 0; $j < $cols; $j++) {
                        $cellIndex = $i * $cols + $j;
                        $cellClass = '';
                        $cellContent = '';
                        $isEditable = false;
                        
                        $x = $j + 1;
                        $y = $i + 1;
                        
                        $isStart = false;
                        foreach ($startPositions as $startPos) {
                            if ($startPos[0] === $x && $startPos[1] === $y) {
                                $isStart = true;
                                break;
                            }
                        }
                        
                        $isFinish = false;
                        foreach ($finishPositions as $finishPos) {
                            if ($finishPos[0] === $x && $finishPos[1] === $y) {
                                $isFinish = true;
                                break;
                            }
                        }
                        
                        if ($isStart) {
                            $cellClass = 'start active';
                            $cellContent = 'START';
                        }
                        elseif ($isFinish) {
                            $cellClass = 'finish active';
                            $cellContent = 'FINISH';
                        }
                        elseif (in_array($cellIndex, $cells) || ($hasCoordinates && isCoordinateActive($x, $y, $coordinates))) {
                            $cellClass = 'active editable-cell';
                            $isEditable = true;
                            
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
                            $textFieldName = $hasCoordinates ? "cell_text[{$x},{$y}]" : "cell_text[{$cellIndex}]";
                            $textFieldData = $hasCoordinates ? "data-x=\"{$x}\" data-y=\"{$y}\"" : "data-cell-index=\"{$cellIndex}\"";
                            $coordKey = "{$x},{$y}";
                            $existingContent = '';
                            $textColorClass = '';
                            
                            if (isset($_POST['cell_text'][$coordKey])) {
                                $existingContent = $_POST['cell_text'][$coordKey];
                            }
                            
                            if (isset($gridData['cellTextColors'][$coordKey])) {
                                $textColorClass = 'text-' . $gridData['cellTextColors'][$coordKey];
                            }
                            
                            echo '<div class="cell-text ' . $textColorClass . '" contenteditable="true" data-name="' . $textFieldName . '" ' . $textFieldData . '">' . $existingContent . '</div>';
                            echo '<input type="hidden" name="' . $textFieldName . '" class="cell-text-input" value="' . htmlspecialchars($existingContent, ENT_QUOTES, 'UTF-8', false) . '">';
                        } else {
                            echo $cellContent;
                        }

                        echo '</div>';
                    }
                }
                
                echo '</div>';
                echo '</div>';
                
                echo '<input type="submit" value="Go to the Next Step: Add Images">';
                echo '</form>';
                
            } else {
                echo '<p>No grid data found. Please go back and create a board.</p>';
                echo '<a href="index.php">Go Back</a>';
            }
            ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentTextColor = 'black';
            let cellTextColors = <?php echo json_encode($gridData['cellTextColors'] ?? []); ?>;
            const textColorOptions = document.querySelectorAll('.text-color-option');
            const textareas = document.querySelectorAll('.cell-text');
            const form = document.getElementById('text-form');

            function updateCellTextColorsInput() {
                document.getElementById('cellTextColors').value = JSON.stringify(cellTextColors);
            }

            textColorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    textColorOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    currentTextColor = this.dataset.textColor;
                });
            });

            textareas.forEach(textarea => {
                textarea.addEventListener('focus', function() {
                    this.classList.remove(...Array.from(this.classList).filter(c => c.startsWith('text-')));
                    this.classList.add(`text-${currentTextColor}`);
                    let cellKey = this.dataset.x ? `${this.dataset.x},${this.dataset.y}` : this.dataset.cellIndex;
                    if (cellKey) {
                        cellTextColors[cellKey] = currentTextColor;
                        updateCellTextColorsInput();
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                updateCellTextColorsInput();
                textareas.forEach(div => {
                    const cellName = div.dataset.name;
                    const hiddenInput = document.querySelector(`input[name="${cellName}"]`);
                    if (hiddenInput) {
                        hiddenInput.value = div.innerHTML;
                    }
                });
            });
        });
    </script>
    <?php echo $chat['script']; ?>
</body>
</html>