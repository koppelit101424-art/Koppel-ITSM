<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white py-3 px-4">
        <h5 class="modal-title fw-bold">
          <i class="fas fa-info-circle me-2"></i>Item Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-4">
          <!-- Left Column -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-tag me-2"></i>Basic Information
              </h6>
              <div class="mb-3">
                <label class="text-muted small">Code</label>
                <p class="fw-semibold mb-0" id="modalItemCode">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Item Name</label>
                <p class="fw-semibold mb-0" id="modalItemName">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Brand</label>
                <p class="fw-semibold mb-0" id="modalItemBrand">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Model</label>
                <p class="fw-semibold mb-0" id="modalItemModel">—</p>
              </div>
            </div>
          </div>
          
          <!-- Right Column -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-cube me-2"></i>Inventory Details
              </h6>
              <div class="mb-3">
                <label class="text-muted small">Serial Number</label>
                <p class="fw-semibold mb-0" id="modalItemSerial">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Quantity</label>
                <p class="fw-semibold mb-0" id="modalItemQty">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Received Date</label>
                <p class="fw-semibold mb-0" id="modalItemReceived">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Type</label>
                <p class="fw-semibold mb-0" id="modalItemType">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Status</label>
                <span id="modalItemStatus">—</span>
              </div>
            </div>
          </div>
          
          <!-- Full-width Description -->
          <div class="col-12 mt-2">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-align-left me-2"></i>Description
              </h6>
              <p class="mb-0" id="modalItemDesc" style="white-space: pre-wrap; line-height: 1.6;">—</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-3 px-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Close
        </button>
                <!-- <a href="#" id="editLink" class="btn btn-primary">
          <i class="fas fa-edit me-1"></i>Edit Item
        </a> -->
        <!-- <button type="button" class="btn btn-primary" >
          <a href="#" id="editLink" > <i class="fas fa-edit me-1"></i>Edit Item</a> -->
        <!-- inv/edit_item.php?item_id$itemId" -->
        <!-- </button> -->
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>