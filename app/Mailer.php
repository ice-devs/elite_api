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
        $this->smtpEmail    = getenv('SMTP_EMAIL') ?: '';
        $this->smtpPassword = getenv('SMTP_PASSWORD') ?: '';
        $this->smtpHost     = getenv('SMTP_HOSTNAME') ?: '';
        $this->smtpSender   = getenv('SMTP_SENDER') ?: '';

        // Initialize PHPMailer
        $this->mailer = new PHPMailer(true);

    }

    public function mailIt($orderId)
    {
        try {
            // Fetch order details
            $dataArray = OrderController::getOrderById($orderId);
            $fullData = (object) $dataArray;


            if (!$fullData) {
                throw new \RuntimeException("Order with ID {$orderId} not found.");
            }

            // Configure PHPMailer
            $this->mailer->isSMTP();
            $this->mailer->SMTPAuth   = true;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = 587;
            $this->mailer->Host       = $this->smtpHost;
            $this->mailer->Username   = $this->smtpEmail;
            $this->mailer->Password   = $this->smtpPassword;

            // Sender and recipient
            $this->mailer->setFrom($this->smtpEmail, $this->smtpSender);
            $this->mailer->addAddress($fullData->email ?? '', $fullData->clientName ?? 'Customer');
            $this->mailer->addReplyTo($this->smtpEmail, $this->smtpSender);

            // Email format
            $this->mailer->isHTML(true);

            // Prepare email subject + template
            $letterFile = null;


            $shortId = Str::substr($fullData->orderId, 0, 8);

            switch ($fullData->status) {
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
                    throw new \InvalidArgumentException("Invalid order status: {$fullData->status}");
            }

            // Prepare template variables
            $data = [
                'id'            => $shortId,
                'name'          => $fullData->clientName,
                'status'        => $fullData->status,
                'paymentMethod' => $fullData->payMethod,
                'products'      => $fullData->product,
                'total'         => $fullData->total,
                'amountPaid'    => $fullData->amountPaid,
                'balance'       => $fullData->balance,
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
