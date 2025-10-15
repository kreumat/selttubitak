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
    <title>Board Game Generator - Add Images</title>
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
        <p>Step 3: Add Images</p>
    </div>

    <div class="page-wrapper">
        <div class="info-box">
            <h3>Image Upload</h3>
            <div class="image-upload-container">
                <input type="file" id="image-upload" accept="image/*" multiple>
                <div class="thumbnails-container" id="thumbnails-container"></div>
            </div>
        </div>
        
        <div class="container">
            <h1>Add Images to Your Board</h1>

            <?php
            if (isset($_POST['gridData']) && isset($_POST['cell_text'])) {
                $gridData = json_decode($_POST['gridData'], true);
                $rows = $gridData['rows'];
                $cols = $gridData['cols'];
                $cells = $gridData['cells'];
                $cellTexts = $_POST['cell_text'];
                $hasCoordinates = isset($gridData['coordinates']);
                $coordinates = $hasCoordinates ? $gridData['coordinates'] : [];

                if (!isset($gridData['cellImages'])) {
                    $gridData['cellImages'] = [];
                }
                if (isset($_SESSION['cellImages']) && !empty($_SESSION['cellImages'])) {
                    $gridData['cellImages'] = $_SESSION['cellImages'];
                }

                echo '<form action="edit.php" method="post" id="go-back-form">';
                echo '<input type="hidden" name="gridData" value=\'' . htmlspecialchars(json_encode($gridData)) . '\'>';
                echo '<input type="hidden" name="returnFromImages" value="true">';
                foreach ($cellTexts as $key => $value) {
                    echo '<input type="hidden" name="cell_text[' . htmlspecialchars($key) . ']" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false) . '">';
                }
                echo '<button type="submit">Go back</button>';
                echo '</form>';

                echo '<form action="walls.php" method="post" id="images-form">';
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
                        $cellContent = '';
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
                            $cellContent = 'START';
                        } elseif ($isFinish) {
                            $cellClass = 'finish active';
                            $cellContent = 'FINISH';
                        } elseif (in_array($cellIndex, $cells) || ($hasCoordinates && isCoordinateActive($x, $y, $coordinates))) {
                            $cellClass = 'active';
                            if (isset($cellTexts[$coordKey])) {
                                $cellContent = $cellTexts[$coordKey];
                            } elseif (isset($cellTexts[$cellIndex])) {
                                $cellContent = $cellTexts[$cellIndex];
                            }
                            if (isset($gridData['cellColors'][$coordKey])) {
                                $cellClass .= ' color-' . $gridData['cellColors'][$coordKey];
                            }
                        }

                        echo '<div class="cell '.$cellClass.'" data-x="'.$x.'" data-y="'.$y.'">';
                        echo '<div class="cell-image" data-cell-coord="'.$coordKey.'"></div>';
                        echo '<div class="cell-text">'.$cellContent.'</div>';
                        echo '</div>';
                    }
                }

                echo '</div>';
                echo '</div>';

                echo '<input type="submit" value="Go to the Next Step: Add Walls">';
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
            let gridData = <?php echo json_encode($gridData ?? []); ?>;
            if (!gridData.cellImages) gridData.cellImages = {};

            const imageUploadInput = document.getElementById('image-upload');
            const thumbnailsContainer = document.getElementById('thumbnails-container');
            const gridDataInput = document.getElementById('gridDataInput');
            let selectedImage = null;

            imageUploadInput.addEventListener('change', function(event) {
                const files = event.target.files;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!file.type.startsWith('image/')) continue;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageData = e.target.result;
                        const thumbnail = document.createElement('img');
                        thumbnail.src = imageData;
                        thumbnail.style.width = '50px';
                        thumbnail.style.height = '50px';
                        thumbnail.style.cursor = 'pointer';
                        thumbnail.addEventListener('click', () => {
                            selectedImage = imageData;
                            document.querySelectorAll('.thumbnails-container img').forEach(img => img.style.border = 'none');
                            thumbnail.style.border = '2px solid blue';
                        });
                        thumbnailsContainer.appendChild(thumbnail);
                    };
                    reader.readAsDataURL(file);
                }
            });

            document.querySelectorAll('.cell.active').forEach(cell => {
                cell.addEventListener('click', () => {
                    if (selectedImage) {
                        const x = cell.dataset.x;
                        const y = cell.dataset.y;
                        const cellCoord = `${x},${y}`;
                        const cellImage = cell.querySelector('.cell-image');
                        if (cellImage) {
                            cellImage.style.backgroundImage = `url(${selectedImage})`;
                            gridData.cellImages[cellCoord] = selectedImage;
                            updateGridDataInput();
                            updateSessionImageData(cellCoord, selectedImage);
                        }
                    }
                });
            });

            function updateGridDataInput() {
                const gridDataForForm = { ...gridData };
                if (gridDataForForm.cellImages) {
                    gridDataForForm.hasSessionImages = true;
                    delete gridDataForForm.cellImages;
                }
                gridDataInput.value = JSON.stringify(gridDataForForm);
            }

            function updateSessionImageData(cellCoord, imageData) {
                const formData = new FormData();
                formData.append('action', 'setImage');
                formData.append('coord', cellCoord);
                formData.append('imageData', imageData);

                fetch('session_handler.php', { method: 'POST', body: formData })
                    .then(response => response.text())
                    .then(data => console.log('Session updated:', data))
                    .catch(error => console.error('Error updating session:', error));
            }
            
            // Initialize images on load
            if (gridData.cellImages) {
                for (const cellCoord in gridData.cellImages) {
                    const imageData = gridData.cellImages[cellCoord];
                    const cellImage = document.querySelector(`.cell-image[data-cell-coord="${cellCoord}"]`);
                    if (cellImage && imageData) {
                        cellImage.style.backgroundImage = `url(${imageData})`;
                    }
                }
            }
        });
    </script>
    <?php echo $chat['script']; ?>
</body>
</html>