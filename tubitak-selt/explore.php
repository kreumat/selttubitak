<?php
/**
 * Explore Page - Board Game Collection
 * 
 * Purpose: This page displays a collection of board games that users can browse,
 * search, and explore. Each game has a thumbnail, name, and link to its dedicated page.
 * 
 * Dependencies:
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
    <title>Explore Board Games - SELT</title>
    <link rel="icon" type="image/png" href="images/selt_logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php echo $chat['styles']; ?>
    <style>
        /* Additional styles specific to Explore page */
        .search-container {
            margin-bottom: 2rem;
            width: 100%;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .search-bar {
            display: flex;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--divider-color);
        }
        
        .search-input {
            flex-grow: 1;
            padding: 1rem 1.5rem;
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            color: var(--text-dark);
        }
        
        .search-input:focus {
            outline: none;
        }
        
        .search-button {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 0 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-button:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        
        .search-button i {
            font-size: 1.25rem;
        }
        
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .game-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--divider-color);
        }
        
        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .game-thumbnail {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border-bottom: 1px solid var(--divider-color);
        }
        
        .game-info {
            padding: 1.5rem;
        }
        
        .game-title {
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            color: var(--text-dark);
        }
        
        .game-title::after {
            display: none;
        }
        
        .more-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .more-btn:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
    
    <div class="container">
        <h1>Explore Board Games</h1>
        
        <!-- Search Bar 
        <div class="search-container">
            <form id="searchForm" onsubmit="return searchGames();">
                <div class="search-bar">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search games by name...">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div> ŞU ANLIK ÇALIŞMIYOR MAALESEF -->
        
        <!-- Games Grid -->
        <div class="games-grid" id="gamesGrid">
            <!-- Game 1 -->
            <div class="game-card" data-name="board game 1">
                <img src="images/buy_me_a_coffee.png" alt="Board Game 1" class="game-thumbnail">
                <div class="game-info">
                    <h3 class="game-title">Board Game 1</h3>
                    <a href="boardgames/pages/game1.php" class="more-btn">More</a>
                </div>
            </div>
            
            <!-- Game 2 -->
            <div class="game-card" data-name="board game 2">
                <img src="images/buy_me_a_coffee.png" alt="Board Game 2" class="game-thumbnail">
                <div class="game-info">
                    <h3 class="game-title">Board Game 2</h3>
                    <a href="boardgames/pages/game2.php" class="more-btn">More</a>
                </div>
            </div>
            
            <!-- Game 3 
            <div class="game-card" data-name="board game 3">
                <img src="boardgames/boardgamepicture1.png" alt="Board Game 3" class="game-thumbnail">
                <div class="game-info">
                    <h3 class="game-title">Board Game 3</h3>
                    <a href="boardgames/pages/game3.php" class="more-btn">More</a>
                </div>
            </div>-->
            
            <!-- Game 4 
            <div class="game-card" data-name="board game 4">
                <img src="boardgames/boardgamepicture1.png" alt="Board Game 4" class="game-thumbnail">
                <div class="game-info">
                    <h3 class="game-title">Board Game 4</h3>
                    <a href="boardgames/pages/game4.php" class="more-btn">More</a>
                </div>
            </div>-->
            
            <!-- Game 5 
            <div class="game-card" data-name="board game 5">
                <img src="boardgames/boardgamepicture1.png" alt="Board Game 5" class="game-thumbnail">
                <div class="game-info">
                    <h3 class="game-title">Board Game 5</h3>
                    <a href="boardgames/pages/game5.php" class="more-btn">More</a>
                </div>
            </div>-->
            
            <!-- Game 6 
            <div class="game-card" data-name="board game 6">
                <img src="boardgames/boardgamepicture1.png" alt="Board Game 6" class="game-thumbnail">
                <div class="game-info">
                    <h3 class="game-title">Board Game 6</h3>
                    <a href="boardgames/pages/game6.php" class="more-btn">More</a>
                </div>
            </div>-->
            
            <!-- Game 7 
            <div class="game-card" data-name="board game 7">
                <img src="boardgames/boardgamepicture1.png" alt="Board Game 7" class="game-thumbnail">
                <div class="game-info">
                    <h3 class="game-title">Board Game 7</h3>
                    <a href="boardgames/pages/game7.php" class="more-btn">More</a>
                </div> -->
            </div>
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
    
    <script>
        // Search functionality
        function searchGames() {
            const searchInput = document.getElementById('searchInput');
            const searchTerm = searchInput.value.toLowerCase();
            const gameCards = document.querySelectorAll('.game-card');
            
            gameCards.forEach(card => {
                const gameName = card.getAttribute('data-name');
                if (gameName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Prevent form submission
            return false;
        }
    </script>
    
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