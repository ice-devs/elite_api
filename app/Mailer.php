<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Str;
use App\Http\Controllers\OrderController;

class Mailer
{
    private string $smtpEmail;
    private string $smtpPassword;
    private string $smtpHost;
    private string $smtpSender;

    private string $subjectOrdered   = 'Order Confirmation';
    private string $subjectDelivered = 'Order Delivery';
    private string $subjectCancelled = 'Order Cancellation';

    private PHPMailer $mailer;

    public function __construct()
    {
        // Load SMTP configuration from environment variables
        $this->smtpEmail    = getenv('SMTP_EMAIL') ;
        $this->smtpPassword = getenv('SMTP_PASSWORD') ;
        $this->smtpHost     = getenv('SMTP_HOSTNAME') ;
        $this->smtpSender   = getenv('SMTP_SENDER') ;

        // Initialize PHPMailer
        $this->mailer = new PHPMailer(true);

    }

    public function mailIt($request, $orderId)
    {
        try {

            // Configure PHPMailer
            $this->mailer->isSMTP();
            $this->mailer->SMTPDebug = 4;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->SMTPSecure = 'tls';
            $this->mailer->Port       = 587;
            $this->mailer->Host       = $this->smtpHost;
            $this->mailer->Username   = $this->smtpEmail;
            $this->mailer->Password   = $this->smtpPassword;

            // Sender and recipient
            $this->mailer->setFrom($this->smtpEmail, $this->smtpSender);
            $this->mailer->addAddress($request->input('email'), $request->input('clientName') ?? 'Customer');
            $this->mailer->addReplyTo($this->smtpEmail, $this->smtpSender);

            // Email format
            $this->mailer->isHTML(true);

            // Prepare email subject + template
            $letterFile = null;


            $shortId = Str::substr($orderId, 0, 8);

            switch ($request->input('status')) {
                case 'ordered':
                    $this->mailer->Subject = $this->subjectOrdered . " [order-{$shortId}]";
                    $letterFile = dirname(__DIR__) . '/letters/order_letter.php';
                    break;

                case 'delivered':
                    $this->mailer->Subject = $this->subjectDelivered . " [order-{$shortId}]";
                    $letterFile = dirname(__DIR__) . '/letters/delivered_letter.php';
                    break;

                case 'cancelled':
                    $this->mailer->Subject = $this->subjectCancelled . " [order-{$shortId}]";
                    $letterFile = dirname(__DIR__) . '/letters/cancelled_letter.php';
                    break;

                default:
                    throw new \InvalidArgumentException("Invalid order status: {$request->input('status')}");
            }

            // Get the product value from request
            $products = $request->input('product');

            // If it's a string (JSON), decode it
            if (is_string($products)) {
                $products = json_decode($products);
            }

            // Prepare template variables
            $data = [
                'id'             => $shortId,
                'name'           => $request->input('name'),
                'status'         => $request->input('status'),
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

            // Generate email body
            $emailBody = $this->renderTemplate($letterFile, $data);

            if (!$emailBody) {
                throw new \RuntimeException("Email template not found: {$letterFile}");
            }

            $this->mailer->msgHTML($emailBody);

            // Send the email
            $this->mailer->send();
        } catch (Exception $e) {
            // Handle PHPMailer exceptions
            error_log("Mailer Error: " . $e->getMessage());
            throw new \RuntimeException("Failed to send email: " . $e->getMessage());
        }
    }

    private function renderTemplate(string $filename, array $variables)
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
