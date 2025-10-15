<?php
/**
 * Board Game Page - Game 4
 * 
 * Purpose: This page displays details for a specific board game,
 * including its image, description, and download options.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Game 4 - SELT</title>
    <link rel="icon" type="image/png" href="../../images/selt_logo.png">
    <link rel="stylesheet" href="../../style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles specific to game pages */
        .game-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .game-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            width: 100%;
            text-align: center;
        }
        
        .game-image {
            max-width: 400px;
            height: auto;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--divider-color);
        }
        
        .game-details {
            width: 100%;
            max-width: 800px;
        }
        
        .game-description {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            line-height: 1.8;
        }
        
        .download-section {
            background-color: rgba(58, 114, 202, 0.1);
            padding: 1.5rem 2rem;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 2rem;
        }
        
        .download-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 0.85rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .download-button:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .back-to-explore {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }
        
        .back-to-explore:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <span class="navbar-brand-silver">SILVER</span>
            <img src="../../images/selt_logo.png" alt="SELT Logo" class="navbar-brand-logo">
            <span class="navbar-brand-elt">ELT</span>
        </div>
        <div class="mobile-menu-toggle" id="mobileMenuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="navbar-nav" id="navbarNav">
            <a href="../../main.php" class="navbar-nav-item">Main Page</a>
            <a href="../../index.php" class="navbar-nav-item">Simple Board Game Generator</a>
            <a href="../../explore.php" class="navbar-nav-item">Explore</a>
            <a href="../../about.php" class="navbar-nav-item">About Us</a>
            <a href="../../play_on_screen.php" class="navbar-nav-item">Play on a Screen</a>
        </div>
    </nav>
    
    <div class="container">
        <a href="../../explore.php" class="back-to-explore">
            <i class="fas fa-arrow-left"></i> Back to Explore
        </a>
        
        <div class="game-container">
            <div class="game-header">
                <h1>Board Game 4</h1>
                <img src="../../boardgames/boardgamepicture1.png" alt="Board Game 4" class="game-image">
            </div>
            
            <div class="game-details">
                <div class="game-description">
                    <h2>About This Game</h2>
                    <p>
                        Board Game 4 is a grammar-focused educational board game designed to make learning 
                        language structures fun and interactive. This game targets specific grammar points 
                        through engaging gameplay mechanics and challenges.
                    </p>
                    <p>
                        Players move around the board, encountering various grammar tasks such as forming 
                        correct sentences, identifying parts of speech, and correcting common grammar mistakes. 
                        The game incorporates visual cues and examples to reinforce learning.
                    </p>
                    <p>
                        With multiple difficulty levels available, Board Game 4 can be adapted for different 
                        learning stages and abilities. It's an excellent resource for classrooms, tutoring 
                        sessions, or independent study to strengthen grammar skills in an enjoyable way.
                    </p>
                </div>
                
                <div class="download-section">
                    <h3>Download Game Materials</h3>
                    <p>
                        Click the button below to download the printable PDF version of this game. 
                        The PDF includes the game board, rules, and all necessary materials to play.
                    </p>
                    <a href="../../boardgames/boardgame1.pdf" class="download-button" download>
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-nav">
            <a href="../../main.php" class="footer-nav-item">Main Page</a>
            <a href="../../index.php" class="footer-nav-item">Board Game Generator</a>
            <a href="../../explore.php" class="footer-nav-item">Explore</a>
            <a href="../../about.php" class="footer-nav-item">About Us</a>
            <a href="../../play_on_screen.php" class="footer-nav-item">Play on a Screen</a>
        </div>
        <div class="footer-copyright">
            &copy; <?php echo date('Y'); ?> SILVER ELT. All rights reserved.
        </div>
    </footer>
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
</body>
</html>