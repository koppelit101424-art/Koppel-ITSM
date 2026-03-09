<!-- Desktop Details Modal -->
<div class="modal fade" id="desktopDetailsModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header bg-gradient-primary text-white py-3 px-4">
        <h5 class="modal-title fw-bold">
          <i class="fas fa-info-circle me-2"></i>Item Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body p-4">
        <div class="row ">

          <!-- LEFT COLUMN -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-microchip me-2"></i>Hardware Info
              </h6>

              <div class="mb-3">
                <label class="text-muted small">Desktop ID</label>
                <p class="fw-semibold mb-0" id="modalDesktopId">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">CPU</label>
                <p class="fw-semibold mb-0" id="modalCpu">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">RAM</label>
                <p class="fw-semibold mb-0" id="modalRam">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">ROM / Serial</label>
                <p class="fw-semibold mb-0" id="modalRom">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">Motherboard</label>
                <p class="fw-semibold mb-0" id="modalMotherboard">—</p>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-network-wired me-2"></i>System & Network
              </h6>

              <div class="mb-3">
                <label class="text-muted small">Monitor</label>
                <p class="fw-semibold mb-0" id="modalMonitor">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">IP Address</label>
                <p class="fw-semibold mb-0" id="modalIp">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">MAC Address</label>
                <p class="fw-semibold mb-0" id="modalMac">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">Computer Name</label>
                <p class="fw-semibold mb-0" id="modalComputerName">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">Windows Key</label>
                <p class="fw-semibold mb-0" id="modalWindowsKey">—</p>
              </div>
            </div>
          </div>

          <!-- PERIPHERALS -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-keyboard me-2"></i>Peripherals
              </h6>

              <div class="mb-3">
                <label class="text-muted small">Keyboard</label>
                <p class="fw-semibold mb-0" id="modalKeyboard">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">Mouse</label>
                <p class="fw-semibold mb-0" id="modalMouse">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">AVR</label>
                <p class="fw-semibold mb-0" id="modalAvr">—</p>
              </div>
            </div>
          </div>

          <!-- OTHER DETAILS -->
          <div class="col-md-6">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-shield-alt me-2"></i>Security & Tagging
              </h6>

              <div class="mb-3">
                <label class="text-muted small">Antivirus</label>
                <p class="fw-semibold mb-0" id="modalAntivirus">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">Tag Number</label>
                <p class="fw-semibold mb-0" id="modalTagNumber">—</p>
              </div>

              <div class="mb-3">
                <label class="text-muted small">Area ID</label>
                <p class="fw-semibold mb-0" id="modalAreaId">—</p>
              </div>
            </div>
          </div>

          <!-- REMARKS -->
          <div class="col-12">
            <div class="detail-card p-3 rounded-3 bg-light-subtle">
              <h6 class="text-primary mb-3 fw-bold border-bottom pb-2">
                <i class="fas fa-align-left me-2"></i>Remarks
              </h6>
              <p class="mb-0" id="modalRemarks" style="white-space: pre-wrap;">—</p>
            </div>
          </div>

        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer bg-light ">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Close
        </button>
        <a href="#" id="editDesktopLink" class="btn btn-primary">
          <i class="fas fa-edit me-1"></i>Edit Desktop
        </a>
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