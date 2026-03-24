<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header  text-white py-3 px-4">
        <h5 class="modal-title fw-bold">
          <i class="fas fa-receipt me-2"></i>Transaction Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-4">
          <!-- Left Column -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-info-circle me-2"></i>Basic Info
              </h6>
              <div class="mb-3">
                <label class="text-muted small">Transaction ID</label>
                <p class="fw-semibold mb-0" id="modalTransId">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">User</label>
                <p class="fw-semibold mb-0" id="modalUser">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Item</label>
                <p class="fw-semibold mb-0" id="modalItem">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Brand</label>
                <p class="fw-semibold mb-0" id="modalBrand">—</p>
              </div>
            </div>
          </div>
          
          <!-- Right Column -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-cube me-2"></i>Item Details
              </h6>
              <div class="mb-3">
                <label class="text-muted small">Model</label>
                <p class="fw-semibold mb-0" id="modalModel">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Quantity</label>
                <p class="fw-semibold mb-0" id="modalQty">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Date</label>
                <p class="fw-semibold mb-0" id="modalDate">—</p>
              </div>
              <div class="mb-3">
                <label class="text-muted small">Returned</label>
                <p class="fw-semibold mb-0" id="modalReturned">—</p>
              </div>
            </div>
          </div>
          
          <!-- Full-width Remarks -->
          <div class="col-12">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-comment-alt me-2"></i>Remarks
              </h6>
              <div class="row">
                <div class="col-6">
                    <p class="mb-0" id="modalRemarks" style="line-height: 1.6;">—</p>
                </div>
                <div class="col-6">
                    <span id="modalStatus" class="px-3 py-2 rounded-pill fw-bold" 
                    style="background: rgba(51, 161, 224, 0.15); color: #33A1E0;">
                    —
                    </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Status Badge -->
          <div class="col-12 d-flex justify-content-center pt-2">
            
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-center">
        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
          <i class="fas fa-check me-1"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.bg-gradient-primary {
  background: linear-gradient(135deg, #33A1E0 0%, #1e8ac5 100%);
}
.bg-light-subtle {
  background-color: #f8f9fa;
}
.detail-card {
  border: 1px solid rgba(0,0,0,0.08);
  transition: transform 0.2s, box-shadow 0.2s;
}
.detail-card:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.text-primary {
  color: #33A1E0 !important;
}
</style>