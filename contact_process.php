<?php
include "db_conn.php";
include "csrf.php";

header('Content-Type: application/json');

if (!verifyToken($_POST['token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF error']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$company = trim($_POST['company'] ?? '');
$country = trim($_POST['country'] ?? '');
$job_title = trim($_POST['job_title'] ?? '');
$job_details = trim($_POST['job_details'] ?? '');

if (empty($name) || empty($email) || empty($phone) || empty($company) || empty($country) || empty($job_title) || empty($job_details)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO inquiries (name,email,phone,company,country,job_title,job_details) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("sssssss", $name, $email, $phone, $company, $country, $job_title, $job_details);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Inquiry submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit inquiry. Please try again.']);
}
?>