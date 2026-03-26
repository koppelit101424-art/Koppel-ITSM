<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';
include 'add_item_sql.php';
?>

<div class="main-content" id="mainContent">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center text-white">
            <h2>Add New Item</h2>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST" class="mt-3">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Item Code (Auto Generated)</label>
                        <input type="text" id="item_code" name="item_code" class="form-control" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Item</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" class="form-control">
                        <small id="serial_msg"></small>
                    </div>
                </div>

                <!-- Laptop / System Unit Additional Specs -->
                <div class="row mb-3" id="pcSpecsFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">CPU</label>
                        <input type="text" name="cpu" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RAM</label>
                        <input type="text" name="ram" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ROM</label>
                        <input type="text" name="rom" class="form-control">
                    </div>
                    <div class="col-md-3 ">
                        <label class="form-label">Motherboard</label>
                        <input type="text" name="motherboard" class="form-control">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="form-label">Operating System</label>
                        <input type="text" name="os" class="form-control">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="form-label">OS Key</label>
                        <input type="text" name="key" class="form-control">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="form-label">Antivirus</label>
                        <input type="text" name="antivirus" class="form-control">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="form-label">Computer Name</label>
                        <input type="text" name="comp_name" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="6"></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Received</label>
                        <input type="date" name="date_received" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Item Type</label>
                        <select name="type_id" class="form-control" required>
                            <option value="">Select Item Type</option>
                            <?php if ($type_result->num_rows > 0): ?>
                                <?php while($type = $type_result->fetch_assoc()): ?>
                                    <option value="<?= $type['type_id'] ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">QR Code</label><br>
                    <img id="qr_code_img" src="" style="max-width:150px; display:none;">
                </div>
                <button type="submit" id="saveBtn" class="btn btn-primary">Save Item</button>
                <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Back</a>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const itemInput   = document.querySelector("input[name='name']");
        const serialInput = document.querySelector("input[name='serial_number']");
        const codeInput   = document.getElementById("item_code");
        const saveBtn     = document.getElementById("saveBtn");
        const serialMsg   = document.getElementById("serial_msg");
        const pcSpecsFields = document.getElementById("pcSpecsFields");

        // Generate Item Code
        async function generateCode() {
            const item = itemInput.value.trim();
            const serial = serialInput.value.trim();
            if (item.length >= 1) {
                let prefix = item.substring(0,3).toUpperCase();
                let last4 = (serial && serial.toUpperCase() !== "N/A") ? serial.slice(-4) : "0000";

                let count = await fetch("get_item_count.php?item=" + encodeURIComponent(item))
                    .then(res => res.json())
                    .then(data => data.count + 1)
                    .catch(() => 1);

                let itemNumber = String(count).padStart(2,'0');
                codeInput.value = prefix + last4 + itemNumber;
            }
        }

        itemInput.addEventListener("input", generateCode);
        serialInput.addEventListener("input", generateCode);

        // Serial Number Check
        let serialTimer;
        serialInput.addEventListener("input", function() {
            clearTimeout(serialTimer);
            serialTimer = setTimeout(() => {
                let serial = serialInput.value.trim();
                if(serial.length === 0){
                    serialMsg.innerHTML = "";
                    saveBtn.disabled = false;
                    return;
                }
                fetch("./inventory/crud/check_serial.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: "serial=" + encodeURIComponent(serial)
                })
                .then(res => res.text())
                .then(data => {
                    if(data.trim() === "taken"){
                        serialMsg.innerHTML = "<span style='color:red'>Serial number already exists</span>";
                        serialInput.classList.add("is-invalid");
                        saveBtn.disabled = true;
                    }else{
                        serialMsg.innerHTML = "<span style='color:green'>Serial number available</span>";
                        serialInput.classList.remove("is-invalid");
                        saveBtn.disabled = false;
                    }
                });
            }, 400);
        });

        // Show additional fields for Laptop / System Unit
        itemInput.addEventListener("input", function() {
            const value = itemInput.value.trim().toLowerCase();
            if (value.includes("laptop") || value.includes("system unit")) {
                pcSpecsFields.style.display = "flex";
            } else {
                pcSpecsFields.style.display = "none";
            }
        });

    });
</script>
<script>
    const qrImg = document.getElementById("qr_code_img");

// Generate QR Code
function generateQR(code) {
    if (!code) {
        qrImg.style.display = "none";
        return;
    }

    QRCode.toDataURL(code, function (err, url) {
        if (!err) {
            qrImg.src = url;
            qrImg.style.display = "block";
        }
    });
}
async function generateCode() {
    const item = itemInput.value.trim();
    const serial = serialInput.value.trim();

    if (item.length >= 1) {
        let prefix = item.substring(0,3).toUpperCase();
        let last4 = (serial && serial.toUpperCase() !== "N/A") ? serial.slice(-4) : "0000";

        let count = await fetch("get_item_count.php?item=" + encodeURIComponent(item))
            .then(res => res.json())
            .then(data => data.count + 1)
            .catch(() => 1);

        let itemNumber = String(count).padStart(2,'0');
        let finalCode = prefix + last4 + itemNumber;

        codeInput.value = finalCode;

        // 👉 Generate QR
        generateQR(finalCode);
    }
}
</script>
