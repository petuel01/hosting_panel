<?php
// Secret key to validate the webhook
$secret = 'baifempetuelkey'; // Replace with the secret you set in the GitHub webhook

// Get the payload and signature from the request
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Validate the signature
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die('Invalid signature.');
}

// Decode the payload
$data = json_decode($payload, true);

// Check if it's a push event
if ($data['ref'] === 'refs/heads/main') { // Replace 'main' with your branch name if different
    // Pull the latest changes from GitHub
    $output = shell_exec('cd /var/www/hosting_panel && git pull 2>&1');
    file_put_contents('/var/log/github-webhook.log', date('Y-m-d H:i:s') . " - Git Pull Output:\n" . $output . "\n", FILE_APPEND);
    echo "Git pull executed.";
} else {
    echo "Not a push to the main branch.";
}
?>