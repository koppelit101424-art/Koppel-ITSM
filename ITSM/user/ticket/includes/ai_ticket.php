<?php
session_start();
include 'includes/auth.php';
include 'includes/db.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$message = strtolower(trim($data['message'] ?? ''));

// Validate input
if (!$message) {
    echo json_encode(["success" => false, "error" => "Message is empty."]);
    exit;
}

// Get logged-in user
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "error" => "User not logged in."]);
    exit;
}

/* =========================
   GET USER DETAILS
========================= */
$stmt = $conn->prepare("SELECT fullname, email, company, department FROM user_tb WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(["success" => false, "error" => "User not found."]);
    exit;
}

/* =========================
   AI DETECTION (RULE BASED)
========================= */
$subject_id = 4;          // default
$subject_details_id = 10; // default
$priority = "medium";

// Simple keyword detection
if (strpos($message, 'printer') !== false) {
    $subject_id = 1;
    $subject_details_id = 1;
    $priority = "medium";
} elseif (strpos($message, 'login') !== false || strpos($message, 'password') !== false) {
    $subject_id = 2;
    $subject_details_id = 5;
    $priority = "high";
} elseif (strpos($message, 'internet') !== false || strpos($message, 'network') !== false) {
    $subject_id = 3;
    $subject_details_id = 8;
    $priority = "high";
}

/* =========================
   GET SUBJECT & DETAILS TEXT
========================= */
$stmt = $conn->prepare("SELECT subject FROM ticket_subject WHERE subject_id=?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc()['subject'] ?? 'General';

$stmt = $conn->prepare("SELECT name FROM subject_details WHERE subject_details_id=?");
$stmt->bind_param("i", $subject_details_id);
$stmt->execute();
$subject_details = $stmt->get_result()->fetch_assoc()['name'] ?? 'Other';

/* =========================
   GENERATE TICKET NUMBER
========================= */
$res = $conn->query("SELECT MAX(ticket_id) as max_id FROM ticket_tb");
$row = $res->fetch_assoc();
$ticket_number = 'TICKET-' . str_pad(($row['max_id'] ?? 0)+1,5,'0',STR_PAD_LEFT);

/* =========================
   INSERT TICKET
========================= */
$status = "waiting for support";
$assigned_to = 1; // default admin

$stmt = $conn->prepare("
INSERT INTO ticket_tb (
    ticket_number, user_id, fullname, email, company, department,
    priority, urgency, impact, ticket_category,
    subject, subject_details,
    issue, status, assigned_to
) VALUES (?, ?, ?, ?, ?, ?, ?, 'None', 'Individual', 'incident', ?, ?, ?, ?, ?)
");

// 12 parameters, matches the statement
$stmt->bind_param(
    "sissssssssssi",
    $ticket_number,
    $user_id,
    $user['fullname'],
    $user['email'],
    $user['company'],
    $user['department'],
    $priority,
    $subject,
    $subject_details,
    $message,
    $status,
    $assigned_to
);

// Execute and check
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => $stmt->error]);
    exit;
}

// Return success
echo json_encode([
    "success" => true,
    "ticket_number" => $ticket_number,
    "detected" => "Detected: $subject → $subject_details ($priority)"
]);