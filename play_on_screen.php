<?php
include 'chat.php';
$chat = setupChat();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play on Screen - Board Game Generator</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>
    <?php echo $chat['styles']; ?>
</head>
<body>
    <?php echo $chat['ui']; ?>
    <nav class="navbar">
        <a href="index.php">Simple Board Game Generator</a>
        <a href="explore.php">Explore</a>
        <a href="play_on_screen.php">Play on a Screen</a>
    </nav>
    
    <div class="page-wrapper">
        <div class="info-box">
            <h4>Pawns</h4>
            <div class="pawn-container">
                <?php for($i = 1; $i <= 3; $i++): ?>
                <div class="pawn" draggable="true" data-pawn-id="<?php echo $i; ?>">
                    <img src="images/pawn<?php echo $i; ?>.png" alt="Pawn <?php echo $i; ?>" style="width:50px; height:50px;">
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="container">
            <h1>Play on Screen</h1>
            <p>Upload your PDF board game and play directly on your screen. Drag pawns from the left panel onto the board.</p>
            
            <div class="pdf-upload-container">
                <input type="file" id="pdf-upload" accept="application/pdf">
            </div>
            
            <div id="pdf-viewer" class="pdf-viewer" style="position: relative; min-height: 600px; border: 1px solid black;">
                <p>Upload a PDF to view it here</p>
            </div>
            
            <div id="pdf-navigation" style="display: none;">
                <button id="prev-page" disabled>Previous Page</button>
                <button id="next-page">Next Page</button>
                <span>Page <span id="current-page">1</span> of <span id="total-pages">1</span></span>
            </div>
        </div>
        
        <div class="info-box">
            <h3>Dice Roller</h3>
            <div class="dice-container">
                <div id="dice" class="dice" style="width: 50px; height: 50px; border: 1px solid black; text-align: center; line-height: 50px;">1</div>
                <button id="roll-dice">Roll Dice</button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pdfUpload = document.getElementById('pdf-upload');
            const pdfViewer = document.getElementById('pdf-viewer');
            const pdfNavigation = document.getElementById('pdf-navigation');
            const prevPageBtn = document.getElementById('prev-page');
            const nextPageBtn = document.getElementById('next-page');
            const currentPageSpan = document.getElementById('current-page');
            const totalPagesSpan = document.getElementById('total-pages');
            
            let pdfDoc = null;
            let pageNum = 1;

            pdfUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type === 'application/pdf') {
                    const fileURL = URL.createObjectURL(file);
                    pdfjsLib.getDocument(fileURL).promise.then(function(pdf) {
                        pdfDoc = pdf;
                        totalPagesSpan.textContent = pdf.numPages;
                        pdfNavigation.style.display = pdf.numPages > 1 ? 'block' : 'none';
                        pageNum = 1;
                        renderPage(pageNum);
                    });
                }
            });
            
            function renderPage(num) {
                pdfDoc.getPage(num).then(function(page) {
                    const scale = 1.5;
                    const viewport = page.getViewport({ scale: scale });
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    pdfViewer.innerHTML = '';
                    pdfViewer.appendChild(canvas);
                    
                    page.render({ canvasContext: context, viewport: viewport });
                    currentPageSpan.textContent = num;
                    updateButtonStates();
                });
            }
            
            function updateButtonStates() {
                prevPageBtn.disabled = pageNum <= 1;
                nextPageBtn.disabled = pageNum >= pdfDoc.numPages;
            }
            
            prevPageBtn.addEventListener('click', () => {
                if (pageNum <= 1) return;
                pageNum--;
                renderPage(pageNum);
            });
            
            nextPageBtn.addEventListener('click', () => {
                if (pageNum >= pdfDoc.numPages) return;
                pageNum++;
                renderPage(pageNum);
            });
            
            const dice = document.getElementById('dice');
            const rollDiceBtn = document.getElementById('roll-dice');
            
            rollDiceBtn.addEventListener('click', () => {
                const finalNumber = Math.floor(Math.random() * 6) + 1;
                dice.textContent = finalNumber;
            });
            
            const pawns = document.querySelectorAll('.pawn');
            pawns.forEach(pawn => {
                pawn.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', e.target.dataset.pawnId);
                });
            });

            pdfViewer.addEventListener('dragover', (e) => {
                e.preventDefault();
            });

            pdfViewer.addEventListener('drop', (e) => {
                e.preventDefault();
                const pawnId = e.dataTransfer.getData('text/plain');
                const pawnImgSrc = document.querySelector(`.pawn[data-pawn-id='${pawnId}'] img`).src;
                
                const rect = pdfViewer.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                createDraggablePawn(pawnImgSrc, x, y);
            });

            function createDraggablePawn(imgSrc, x, y) {
                const draggablePawn = document.createElement('img');
                draggablePawn.src = imgSrc;
                draggablePawn.style.position = 'absolute';
                draggablePawn.style.left = (x - 25) + 'px';
                draggablePawn.style.top = (y - 25) + 'px';
                draggablePawn.style.width = '50px';
                draggablePawn.style.height = '50px';
                draggablePawn.style.cursor = 'grab';
                pdfViewer.appendChild(draggablePawn);

                let isDragging = false;
                draggablePawn.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    draggablePawn.style.cursor = 'grabbing';
                });

                document.addEventListener('mousemove', (e) => {
                    if (isDragging) {
                        const rect = pdfViewer.getBoundingClientRect();
                        const newX = e.clientX - rect.left;
                        const newY = e.clientY - rect.top;
                        draggablePawn.style.left = (newX - 25) + 'px';
                        draggablePawn.style.top = (newY - 25) + 'px';
                    }
                });

                document.addEventListener('mouseup', () => {
                    isDragging = false;
                    draggablePawn.style.cursor = 'grab';
                });
            }
        });
    </script>
    <?php echo $chat['script']; ?>
</body>
</html>