<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? null;

/* ==============================
   SAVE SIGNATURE
============================== */
if ($action === 'save') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['image'])) {
        echo json_encode(['error' => 'No image']);
        exit;
    }

    // Check limit
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_signatures WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['total'];

    if ($count >= 3) {
        echo json_encode(['error' => 'Max 3 signatures reached']);
        exit;
    }

    // Process image
    $image = str_replace('data:image/png;base64,', '', $data['image']);
    $image = str_replace(' ', '+', $image);
    $imageData = base64_decode($image);

    // Save file
    $dir = "../uploads/signatures/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $fileName = 'sig_' . $user_id . '_' . time() . '.png';
    $filePath = $dir . $fileName;

    file_put_contents($filePath, $imageData);

    // Save DB
    $stmt = $conn->prepare("INSERT INTO user_signatures (user_id, file_path) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $filePath);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

/* ==============================
   GET SIGNATURES
============================== */
if ($action === 'list') {

    $result = $conn->query("
        SELECT id, file_path 
        FROM user_signatures 
        WHERE user_id = $user_id 
        ORDER BY created_at DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}

/* ==============================
   NORMAL PAGE LOAD (HTML)
============================== */
header('Content-Type: text/html');
?>

<!DOCTYPE html>
<html>
<head>
<title>Signature Pad</title>

<style>
body {
    font-family: Arial;
    background: #f5f6fa;
}

.signature-container {
    max-width: 520px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

h3 {
    margin-bottom: 10px;
}

#signature-pad {
    width: 100%;
    height: 200px;
    border: 2px dashed #ccc;
    border-radius: 10px;
    background: #fff;
    cursor: crosshair;
}

.actions {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.btn {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.btn.save {
    background: #28a745;
    color: white;
}

.btn.clear {
    background: #dc3545;
    color: white;
}

#message {
    margin-top: 10px;
    font-weight: bold;
}

#signature-list {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.sig-box {
    position: relative;
}

.sig-img {
    width: 120px;
    border: 1px solid #ddd;
    padding: 5px;
    border-radius: 6px;
    background: #fff;
}

</style>
</head>

<body>

<div class="signature-container">
    <h3>🖊 Signature</h3>

    <canvas id="signature-pad"></canvas>

    <div class="actions">
        <button class="btn clear" onclick="clearPad()">Clear</button>
        <button class="btn save" onclick="saveSignature()">Save Signature</button>
    </div>

    <div id="message"></div>

    <h4>Your Signatures (Max 3)</h4>
    <div id="signature-list"></div>
</div>

<script>
let canvas = document.getElementById('signature-pad');
let ctx = canvas.getContext('2d');
let drawing = false;

// Fix resolution
canvas.width = canvas.offsetWidth;
canvas.height = canvas.offsetHeight;

canvas.addEventListener('mousedown', () => drawing = true);
canvas.addEventListener('mouseup', () => {
    drawing = false;
    ctx.beginPath();
});
canvas.addEventListener('mousemove', draw);

// Optional mobile support
canvas.addEventListener('touchstart', () => drawing = true);
canvas.addEventListener('touchend', () => {
    drawing = false;
    ctx.beginPath();
});
canvas.addEventListener('touchmove', drawTouch);

function draw(e) {
    if (!drawing) return;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = "#000";

    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
}

function drawTouch(e) {
    e.preventDefault();
    let rect = canvas.getBoundingClientRect();
    let touch = e.touches[0];

    let x = touch.clientX - rect.left;
    let y = touch.clientY - rect.top;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
}

function clearPad() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function saveSignature() {
    let dataURL = canvas.toDataURL();

    fetch('?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image: dataURL })
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            document.getElementById('message').innerHTML = "❌ " + res.error;
        } else {
            document.getElementById('message').innerHTML = "✅ Saved!";
            clearPad();
            loadSignatures();
        }
    });
}

function loadSignatures() {
    fetch('?action=list')
    .then(res => res.json())
    .then(data => {
        let html = '';

        data.forEach(sig => {
            html += `
                <div class="sig-box">
                    <img src="${sig.file_path}" class="sig-img">
                </div>
            `;
        });

        document.getElementById('signature-list').innerHTML = html;
    });
}

loadSignatures();
</script>

</body>
</html>