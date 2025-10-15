<?php
include 'chat.php';
$chat = setupChat();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Board Games - SELT</title>
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
    
    <div class="container">
        <h1>Explore Board Games</h1>
        
        <div class="games-grid" id="gamesGrid">
            <div class="game-card" data-name="board game 1">
                <img src="images/buy_me_a_coffee.png" alt="Board Game 1" class="game-thumbnail" style="width:100px;height:100px;">
                <div class="game-info">
                    <h3 class="game-title">Board Game 1</h3>
                    <a href="boardgames/pages/game1.php">More</a>
                </div>
            </div>
            
            <div class="game-card" data-name="board game 2">
                <img src="images/buy_me_a_coffee.png" alt="Board Game 2" class="game-thumbnail" style="width:100px;height:100px;">
                <div class="game-info">
                    <h3 class="game-title">Board Game 2</h3>
                    <a href="boardgames/pages/game2.php">More</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo $chat['script']; ?>
</body>
</html>