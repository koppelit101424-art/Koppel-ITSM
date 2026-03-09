   <!-- Attachment Preview Modal -->
    <div class="modal fade" id="attachmentModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

            <div class="modal-header bg-gradient-primary text-white py-3 px-4">
            <h5 class="modal-title" id="attachmentTitle">Attachment Preview</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

            <!-- Image Preview -->
            <img id="previewImage" class="img-fluid d-none" />

            <!-- File Preview -->
            <iframe id="previewFrame"
                    style="width:100%;height:70vh;border:none;"
                    class="d-none"></iframe>

            <!-- Fallback -->
            <div id="previewFallback" class="d-none">
            This file type cannot be previewed.<br><br>
            <a id="downloadFallback" class="btn btn-primary">Download File</a>
            </div>

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