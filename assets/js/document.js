pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

document.addEventListener("DOMContentLoaded", function () {
    let pdfUrl = document.getElementById("pdf-container").getAttribute("data-pdf-url");
    let pdfContainer = document.getElementById("pdf-container");

    let isHighlighting = false;
    let isAnnotating = false;

    let highlightBtn = document.getElementById("highlight-btn");
    let annotateBtn = document.getElementById("annotate-btn");
    let exitModeBtn = document.getElementById("exit-mode-btn");

    console.log("📜 Chargement du PDF...");
    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        console.log("✅ PDF chargé avec", pdf.numPages, "pages.");

        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            pdf.getPage(pageNum).then(page => {
                // Créer un conteneur pour chaque page
                let pageContainer = document.createElement("div");
                pageContainer.classList.add("pdf-page-container");
                pdfContainer.appendChild(pageContainer);

                let canvas = document.createElement("canvas");
                canvas.classList.add("pdf-page");
                pageContainer.appendChild(canvas);

                let context = canvas.getContext("2d");
                let viewport = page.getViewport({ scale: 1.5 });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                let renderContext = { canvasContext: context, viewport: viewport };
                page.render(renderContext);

                console.log("📄 Page", pageNum, "rendue.");

                // Créer le canevas pour le surlignage et l'annotation
                let highlightCanvas = document.createElement("canvas");
                highlightCanvas.classList.add("highlight-layer");
                highlightCanvas.width = viewport.width;
                highlightCanvas.height = viewport.height;
                pageContainer.appendChild(highlightCanvas);

                let highlightContext = highlightCanvas.getContext("2d");

                // Surligner avec la souris
                let startX, startY, endX, endY, isDrawing = false;

                highlightCanvas.addEventListener("mousedown", function (e) {
                    if (!isHighlighting) return;
                    isDrawing = true;
                    startX = e.offsetX;
                    startY = e.offsetY;
                });

                highlightCanvas.addEventListener("mousemove", function (e) {
                    if (!isDrawing) return;
                    endX = e.offsetX;
                    endY = e.offsetY;
                    highlightContext.clearRect(0, 0, highlightCanvas.width, highlightCanvas.height);
                    highlightContext.fillStyle = "rgba(255, 255, 0, 0.5)";
                    highlightContext.fillRect(startX, startY, endX - startX, endY - startY);
                });

                highlightCanvas.addEventListener("mouseup", function () {
                    isDrawing = false;
                    highlightContext.fillStyle = "rgba(255, 255, 0, 0.5)";
                    highlightContext.fillRect(startX, startY, endX - startX, endY - startY);
                });
            });
        }
    });

    // Gestion des modes
    highlightBtn.addEventListener("click", function () {
        isHighlighting = true;
        isAnnotating = false;
        console.log(isHighlighting, isAnnotating);
        updateMode();
    });

    annotateBtn.addEventListener("click", function () {
        isAnnotating = true;
        isHighlighting = false;
        console.log(isHighlighting, isAnnotating);
        updateMode();
    });

    exitModeBtn.addEventListener("click", function () {
        isHighlighting = false;
        isAnnotating = false;
        console.log(isHighlighting, isAnnotating);
        updateMode();
    });

    function updateMode() {
        console.log("🔄 Mise à jour des modes :", { isHighlighting, isAnnotating });
        highlightBtn.style.backgroundColor = isHighlighting ? "#ffcc00" : "";
        annotateBtn.style.backgroundColor = isAnnotating ? "#00ccff" : "";
        exitModeBtn.style.display = (isHighlighting || isAnnotating) ? "inline-block" : "none";
    }

    // Gestion des annotations
    pdfContainer.addEventListener("click", function (event) {
        if (!isAnnotating) return;
    
        let annotationText = prompt("Entrez votre annotation :");
        if (!annotationText) return;
    
        let annotation = document.createElement("div");
        annotation.classList.add("annotation");
        annotation.innerText = annotationText;
    
        let pageRect = event.target.getBoundingClientRect();
        annotation.style.left = (event.clientX - pageRect.left) + "px";
        annotation.style.top = (event.clientY - pageRect.top) + "px";
    
        console.log("📌 Position annotation :", annotation.style.left, annotation.style.top);
    
        // Ajouter un bouton pour supprimer l'annotation
        let deleteBtn = document.createElement("button");
        deleteBtn.innerText = "❌";
        deleteBtn.style.marginLeft = "5px";
        deleteBtn.onclick = (e) => {
            e.stopPropagation(); // Empêcher l'activation du gestionnaire de clic lors de la suppression
            annotation.remove();
        };
        annotation.appendChild(deleteBtn);
    
        pdfContainer.appendChild(annotation);
    });
    
});
