<?php
/**
 * Board Game Generator - Play on Screen
 * 
 * Purpose: This page allows users to upload and play board games directly on screen
 * without printing. It provides functionality to:
 * 1. Display draggable pawns that users can move around the board
 * 2. Upload and view PDF board games
 * 3. Roll dice to play the game
 * 
 * Features:
 * - 9 draggable pawns that can be placed anywhere on the screen
 * - PDF uploader and viewer (not using browser's built-in viewer)
 * - Dice roller that generates random numbers 1-6
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
    <title>Play on Screen - Board Game Generator</title>
    <link rel="icon" type="image/png" href="images/selt_logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- PDF.js library for PDF rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        // Set PDF.js worker source
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>
    <?php echo $chat['styles']; ?>
    <style>
        /* Custom styles for play on screen */
        .pawn-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .pawn {
            width: 60px;
            height: 60px;
            cursor: grab;
            transition: transform 0.2s ease;
            user-select: none;
            margin: 0 auto;
        }
        
        .pawn:hover {
            transform: scale(1.1);
        }
        
        .pawn:active {
            cursor: grabbing;
        }
        
        .pawn img {
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        
        .pdf-upload-container {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 10px;
            background-color: rgba(237, 242, 247, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .pdf-viewer {
            width: 100%;
            min-height: 500px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: auto;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .pdf-viewer-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 500px;
            color: var(--text-muted);
        }
        
        .pdf-viewer-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .dice-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 2rem;
        }
        
        .dice {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            border: 2px solid var(--primary-color);
        }
        
        .dice:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .dice-rolling {
            animation: diceRoll 0.5s ease-in-out;
        }
        
        @keyframes diceRoll {
            0% { transform: rotate(0deg); }
            20% { transform: rotate(-30deg); }
            40% { transform: rotate(30deg); }
            60% { transform: rotate(-15deg); }
            80% { transform: rotate(15deg); }
            100% { transform: rotate(0deg); }
        }
        
        .page-canvas {
            margin: 10px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        
        /* Draggable pawns that stay in position and scroll with content */
        .draggable-pawn {
            position: absolute;
            width: 40px;
            height: 40px;
            z-index: 1000;
            cursor: grab;
            user-select: none;
        }
        
        .draggable-pawn:active {
            cursor: grabbing;
        }
        
        .draggable-pawn img {
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        
        /* Custom file input */
        .file-input-container {
            position: relative;
            margin-bottom: 1.5rem;
            width: 100%;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            text-align: center;
        }
        
        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        
        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .file-name {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            text-align: center;
        }
        
        /* Navigation buttons for PDF */
        .pdf-nav-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        
        .pdf-nav-button {
            padding: 0.5rem 1rem;
            border: none;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .pdf-nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        
        .pdf-nav-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .page-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
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
    
    <div class="page-wrapper">
        <!-- Left panel - Pawns -->
        <div class="info-box">
         
            <h4>Pawns</h4>
            <div class="pawn-container">
                <?php for($i = 1; $i <= 3; $i++): ?>
                <div class="pawn" draggable="true" data-pawn-id="<?php echo $i; ?>">
                    <img src="images/pawn<?php echo $i; ?>.png" alt="Pawn <?php echo $i; ?>">
                </div>
                <?php endfor; ?>
            </div>
            

        </div>
        
        <!-- Middle panel - PDF Viewer -->
        <div class="container">
            <h1>Play on Screen</h1>
            <p class="instructions">Upload your PDF board game and play directly on your screen. Drag pawns from the left panel onto the board.</p>
            
            <div class="pdf-upload-container">
                <div class="file-input-container">
                    <label for="pdf-upload" class="file-input-label">
                        <i class="fas fa-upload"></i> Upload Board Game (PDF)
                    </label>
                    <input type="file" id="pdf-upload" class="file-input" accept="application/pdf">
                    <div class="file-name" id="file-name">No file selected</div>
                </div>
            </div>
            
            <div id="pdf-viewer" class="pdf-viewer" style="position: relative; min-height: 600px;">
                <div class="pdf-viewer-placeholder">
                    <i class="fas fa-file-pdf"></i>
                    <p>Upload a PDF board game to view it here</p>
                </div>
            </div>
            
            <div id="pdf-navigation" style="display: none; text-align: center;">
                <div class="pdf-nav-buttons">
                    <button id="prev-page" class="pdf-nav-button" disabled>
                        <i class="fas fa-chevron-left"></i> Previous Page
                    </button>
                    <button id="next-page" class="pdf-nav-button">
                        Next Page <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="page-info">
                    Page <span id="current-page">1</span> of <span id="total-pages">1</span>
                </div>
            </div>
        </div>
        
        <!-- Right panel - Dice -->
        <div class="info-box team-info-box">
            <h3>Dice Roller</h3>
            
            <div class="dice-container" style="margin-bottom: 2rem;">
                <div id="dice" class="dice">1</div>
                <button id="roll-dice" class="special-button" style="background: linear-gradient(135deg, var(--accent-color) 0%, var(--secondary-color) 100%);">Roll Dice</button>
            </div>

        </div>
    </div>
    

    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // PDF viewer functionality
            const pdfUpload = document.getElementById('pdf-upload');
            const pdfViewer = document.getElementById('pdf-viewer');
            const fileName = document.getElementById('file-name');
            const pdfNavigation = document.getElementById('pdf-navigation');
            const prevPageBtn = document.getElementById('prev-page');
            const nextPageBtn = document.getElementById('next-page');
            const currentPageSpan = document.getElementById('current-page');
            const totalPagesSpan = document.getElementById('total-pages');
            
            let pdfDoc = null;
            let pageNum = 1;
            let pageRendering = false;
            let pageNumPending = null;
            let scale = 1.0;
            
            // Listen for file input changes
            pdfUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type === 'application/pdf') {
                    // Update file name display
                    fileName.textContent = file.name;
                    
                    // Clear the PDF viewer
                    pdfViewer.innerHTML = '';
                    
                    // Create a URL for the PDF file
                    const fileURL = URL.createObjectURL(file);
                    
                    // Load the PDF using PDF.js
                    pdfjsLib.getDocument(fileURL).promise.then(function(pdf) {
                        pdfDoc = pdf;
                        totalPagesSpan.textContent = pdf.numPages;
                        
                        // Show navigation if more than one page
                        if (pdf.numPages > 1) {
                            pdfNavigation.style.display = 'block';
                        } else {
                            pdfNavigation.style.display = 'none';
                        }
                        
                        // Initial page render
                        pageNum = 1;
                        renderPage(pageNum);
                    }).catch(function(error) {
                        console.error('Error loading PDF:', error);
                        pdfViewer.innerHTML = `
                            <div class="pdf-viewer-placeholder">
                                <i class="fas fa-exclamation-triangle" style="color: #e53e3e;"></i>
                                <p>Error loading PDF: ${error.message}</p>
                            </div>
                        `;
                    });
                } else if (file) {
                    // Not a PDF file
                    fileName.textContent = 'Invalid file type. Please select a PDF.';
                    pdfViewer.innerHTML = `
                        <div class="pdf-viewer-placeholder">
                            <i class="fas fa-exclamation-triangle" style="color: #e53e3e;"></i>
                            <p>Invalid file type. Please select a PDF.</p>
                        </div>
                    `;
                }
            });
            
            // Render a specific page of the PDF
            function renderPage(num) {
                pageRendering = true;
                
                // Get the page
                pdfDoc.getPage(num).then(function(page) {
                    // Set scale based on the container width
                    const viewport = page.getViewport({ scale: scale });
                    const containerWidth = pdfViewer.clientWidth - 40; // 20px padding on each side
                    scale = containerWidth / viewport.width;
                    const adjustedViewport = page.getViewport({ scale: scale });
                    
                    // Create a canvas for the page
                    const canvas = document.createElement('canvas');
                    canvas.className = 'page-canvas';
                    const context = canvas.getContext('2d');
                    canvas.height = adjustedViewport.height;
                    canvas.width = adjustedViewport.width;
                    
                    // Add the canvas to the viewer
                    pdfViewer.appendChild(canvas);
                    
                    // Render the page content
                    const renderContext = {
                        canvasContext: context,
                        viewport: adjustedViewport
                    };
                    
                    const renderTask = page.render(renderContext);
                    
                    // When rendering is complete
                    renderTask.promise.then(function() {
                        pageRendering = false;
                        
                        // Update current page display
                        currentPageSpan.textContent = num;
                        
                        // Handle any pending page requests
                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                        
                        // Update button states
                        updateButtonStates();
                    });
                });
            }
            
            // Update button states based on current page
            function updateButtonStates() {
                prevPageBtn.disabled = pageNum <= 1;
                nextPageBtn.disabled = pageNum >= pdfDoc.numPages;
            }
            
            // Go to previous page
            function previousPage() {
                if (pageNum <= 1) return;
                pageNum--;
                queueRenderPage(pageNum);
            }
            
            // Go to next page
            function nextPage() {
                if (pageNum >= pdfDoc.numPages) return;
                pageNum++;
                queueRenderPage(pageNum);
            }
            
            // Queue rendering of a page
            function queueRenderPage(num) {
                if (pageRendering) {
                    pageNumPending = num;
                } else {
                    // Clear the PDF viewer before rendering new page
                    pdfViewer.innerHTML = '';
                    renderPage(num);
                }
            }
            
            // Navigation button event listeners
            prevPageBtn.addEventListener('click', previousPage);
            nextPageBtn.addEventListener('click', nextPage);
            
            // Dice rolling functionality
            const dice = document.getElementById('dice');
            const rollDiceBtn = document.getElementById('roll-dice');
            
            function rollDice() {
                // Disable button during animation
                rollDiceBtn.disabled = true;
                
                // Add rolling animation
                dice.classList.add('dice-rolling');
                
                // Show random numbers during animation
                let rollCount = 0;
                const rollInterval = setInterval(() => {
                    dice.textContent = Math.floor(Math.random() * 6) + 1;
                    rollCount++;
                    
                    if (rollCount >= 10) {
                        clearInterval(rollInterval);
                        // Final random number
                        const finalNumber = Math.floor(Math.random() * 6) + 1;
                        dice.textContent = finalNumber;
                        
                        // Remove rolling animation
                        setTimeout(() => {
                            dice.classList.remove('dice-rolling');
                            rollDiceBtn.disabled = false;
                        }, 500);
                    }
                }, 50);
            }
            
            // Event listeners for dice
            dice.addEventListener('click', rollDice);
            rollDiceBtn.addEventListener('click', rollDice);
            
            // Draggable pawns functionality
            const pawns = document.querySelectorAll('.pawn');
            
            // Store active draggable pawns
            const activePawns = [];
            
            pawns.forEach(pawn => {
                pawn.addEventListener('dragstart', handleDragStart);
            });
            
            function handleDragStart(e) {
                const pawnId = this.getAttribute('data-pawn-id');
                const pawnImg = this.querySelector('img').src;
                
                // Store drag data
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    id: pawnId,
                    img: pawnImg
                }));
                
                // Set drag effect
                e.dataTransfer.effectAllowed = 'copy';
            }
            
            // Make the entire document a drop target for pawns
            document.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            });
            
            // Make the PDF viewer a drop target for pawns
            pdfViewer.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            });
            
            pdfViewer.addEventListener('drop', function(e) {
                e.preventDefault();
                
                // Get the dropped pawn data
                const data = e.dataTransfer.getData('text/plain');
                if (!data) return;
                
                try {
                    const pawnData = JSON.parse(data);
                    
                    // Get position relative to PDF viewer
                    const rect = pdfViewer.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    // Create a new draggable pawn element
                    createDraggablePawn(pawnData.id, pawnData.img, x, y);
                } catch (error) {
                    console.error('Error parsing drag data:', error);
                }
            });
            
            function createDraggablePawn(id, imgSrc, x, y) {
                // Create a new draggable pawn
                const draggablePawn = document.createElement('div');
                draggablePawn.className = 'draggable-pawn';
                draggablePawn.setAttribute('data-pawn-id', id);
                draggablePawn.style.left = (x - 20) + 'px'; // Center horizontally
                draggablePawn.style.top = (y - 20) + 'px'; // Center vertically
                
                // Create the image
                const img = document.createElement('img');
                img.src = imgSrc;
                img.alt = `Pawn ${id}`;
                
                // Add the image to the pawn
                draggablePawn.appendChild(img);
                
                // Add to PDF viewer container instead of body
                pdfViewer.appendChild(draggablePawn);
                
                // Add to active pawns
                activePawns.push(draggablePawn);
                
                // Make the pawn draggable
                let isDragging = false;
                let offsetX = 0;
                let offsetY = 0;
                
                // Mouse down event
                draggablePawn.addEventListener('mousedown', function(e) {
                    isDragging = true;
                    
                    // Get the pawn's bounding rectangle
                    const pawnRect = draggablePawn.getBoundingClientRect();
                    
                    // Calculate the offset as the difference between the mouse position
                    // and the pawn's position
                    offsetX = e.clientX - pawnRect.left;
                    offsetY = e.clientY - pawnRect.top;
                    
                    // Change cursor to grabbing
                    draggablePawn.style.cursor = 'grabbing';
                    
                    // Prevent default behavior
                    e.preventDefault();
                });
                
                // Mouse move event
                document.addEventListener('mousemove', function(e) {
                    if (!isDragging) return;
                    
                    // Get position relative to PDF viewer
                    const rect = pdfViewer.getBoundingClientRect();
                    
                    // Calculate new position by subtracting the offset from the mouse position
                    // to ensure the pawn stays where the user grabbed it
                    const left = e.clientX - rect.left - offsetX;
                    const top = e.clientY - rect.top - offsetY;
                    
                    // Constrain to PDF viewer boundaries
                    const maxX = pdfViewer.clientWidth - draggablePawn.offsetWidth;
                    const maxY = pdfViewer.clientHeight - draggablePawn.offsetHeight;
                    
                    const constrainedLeft = Math.max(0, Math.min(left, maxX));
                    const constrainedTop = Math.max(0, Math.min(top, maxY));
                    
                    // Apply the new position
                    draggablePawn.style.left = constrainedLeft + 'px';
                    draggablePawn.style.top = constrainedTop + 'px';
                });
                
                // Mouse up event
                document.addEventListener('mouseup', function() {
                    if (isDragging) {
                        isDragging = false;
                        draggablePawn.style.cursor = 'grab';
                    }
                });
                
                // Double click to remove
                draggablePawn.addEventListener('dblclick', function() {
                    // Remove from active pawns
                    const index = activePawns.indexOf(draggablePawn);
                    if (index !== -1) {
                        activePawns.splice(index, 1);
                    }
                    
                    // Remove from DOM
                    draggablePawn.remove();
                });
            }
        });
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