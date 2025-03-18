pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

document.addEventListener("DOMContentLoaded", function () {
    let pdfContainer = document.getElementById("pdf-container");
    console.log("📂 PDF container trouvé :", pdfContainer);

    let pdfUrl = pdfContainer.getAttribute("data-pdf-url");
    let documentId = pdfContainer.getAttribute("data-document-id");
    console.log("🔗 URL du PDF :", pdfUrl);
    console.log("🆔 ID du document :", documentId);

    let isHighlighting = false;
    let isAnnotating = false;

    let annotations = [];
    try {
        annotations = JSON.parse(pdfContainer.getAttribute("data-annotations") || "[]");
        console.log("📌 Annotations chargées depuis l'attribut :", annotations);
    } catch (error) {
        console.error("❌ Erreur lors du parsing des annotations :", error);
        annotations = [];
    }

    let highlightBtn = document.getElementById("highlight-btn");
    let annotateBtn = document.getElementById("annotate-btn");
    let exitModeBtn = document.getElementById("exit-mode-btn");

    console.log("📜 Chargement du PDF...");
    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        console.log("✅ PDF chargé avec", pdf.numPages, "pages.");

        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            pdf.getPage(pageNum).then(page => {
                console.log(`📄 Chargement de la page ${pageNum}`);

                let viewport = page.getViewport({ scale: 1.5 });
                console.log(`🖼️ Viewport pour la page ${pageNum} :`, viewport);

                let pageContainer = document.createElement("div");
                pageContainer.classList.add("pdf-page-container");
                pdfContainer.appendChild(pageContainer);

                let canvas = document.createElement("canvas");
                canvas.classList.add("pdf-page");
                pageContainer.appendChild(canvas);

                let context = canvas.getContext("2d");
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                let renderContext = { canvasContext: context, viewport: viewport };
                page.render(renderContext).promise.then(() => {
                    console.log(`✅ Page ${pageNum} rendue`);
                });
                

                // Ajout du calque de surlignage
                let highlightCanvas = document.createElement("canvas");
                highlightCanvas.classList.add("highlight-layer");
                highlightCanvas.width = viewport.width;
                highlightCanvas.height = viewport.height;
                pageContainer.appendChild(highlightCanvas);

                let highlightContext = highlightCanvas.getContext("2d");

                // Ajout des annotations après le rendu
                annotations
                    .filter(a => a.positionX !== undefined && a.positionY !== undefined)
                    .forEach(a => {
                        console.log("📌 Ajout de l'annotation :", a);
                        let annotation = document.createElement("div");
                        annotation.classList.add("annotation");
                        annotation.innerText = a.contenu || "(Annotation vide)";
                        annotation.style.left = a.positionX * viewport.scale + "px";
                        annotation.style.top = a.positionY * viewport.scale + "px";
                        pageContainer.appendChild(annotation);
                        console.log("📋 Contenu de pageContainer après annotations :", pageContainer.innerHTML);

                    });

                // Gestion du surlignage
                let startX, startY, endX, endY, isDrawing = false;
                highlightCanvas.addEventListener("mousedown", function (e) {
                    if (!isHighlighting) return;
                    isDrawing = true;
                    startX = e.offsetX;
                    startY = e.offsetY;
                    console.log("✏️ Début du surlignage :", { startX, startY });
                });

                highlightCanvas.addEventListener("mousemove", function (e) {
                    if (!isDrawing) return;
                    endX = e.offsetX;
                    endY = e.offsetY;
                    highlightContext.clearRect(0, 0, highlightCanvas.width, highlightCanvas.height);
                    highlightContext.fillStyle = "rgba(255, 255, 0, 0.5)";
                    highlightContext.fillRect(startX, startY, endX - startX, endY - startY);
                    console.log("🖍️ Surlignage en cours :", { endX, endY });
                });

                highlightCanvas.addEventListener("mouseup", function () {
                    isDrawing = false;
                    console.log("✅ Surlignage terminé");
                });

                // Gestion du clic pour ajouter une annotation
                pageContainer.addEventListener("click", function (event) {
                    if (!isAnnotating) return;
                    let annotationText = prompt("Entrez votre annotation :");
                    if (!annotationText) return;

                    let annotation = document.createElement("div");
                    annotation.classList.add("annotation");
                    annotation.innerText = annotationText;

                    let pageRect = event.target.getBoundingClientRect();
                    let scale = viewport.scale;
                    let positionX = (event.clientX - pageRect.left) / scale;
                    let positionY = (event.clientY - pageRect.top) / scale;

                    annotation.style.left = positionX * scale + "px";
                    annotation.style.top = positionY * scale + "px";
                    annotation.style.zIndex = 999;
                    pageContainer.appendChild(annotation);
                    console.log("📌 Annotation ajoutée :", { annotationText, positionX, positionY });

                    // Enregistrement en base de données
                    fetch('/annotation/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            documentId: documentId,
                            contenu: annotationText,
                            positionX: positionX,
                            positionY: positionY
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log("✅ Annotation enregistrée avec succès :", data.annotation);
                        } else {
                            console.error("❌ Erreur lors de l'enregistrement de l'annotation :", data.error);
                        }
                    })
                    .catch(error => {
                        console.error("❌ Erreur de communication :", error);
                    });
                });
            });
        }
    });

    // Gestion des modes
    function updateMode() {
        console.log("🔄 Mise à jour du mode : highlighting =", isHighlighting, ", annotating =", isAnnotating);
        highlightBtn.style.backgroundColor = isHighlighting ? "#ffcc00" : "";
        annotateBtn.style.backgroundColor = isAnnotating ? "#00ccff" : "";
        exitModeBtn.style.display = (isHighlighting || isAnnotating) ? "inline-block" : "none";
    }

    highlightBtn.addEventListener("click", function () {
        isHighlighting = true;
        isAnnotating = false;
        console.log("✏️ Mode surlignage activé");
        updateMode();
    });

    annotateBtn.addEventListener("click", function () {
        isAnnotating = true;
        isHighlighting = false;
        console.log("📝 Mode annotation activé");
        updateMode();
    });

    exitModeBtn.addEventListener("click", function () {
        isHighlighting = false;
        isAnnotating = false;
        console.log("🚫 Mode interactif désactivé");
        updateMode();
    });
    console.log("📌 Détail des annotations récupérées :", annotations);

});
