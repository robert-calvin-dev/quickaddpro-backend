<?php
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get the raw post data from Stripe
$payload = @file_get_contents("php://input");
$sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
$endpoint_secret = ""; // Optional: Add webhook signing secret here if enabled
$event = null;

try {
    $event = \Stripe\Event::constructFrom(json_decode($payload, true));
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;

    $customerEmail = $session->customer_email;

    // Send email with Dropbox link
    $mail = new PHPMailer(true);

    try {
        $mail->setFrom('noreply@quickaddpro.com', 'Quick Add Pro');
        $mail->addAddress($customerEmail);
        $mail->Subject = 'Your Quick Add Pro Plugin Download';
        $mail->isHTML(true);
        $mail->Body = '
            <p>Thanks for purchasing <strong>Quick Add Pro</strong>!</p>
            <p>You can download your plugin here:</p>
            <p><a href="https://www.dropbox.com/scl/fi/wifubhqppuh0n7r6ptepu/Quick-Add-Pro.zip?rlkey=geigs9tuc4iwdh0j7rvf3a2yx&st=9p1vhjtu&dl=1" target="_blank">Download Quick Add Pro</a></p>
            <p>Need help? Just reply to this email.</p>
        ';

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
    }
}

http_response_code(200);
