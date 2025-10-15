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
    <title>Board Game Generator - Final Board</title>
    <link rel="stylesheet" href="style.css">
    <?php echo $chat['styles']; ?>
</head>
<body class="result-page">
    <?php echo $chat['ui']; ?>
    <nav class="navbar">
        <a href="index.php">Simple Board Game Generator</a>
        <a href="explore.php">Explore</a>
        <a href="play_on_screen.php">Play on a Screen</a>
    </nav>
    
    <div class="stepper-container">
        <p>Step 5: Print Board</p>
    </div>

    <div class="page-wrapper screen-only">
        <div class="container">
            <?php
            $boardTitle = isset($_POST['board_title']) ? htmlspecialchars($_POST['board_title']) : "Your Generated Board";
            ?>

            <?php if (isset($_POST['gridData']) && isset($_POST['cell_text'])): ?>
            <form action="walls.php" method="post">
                <input type="hidden" name="gridData" value='<?php echo htmlspecialchars($_POST['gridData']); ?>'>
                <?php foreach ($_POST['cell_text'] as $key => $value): ?>
                    <input type="hidden" name="cell_text[<?php echo htmlspecialchars($key); ?>]" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false); ?>">
                <?php endforeach; ?>
                <button type="submit">Go back</button>
            </form>
            <?php endif; ?>

            <h1><?php echo $boardTitle; ?></h1>
            <button onclick="window.print()">Print Board</button>

            <div class="grid-container">
                <?php
                if (isset($_POST['gridData'])) {
                    $gridData = json_decode($_POST['gridData'], true);
                    $rows = $gridData['rows'];
                    $cols = $gridData['cols'];
                    $cells = $gridData['cells'];
                    $cellTexts = $_POST['cell_text'] ?? [];
                    $hasCoordinates = isset($gridData['coordinates']);
                    $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];
                    $walls = $gridData['walls'] ?? [];

                    echo '<div class="result-grid" style="--rows: '.$rows.'; --cols: '.$cols.';">';

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
                            
                            // Walls rendering can be simplified or removed for the rudimentary look
                            
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                } else {
                    echo '<p>No grid data found.</p>';
                }
                ?>
            </div>
            <a href="index.php">Create New Board</a>
        </div>
    </div>
    
    <div class="print-only-container" style="display: none;">
        <h1><?php echo $boardTitle; ?></h1>
        <?php
        if (isset($_POST['gridData'])) {
            // Re-render for print, similar to above but without interactive elements
            // This part can be a simplified copy of the screen rendering logic
        }
        ?>
    </div>
    <?php echo $chat['script']; ?>
</body>
</html>