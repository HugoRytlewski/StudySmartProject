document.addEventListener("DOMContentLoaded", function () {
    let pdfUrl = document.getElementById("pdf-container").getAttribute("data-pdf-url");
    let pdfContainer = document.getElementById("pdf-container");

    let isHighlighting = false;
    let isAnnotating = false;

    let highlightBtn = document.getElementById("highlight-btn");
    let annotateBtn = document.getElementById("annotate-btn");
    let exitModeBtn = document.getElementById("exit-mode-btn");

    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            pdf.getPage(pageNum).then(page => {
                let canvas = document.createElement("canvas");
                canvas.classList.add("pdf-page");
                pdfContainer.appendChild(canvas);

                let context = canvas.getContext("2d");
                let viewport = page.getViewport({ scale: 1.5 });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                let renderContext = { canvasContext: context, viewport: viewport };
                page.render(renderContext);
            });
        }
    });

    // Activer le surlignage
    highlightBtn.addEventListener("click", function () {
        isHighlighting = true;
        isAnnotating = false;
        updateMode();
    });

    // Activer l'annotation
    annotateBtn.addEventListener("click", function () {
        isAnnotating = true;
        isHighlighting = false;
        updateMode();
    });

    // Quitter les modes
    exitModeBtn.addEventListener("click", function () {
        isHighlighting = false;
        isAnnotating = false;
        updateMode();
    });

    function updateMode() {
        highlightBtn.style.backgroundColor = isHighlighting ? "#ffcc00" : "";
        annotateBtn.style.backgroundColor = isAnnotating ? "#00ccff" : "";
        exitModeBtn.style.display = isHighlighting || isAnnotating ? "inline-block" : "none";
    }

    // Gérer le surlignage (Correction)
    document.addEventListener("mouseup", function () {
        if (isHighlighting) {
            let selection = window.getSelection();
            if (!selection.rangeCount) return;

            let range = selection.getRangeAt(0);
            let span = document.createElement("span");
            span.style.backgroundColor = "yellow";
            span.style.color = "black";
            range.surroundContents(span);

            selection.removeAllRanges();
        }
    });

    // Gérer les annotations (Correction position)
    pdfContainer.addEventListener("click", function (event) {
        if (isAnnotating) {
            let annotationText = prompt("Entrez votre annotation :");
            if (annotationText) {
                let annotation = document.createElement("div");
                annotation.classList.add("annotation");
                annotation.innerText = annotationText;

                annotation.style.position = "absolute";
                annotation.style.left = event.pageX - pdfContainer.offsetLeft + "px";
                annotation.style.top = event.pageY - pdfContainer.offsetTop + "px";
                annotation.style.backgroundColor = "white";
                annotation.style.color = "black";
                annotation.style.border = "1px solid black";
                annotation.style.padding = "5px";
                annotation.style.borderRadius = "5px";

                pdfContainer.appendChild(annotation);
            }
        }
    });
});
