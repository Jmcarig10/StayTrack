<div id="export-modal" class="modal-backdrop" hidden>
    <section class="modal export-modal" role="dialog" aria-modal="true" aria-labelledby="export-modal-title" aria-describedby="export-modal-copy">
        <button class="icon-button modal-close" type="button" data-export-close aria-label="Close export options">
            <svg class="icon"><use href="#icon-x"/></svg>
        </button>

        <header class="export-modal-head">
            <span class="export-modal-icon"><svg class="icon"><use href="#icon-download"/></svg></span>
            <div>
                <h2 id="export-modal-title">Export report</h2>
                <p id="export-modal-copy">Choose a format for your download.</p>
            </div>
        </header>

        <div class="export-options">
            <a class="export-option" href="#" data-export-option="pdf">
                <span class="export-option-icon pdf"><svg class="icon"><use href="#icon-file"/></svg></span>
                <span class="export-option-body">
                    <strong>PDF document</strong>
                    <small>Print-ready and easy to share</small>
                </span>
                <span class="export-option-ext">PDF</span>
                <svg class="icon export-option-arrow"><use href="#icon-chevron"/></svg>
            </a>

            <a class="export-option" href="#" data-export-option="excel">
                <span class="export-option-icon excel"><svg class="icon"><use href="#icon-grid"/></svg></span>
                <span class="export-option-body">
                    <strong>Excel workbook</strong>
                    <small>Editable rows for sorting and analysis</small>
                </span>
                <span class="export-option-ext">XLSX</span>
                <svg class="icon export-option-arrow"><use href="#icon-chevron"/></svg>
            </a>
        </div>

        <p class="export-modal-note"><svg class="icon"><use href="#icon-check"/></svg>Your download starts immediately after choosing a format.</p>

        <div class="modal-actions">
            <button class="button button-secondary" type="button" data-export-close>Cancel</button>
        </div>
    </section>
</div>
