<!-- Item Details Modal -->
<div class="modal fade modal-lg" id="itemDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" >
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header bg-gradient-primary text-white py-3 px-4">
        <h5 class="modal-title fw-bold">
          <i class="fas fa-info-circle me-2"></i>Item Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4">
        <div class="row g-4">

          <!-- LEFT -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                <i class="fas fa-tag me-2"></i>Basic Information
              </h6>

              <div class="mb-2">
                <label class="text-muted small">Code</label>
                <p id="modalItemCode" class="fw-semibold mb-0">—</p>
              </div>

              <div class="mb-2">
                <label class="text-muted small">Item Name</label>
                <p id="modalItemName" class="fw-semibold mb-0">—</p>
              </div>

              <div class="mb-2">
                <label class="text-muted small">Brand</label>
                <p id="modalItemBrand" class="fw-semibold mb-0">—</p>
              </div>

              <div class="mb-2">
                <label class="text-muted small">Model</label>
                <p id="modalItemModel" class="fw-semibold mb-0">—</p>
              </div>
            </div>
          </div>

          <!-- RIGHT -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                <i class="fas fa-cube me-2"></i>Inventory Details
              </h6>

              <div class="mb-2">
                <label class="text-muted small">Serial</label>
                <p id="modalItemSerial" class="fw-semibold mb-0">—</p>
              </div>

              <div class="mb-2">
                <label class="text-muted small">Quantity</label>
                <p id="modalItemQty" class="fw-semibold mb-0">—</p>
              </div>

              <div class="mb-2">
                <label class="text-muted small">Received</label>
                <p id="modalItemReceived" class="fw-semibold mb-0">—</p>
              </div>

              <div class="mb-2">
                <label class="text-muted small">Type</label>
                <p id="modalItemType" class="fw-semibold mb-0">—</p>
              </div>
            </div>
          </div>



          <!-- LAPTOP / SYSTEM UNIT SPECS -->
          <div class="col-12" id="pcSpecsSection" style="display:none;">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                <i class="fas fa-desktop me-2"></i>Laptop / PC Specs
              </h6>

              <div class="row">

                <div class="col-md-3">
                  <label class="text-muted small">CPU</label>
                  <p id="modalCpu">—</p>
                </div>

                <div class="col-md-3">
                  <label class="text-muted small">RAM</label>
                  <p id="modalRam">—</p>
                </div>

                <div class="col-md-3">
                  <label class="text-muted small">ROM</label>
                  <p id="modalRom">—</p>
                </div>

                <div class="col-md-3">
                  <label class="text-muted small">Motherboard</label>
                  <p id="modalMotherboard">—</p>
                </div>

                <div class="col-md-3">
                  <label class="text-muted small">OS</label>
                  <p id="modalOs">—</p>
                </div>

                <div class="col-md-3">
                  <label class="text-muted small">OS Key</label>
                  <p id="modalKey">—</p>
                </div>

                <div class="col-md-3">
                  <label class="text-muted small">Antivirus</label>
                  <p id="modalAntivirus">—</p>
                </div>

                <div class="col-md-3">
                  <label class="text-muted small">Computer Name</label>
                  <p id="modalCompName">—</p>
                </div>

              </div>
            </div>
          </div>
          <!-- DESCRIPTION -->
          <div class="col-12 mt-2">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-align-left me-2"></i>Description
              </h6>
              <p class="mb-0" id="modalItemDesc" style="white-space: pre-wrap; line-height: 1.6;">—</p>
                <div class="mt-3">
                <label class="text-muted small">Status</label>
                <span id="modalItemStatus">—</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>

    </div>
  </div>
</div>