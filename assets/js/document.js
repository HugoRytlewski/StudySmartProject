pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

document.addEventListener("DOMContentLoaded", function () {
    let pdfUrl = document.getElementById("pdf-container").getAttribute("data-pdf-url"),
        pdfContainer = document.getElementById("pdf-container"),
        isHighlighting = false, isAnnotating = false, isDrawing = false,
        pageCanvasMap = {}, pageContainerMap = {}, highlightsData = {},
        startX, startY, currentX, currentY, startPage = null,
        highlightBtn = document.getElementById("highlight-btn"),
        annotateBtn = document.getElementById("annotate-btn"),
        exitModeBtn = document.getElementById("exit-mode-btn");

    pdfContainer.style.position = "relative";
    
    window.debugHighlights = () => {
        console.log(JSON.stringify(highlightsData, null, 2));
        return highlightsData;
    };

    function getPageInfoFromEvent(event) {
        let current = event.target, pageContainer = null;
        
        while (current && current !== document.body) {
            if (current.classList.contains('pdf-page-container')) {
                pageContainer = current;
                break;
            }
            current = current.parentElement;
        }
        
        if (!pageContainer) {
            document.querySelectorAll('.pdf-page-container').forEach(page => {
                const rect = page.getBoundingClientRect();
                if (event.clientX >= rect.left && event.clientX <= rect.right && 
                    event.clientY >= rect.top && event.clientY <= rect.bottom) {
                    pageContainer = page;
                }
            });
        }
        
        if (!pageContainer) return null;
        
        const pageNum = parseInt(pageContainer.getAttribute('data-page')),
              rect = pageContainer.getBoundingClientRect();
        
        return {
            pageNum: pageNum,
            x: event.clientX - rect.left,
            y: event.clientY - rect.top
        };
    }

    function redrawHighlights(pageNum) {
        const canvas = pageCanvasMap[pageNum];
        if (!canvas) return;
        
        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        if (highlightsData[pageNum]) {
            highlightsData[pageNum].forEach(h => {
                ctx.fillStyle = h.color || "rgba(255, 255, 0, 0.3)";
                ctx.fillRect(h.x, h.y, h.width, h.height);
            });
        }
    }
    
    function redrawAllHighlights() {
        Object.keys(highlightsData).forEach(pageNum => redrawHighlights(parseInt(pageNum)));
    }

    function updateMode() {
        highlightBtn.style.backgroundColor = isHighlighting ? "#ffcc00" : "";
        annotateBtn.style.backgroundColor = isAnnotating ? "#00ccff" : "";
        exitModeBtn.style.display = (isHighlighting || isAnnotating) ? "inline-block" : "none";
        document.body.classList.toggle("highlighting-mode", isHighlighting);
        document.body.classList.toggle("annotating-mode", isAnnotating);
    }

    function handleMouseDown(e) {
        if (!isHighlighting) return;
        
        const pageInfo = getPageInfoFromEvent(e);
        if (!pageInfo) return;
        
        isDrawing = true;
        startPage = pageInfo.pageNum;
        startX = pageInfo.x;
        startY = pageInfo.y;
        currentX = startX;
        currentY = startY;
    }
    
    function handleMouseMove(e) {
        if (!isDrawing || !isHighlighting || !startPage) return;
        
        const pageInfo = getPageInfoFromEvent(e);
        
        if (pageInfo && pageInfo.pageNum === startPage) {
            currentX = pageInfo.x;
            currentY = pageInfo.y;
            
            // Update temporary highlight
            const highlightCanvas = pageCanvasMap[startPage];
            if (highlightCanvas) {
                const ctx = highlightCanvas.getContext("2d");
                redrawHighlights(startPage);
                
                const x = Math.min(startX, currentX),
                      y = Math.min(startY, currentY),
                      width = Math.abs(currentX - startX),
                      height = Math.abs(currentY - startY);
                
                ctx.fillStyle = window.currentHighlightColor || "rgba(255, 255, 0, 0.3)";
                ctx.fillRect(x, y, width, height);
            }
        }
    }
    
    function handleMouseUp(e) {
        if (!isDrawing || !isHighlighting || !startPage) return;
        
        const pageInfo = getPageInfoFromEvent(e);
        
        if (pageInfo && pageInfo.pageNum === startPage) {
            currentX = pageInfo.x;
            currentY = pageInfo.y;
        }
        
        const x = Math.min(startX, currentX),
              y = Math.min(startY, currentY),
              width = Math.abs(currentX - startX),
              height = Math.abs(currentY - startY);
        
        if (width > 5 && height > 5) {
            highlightsData[startPage].push({
                x, y, width, height,
                color: window.currentHighlightColor || "rgba(255, 255, 0, 0.3)"
            });
            redrawHighlights(startPage);
        } else {
            redrawHighlights(startPage);
        }
        
        isDrawing = false;
        startPage = null;
    }

    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            highlightsData[pageNum] = [];
            
            pdf.getPage(pageNum).then(page => {
                let pageContainer = document.createElement("div");
                pageContainer.classList.add("pdf-page-container");
                pageContainer.setAttribute("data-page", pageNum);
                pageContainer.style.position = "relative";
                pageContainer.style.marginBottom = "20px";
                pageContainerMap[pageNum] = pageContainer;
                pdfContainer.appendChild(pageContainer);

                let canvas = document.createElement("canvas");
                canvas.classList.add("pdf-page");
                canvas.style.display = "block";
                pageContainer.appendChild(canvas);

                let viewport = page.getViewport({ scale: 1.5 });
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                
                pageContainer.setAttribute("data-width", viewport.width);
                pageContainer.setAttribute("data-height", viewport.height);

                page.render({ canvasContext: canvas.getContext("2d"), viewport }).promise.then(() => {
                    let highlightCanvas = document.createElement("canvas");
                    highlightCanvas.classList.add("highlight-layer");
                    highlightCanvas.width = viewport.width;
                    highlightCanvas.height = viewport.height;
                    highlightCanvas.style.position = "absolute";
                    highlightCanvas.style.top = "0";
                    highlightCanvas.style.left = "0";
                    highlightCanvas.style.pointerEvents = "none"; 
                    highlightCanvas.style.zIndex = "2";
                    pageContainer.appendChild(highlightCanvas);
                    
                    pageCanvasMap[pageNum] = highlightCanvas;
                });
            }).catch(error => console.error(`❌ Erreur lors du rendu de la page ${pageNum}:`, error));
        }

        setTimeout(() => {
            pdfContainer.addEventListener("mousedown", handleMouseDown);
            document.addEventListener("mousemove", handleMouseMove);
            document.addEventListener("mouseup", handleMouseUp);
            
            pdfContainer.addEventListener("dblclick", e => {
                if (!isHighlighting) return;
                
                const pageInfo = getPageInfoFromEvent(e);
                if (!pageInfo) return;
                
                const { pageNum, x, y } = pageInfo;
                let hitIndex = -1;
                
                for (let i = 0; i < highlightsData[pageNum].length; i++) {
                    const h = highlightsData[pageNum][i];
                    if (x >= h.x && x <= (h.x + h.width) && y >= h.y && y <= (h.y + h.height)) {
                        hitIndex = i;
                        break;
                    }
                }
                
                if (hitIndex !== -1) {
                    highlightsData[pageNum].splice(hitIndex, 1);
                    redrawHighlights(pageNum);
                }
            });
        }, 1000);
    }).catch(error => console.error("❌ Erreur lors du chargement du PDF:", error));

    highlightBtn.addEventListener("click", function () {
        isHighlighting = true;
        isAnnotating = false;
        updateMode();
        document.querySelectorAll(".highlight-layer").forEach(canvas => canvas.style.pointerEvents = "auto");
        pdfContainer.style.cursor = "crosshair";
    });

    annotateBtn.addEventListener("click", function () {
        isAnnotating = true;
        isHighlighting = false;
        updateMode();
        document.querySelectorAll(".highlight-layer").forEach(canvas => canvas.style.pointerEvents = "none");
        pdfContainer.style.cursor = "text";
    });

    exitModeBtn.addEventListener("click", function () {
        isHighlighting = isAnnotating = false;
        updateMode();
        document.querySelectorAll(".highlight-layer").forEach(canvas => canvas.style.pointerEvents = "none");
        pdfContainer.style.cursor = "default";
    });
    
    window.exportHighlights = () => JSON.stringify(highlightsData);
    
    window.importHighlights = function(jsonData) {
        try {
            highlightsData = JSON.parse(jsonData);
            redrawAllHighlights();
            return true;
        } catch (e) {
            console.error("Erreur lors de l'importation des surlignages:", e);
            return false;
        }
    };

    pdfContainer.addEventListener("click", function (event) {
        if (!isAnnotating) return;
        
        if (event.target.classList.contains("annotation") || 
            event.target.parentNode.classList.contains("annotation")) return;
    
        let annotationText = prompt("Entrez votre annotation :");
        if (!annotationText || annotationText.trim() === "") return;
    
        const pageInfo = getPageInfoFromEvent(event);
        if (!pageInfo) return;

        let annotation = document.createElement("div");
        annotation.classList.add("annotation");
        annotation.innerHTML = `<p>${annotationText}</p>`;
        
        Object.assign(annotation.style, {
            position: "absolute",
            left: pageInfo.x + "px",
            top: pageInfo.y + "px",
            backgroundColor: "rgba(255, 255, 183, 0.9)",
            border: "1px solid #ccc",
            borderRadius: "3px",
            padding: "5px",
            maxWidth: "250px",
            boxShadow: "2px 2px 4px rgba(0,0,0,0.2)",
            zIndex: "3"
        });
    
        let deleteBtn = document.createElement("button");
        deleteBtn.innerText = "❌";
        Object.assign(deleteBtn.style, {
            marginLeft: "5px",
            background: "none",
            border: "none",
            cursor: "pointer",
            float: "right"
        });
        deleteBtn.onclick = e => {
            e.stopPropagation();
            annotation.remove();
        };
        annotation.prepend(deleteBtn);
    
        annotation.setAttribute("draggable", "true");
        annotation.addEventListener("dragstart", function(e) {
            const style = window.getComputedStyle(annotation);
            e.dataTransfer.setData("text/plain", 
                (parseInt(style.left) - e.clientX) + ',' + (parseInt(style.top) - e.clientY));
            e.dataTransfer.setData("application/x-page", pageInfo.pageNum);
        });
        
        const pageContainer = pageContainerMap[pageInfo.pageNum];
        if (pageContainer) pageContainer.appendChild(annotation);
    });
    
    pdfContainer.addEventListener("dragover", e => e.preventDefault());
    
    pdfContainer.addEventListener("drop", function(e) {
        e.preventDefault();
        const annotation = document.querySelector(".annotation[draggable=true]");
        if (!annotation) return;
        
        const pageInfo = getPageInfoFromEvent(e);
        if (!pageInfo) return;
        
        const offset = e.dataTransfer.getData("text/plain").split(','),
              dx = parseInt(offset[0]),
              dy = parseInt(offset[1]),
              pageContainer = pageContainerMap[pageInfo.pageNum];
              
        if (!pageContainer) return;
        
        if (annotation.parentElement !== pageContainer) pageContainer.appendChild(annotation);
        
        annotation.style.left = (e.clientX + dx) + 'px';
        annotation.style.top = (e.clientY + dy) + 'px';
    });
});
