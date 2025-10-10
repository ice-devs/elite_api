<?php

namespace App\Mailer;

use Exception;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    private string $smtpEmail;
    private string $smtpPassword;
    private string $smtpHost;
    private string $smtpSender;

    private string $subjectOrdered   = 'Order Confirmation';
    private string $subjectDelivered = 'Order Delivered';
    private string $subjectCancelled = 'Order Cancelled';

    private PHPMailer $mailer;

    public function __construct()
    {
        // Load SMTP configuration from environment variables
        $this->smtpEmail    = getenv('SMTP_EMAIL') ?: '';
        $this->smtpPassword = getenv('SMTP_PASSWORD') ?: '';
        $this->smtpHost     = getenv('SMTP_HOSTNAME') ?: '';
        $this->smtpSender   = getenv('SMTP_SENDER') ?: '';

        // Initialize PHPMailer
        $this->mailer = new PHPMailer(true);
    }

    public function mailIt($request, string $orderId): void
    {
        try {
            $this->configureMailer();

            // Recipient details
            $this->mailer->setFrom($this->smtpEmail, $this->smtpSender);
            $this->mailer->addAddress($request->input('email'), $request->input('clientName') ?? 'Customer');
            $this->mailer->addReplyTo($this->smtpEmail, $this->smtpSender);

            // Email subject and template
            $shortId = Str::substr($orderId, 0, 8);
            $status = $request->input('status');
            $letterFile = $this->resolveTemplate($status, $shortId);

            // Decode products if JSON
            $products = $request->input('product');
            if (is_string($products)) {
                $products = json_decode($products);
            }

            // Prepare template variables
            $data = [
                'id'             => $shortId,
                'name'           => $request->input('name'),
                'status'         => $status,
                'payMethod'      => $request->input('payMethod'),
                'products'       => $products,
                'total'          => $request->input('total'),
                'amountPaid'     => $request->input('amountPaid'),
                'balance'        => $request->input('balance'),
                'clientName'     => $request->input('clientName'),
                'email'          => $request->input('email'),
                'phone'          => $request->input('phone'),
                'address'        => $request->input('address'),
                'deliveryFee'    => $request->input('deliveryFee'),
                'subtotal'       => $request->input('subtotal'),
                'coupon'         => $request->input('coupon'),
                'discount'       => $request->input('discount'),
                'deliveryState'  => $request->input('deliveryState'),
                'deliveryMethod' => $request->input('deliveryMethod'),
            ];

            // Render the email body
            $emailBody = $this->renderTemplate($letterFile, $data);
            if (!$emailBody) {
                throw new Exception("Email template not found: {$letterFile}");
            }

            $this->mailer->isHTML(true);
            $this->mailer->msgHTML($emailBody);

            // Send the email
            $this->mailer->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            throw new Exception("Failed to send email: " . $e->getMessage());
        }
    }

    private function configureMailer(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth = true;
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port = 587;
        $this->mailer->Host = $this->smtpHost;
        $this->mailer->Username = $this->smtpEmail;
        $this->mailer->Password = $this->smtpPassword;
        $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
    }

    private function resolveTemplate(string $status, string $shortId): string
    {
        $base = dirname(__DIR__, 2) . '/letters';

        switch ($status) {
            case 'ordered':
                $this->mailer->Subject = $this->subjectOrdered . " [order-{$shortId}]";
                return "{$base}/order_letter.php";

            case 'delivered':
                $this->mailer->Subject = $this->subjectDelivered . " [order-{$shortId}]";
                return "{$base}/delivered_letter.php";

            case 'cancelled':
                $this->mailer->Subject = $this->subjectCancelled . " [order-{$shortId}]";
                return "{$base}/cancelled_letter.php";

            default:
                throw new Exception("Invalid order status: {$status}");
        }
    }

    private function renderTemplate(string $filename, array $variables): string|false
    {
        if (!is_file($filename)) {
            return false;
        }

        extract($variables);
        ob_start();
        include $filename;
        return ob_get_clean();
    }
}
