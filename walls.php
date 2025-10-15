<?php
session_start();
include 'chat.php';
$chat = setupChat();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Game Generator - Add Walls</title>
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
        <p>Step 4: Add Walls</p>
    </div>

    <div class="page-wrapper">
        <div class="container">
            <h1>Add Walls to Your Board</h1>
            <p>Click on the border between two tiles to add or remove a wall.</p>
        
            <?php
            if (isset($_POST['gridData']) && isset($_POST['cell_text'])) {
                $gridData = json_decode($_POST['gridData'], true);
                $rows = $gridData['rows'];
                $cols = $gridData['cols'];
                $cells = $gridData['cells'];
                $cellTexts = $_POST['cell_text'];
                $hasCoordinates = isset($gridData['coordinates']);
                $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];

                if (!isset($gridData['walls'])) {
                    $gridData['walls'] = [];
                }

                echo '<form action="images.php" method="post">';
                echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
                foreach ($cellTexts as $key => $value) {
                    echo '<input type="hidden" name="cell_text[' . htmlspecialchars($key) . ']" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false) . '">';
                }
                echo '<button type="submit">Go back</button>';
                echo '</form>';

                echo '<form action="result.php" method="post" id="walls-form">';
                echo '<input type="hidden" name="gridData" id="gridDataInput" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
                foreach ($cellTexts as $key => $value) {
                    echo '<input type="hidden" name="cell_text[' . htmlspecialchars($key) . ']" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false) . '">';
                }

                echo '<div class="grid-container">';
                echo '<div class="result-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';

                function isCoordinateActive($x, $y, $coordinates) {
                    foreach ($coordinates as $coord) {
                        if ($coord[0] === $x && $coord[1] === $y) return true;
                    }
                    return false;
                }

                $startPositions = $gridData['startPositions'] ?? [];
                $finishPositions = $gridData['finishPositions'] ?? [];

                for ($i = 0; $i < $rows; $i++) {
                    for ($j = 0; $j < $cols; $j++) {
                        $cellIndex = $i * $cols + $j;
                        $cellClass = '';
                        $cellText = '';
                        $x = $j + 1;
                        $y = $i + 1;
                        $coordKey = "{$x},{$y}";
                        
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
                            $cellText = 'START';
                        } elseif ($isFinish) {
                            $cellClass = 'finish active';
                            $cellText = 'FINISH';
                        } elseif (in_array($cellIndex, $cells) || ($hasCoordinates && isCoordinateActive($x, $y, $coordinates))) {
                            $cellClass = 'active';
                            if (isset($cellTexts[$coordKey])) {
                                $cellText = $cellTexts[$coordKey];
                            } elseif (isset($cellTexts[$cellIndex])) {
                                $cellText = $cellTexts[$cellIndex];
                            }
                            if (isset($gridData['cellColors'][$coordKey])) {
                                $cellClass .= ' color-' . $gridData['cellColors'][$coordKey];
                            }
                        }
                        
                        echo '<div class="cell '.$cellClass.'" data-x="'.$x.'" data-y="'.$y.'">';
                        if (isset($_SESSION['cellImages'][$coordKey])) {
                            $imageData = $_SESSION['cellImages'][$coordKey];
                            echo '<div class="cell-image" style="background-image: url('.$imageData.');"></div>';
                        }
                        echo '<div class="cell-text">'.$cellText.'</div>';

                        // Simplified wall indicators
                        echo '<div class="wall-top" data-wall="'.$x.','.$y.',top"></div>';
                        echo '<div class="wall-right" data-wall="'.$x.','.$y.',right"></div>';
                        echo '<div class="wall-bottom" data-wall="'.$x.','.$y.',bottom"></div>';
                        echo '<div class="wall-left" data-wall="'.$x.','.$y.',left"></div>';

                        echo '</div>';
                    }
                }

                echo '</div>';
                echo '</div>';

                echo '<input type="submit" value="View Final Board">';
                echo '</form>';
            } else {
                echo '<p>No grid data found.</p><a href="index.php">Go Back</a>';
            }
            ?>
        </div>
    </div>
        
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let gridData = <?php echo json_encode($gridData ?? []); ?>;
            if (!gridData.walls) gridData.walls = [];
            const gridDataInput = document.getElementById('gridDataInput');

            function updateGridDataInput() {
                gridDataInput.value = JSON.stringify(gridData);
            }

            document.querySelectorAll('[data-wall]').forEach(wallElement => {
                wallElement.style.position = 'absolute';
                wallElement.style.background = 'rgba(0,0,0,0.2)';
                wallElement.style.cursor = 'pointer';

                const wallData = wallElement.dataset.wall;
                const [x,y,dir] = wallData.split(',');

                if (dir === 'top') {
                    wallElement.style.top = '0';
                    wallElement.style.left = '0';
                    wallElement.style.width = '100%';
                    wallElement.style.height = '5px';
                } else if (dir === 'right') {
                    wallElement.style.top = '0';
                    wallElement.style.right = '0';
                    wallElement.style.width = '5px';
                    wallElement.style.height = '100%';
                } else if (dir === 'bottom') {
                    wallElement.style.bottom = '0';
                    wallElement.style.left = '0';
                    wallElement.style.width = '100%';
                    wallElement.style.height = '5px';
                } else if (dir === 'left') {
                    wallElement.style.top = '0';
                    wallElement.style.left = '0';
                    wallElement.style.width = '5px';
                    wallElement.style.height = '100%';
                }

                if (gridData.walls.includes(wallData)) {
                    wallElement.classList.add('wall-active');
                    wallElement.style.background = 'black';
                }

                wallElement.addEventListener('click', () => {
                    const wallIndex = gridData.walls.indexOf(wallData);
                    if (wallIndex === -1) {
                        gridData.walls.push(wallData);
                        wallElement.classList.add('wall-active');
                        wallElement.style.background = 'black';
                    } else {
                        gridData.walls.splice(wallIndex, 1);
                        wallElement.classList.remove('wall-active');
                        wallElement.style.background = 'rgba(0,0,0,0.2)';
                    }
                    updateGridDataInput();
                });
            });

            document.getElementById('walls-form').addEventListener('submit', () => {
                updateGridDataInput();
            });
        });
    </script>
    <?php echo $chat['script']; ?>
</body>
</html>