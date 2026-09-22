{* Confirm dialog - rendered once and reused via JavaScript *}
<div id="confirm-dialog" class="confirm-dialog-modal" style="display: none;">
    <div class="confirm-dialog-backdrop"></div>
    <div class="confirm-dialog-content">
        <div class="confirm-dialog-header">
            <h3 id="confirm-dialog-title">Bevestiging</h3>
        </div>
        <div class="confirm-dialog-body">
            <p id="confirm-dialog-message"></p>
        </div>
        <div class="confirm-dialog-footer">
            <button type="button" class="confirm-dialog-btn confirm-dialog-btn-cancel" id="confirm-dialog-cancel">
                Annuleren
            </button>
            <button type="button" class="confirm-dialog-btn confirm-dialog-btn-secondary" id="confirm-dialog-secondary" style="display: none;">
            </button>
            <button type="button" class="confirm-dialog-btn confirm-dialog-btn-confirm" id="confirm-dialog-confirm">
                Bevestigen
            </button>
        </div>
    </div>
</div>
