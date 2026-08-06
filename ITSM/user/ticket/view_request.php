<?php while($rec = $recommendations->fetch_assoc()): ?>

<div class="border rounded p-3 mb-3">

    <div class="fw-bold">
        <?= htmlspecialchars($rec['fullname']) ?>
    </div>

    <small class="text-muted">
        <?= date('M d, Y h:i A', strtotime($rec['created_at'])) ?>
    </small>

    <div class="mt-2">
        <?= nl2br(htmlspecialchars($rec['recommendation'])) ?>
    </div>

</div>

<?php endwhile; ?>

ewqewqeqw