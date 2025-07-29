<?php
namespace App;
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/mail.config.php';

/**
 * Email Service Class
 * 
 * Handles all email functionality including welcome emails, notifications,
 * booking confirmations, and enquiry acknowledgments.
 * 
 * @author Nananom Farms Development Team
 * @version 1.0
 */
class EmailService {
    private $mailer;
    private $fromEmail;
    private $fromName;
    private $adminEmail;
    
    public function __construct() {
        $this->fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@nananom.com');
        $this->fromName = env('MAIL_FROM_NAME', 'Nananom Farms');
        $this->adminEmail = env('ADMIN_EMAIL', 'admin@nananom.com');
        
        $mailCon = createMailer();
        $this->mailer = $mailCon;
    }
    
    /**
     * Setup PHPMailer configuration
     */
    // private function setupMailer() {
    //     try {
    //         require_once __DIR__ . '/../vendor/autoload.php';
            
    //         $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            
    //         // Server settings
    //         $this->mailer->isSMTP();
    //         $this->mailer->Host = env('MAIL_HOST', 'smtp.gmail.com');
    //         $this->mailer->SMTPAuth = true;
    //         $this->mailer->Username = env('MAIL_USERNAME');
    //         $this->mailer->Password = env('MAIL_PASSWORD');
    //         $this->mailer->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
    //         $this->mailer->Port = env('MAIL_PORT', 587);
            
    //         // Default sender
    //         $this->mailer->setFrom($this->fromEmail, $this->fromName);
            
    //     } catch (\Exception $e) {
    //         error_log("Email setup error: " . $e->getMessage());
    //     }
    // }
    
    /**
     * Send welcome email to new users
     * 
     * @param array $userData User registration data
     * @return array Result array with success status
     */
    public function sendWelcomeEmail($userData) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userData['email'], $userData['first_name'] . ' ' . $userData['last_name']);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to Nananom Farms!';
            
            $htmlBody = $this->getWelcomeEmailTemplate($userData);
            $textBody = $this->getWelcomeEmailTextTemplate($userData);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Welcome email sent successfully'
            ];
            
        } catch (\Exception $e) {
            error_log("Welcome email error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send welcome email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send login notification email
     * 
     * @param array $userData User data
     * @param string $loginTime Login timestamp
     * @param string $ipAddress User IP address
     * @return array Result array with success status
     */
    public function sendLoginNotification($userData, $loginTime, $ipAddress) {
        try {
            // Skip login notifications for now (can be enabled in settings)
            if (!env('SEND_LOGIN_NOTIFICATIONS', false)) {
                return ['success' => true, 'message' => 'Login notifications disabled'];
            }
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userData['email'], $userData['first_name'] . ' ' . $userData['last_name']);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Login Notification - Nananom Farms';
            
            $htmlBody = $this->getLoginNotificationTemplate($userData, $loginTime, $ipAddress);
            $textBody = $this->getLoginNotificationTextTemplate($userData, $loginTime, $ipAddress);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Login notification sent successfully'
            ];
            
        } catch (\Exception $e) {
            error_log("Login notification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send login notification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send booking confirmation email
     * 
     * @param array $bookingData Booking information
     * @param array $userData User information
     * @param array $serviceData Service information
     * @return array Result array with success status
     */
    public function sendBookingConfirmation($bookingData, $userData, $serviceData) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userData['email'], $userData['first_name'] . ' ' . $userData['last_name']);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Booking Confirmation - Nananom Farms';
            
            $htmlBody = $this->getBookingConfirmationTemplate($bookingData, $userData, $serviceData);
            $textBody = $this->getBookingConfirmationTextTemplate($bookingData, $userData, $serviceData);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Booking confirmation sent successfully'
            ];
            
        } catch (\Exception $e) {
            error_log("Booking confirmation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send booking confirmation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send enquiry acknowledgment email
     * 
     * @param array $enquiryData Enquiry information
     * @param array $userData User information
     * @return array Result array with success status
     */
    public function sendEnquiryAcknowledgment($enquiryData, $userData) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userData['email'], $userData['first_name'] . ' ' . $userData['last_name']);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Enquiry Received - Nananom Farms';
            
            $htmlBody = $this->getEnquiryAcknowledgmentTemplate($enquiryData, $userData);
            $textBody = $this->getEnquiryAcknowledgmentTextTemplate($enquiryData, $userData);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Enquiry acknowledgment sent successfully'
            ];
            
        } catch (\Exception $e) {
            error_log("Enquiry acknowledgment error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send enquiry acknowledgment: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send admin notification for new booking
     * 
     * @param array $bookingData Booking information
     * @param array $userData User information
     * @param array $serviceData Service information
     * @return array Result array with success status
     */
    public function sendAdminBookingNotification($bookingData, $userData, $serviceData) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($this->adminEmail, 'Admin');
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Booking Received - Nananom Farms';
            
            $htmlBody = $this->getAdminBookingNotificationTemplate($bookingData, $userData, $serviceData);
            $textBody = $this->getAdminBookingNotificationTextTemplate($bookingData, $userData, $serviceData);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Admin booking notification sent successfully'
            ];
            
        } catch (\Exception $e) {
            error_log("Admin booking notification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send admin booking notification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send admin notification for new enquiry
     * 
     * @param array $enquiryData Enquiry information
     * @param array $userData User information
     * @return array Result array with success status
     */
    public function sendAdminEnquiryNotification($enquiryData, $userData) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($this->adminEmail, 'Admin');
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Enquiry Received - Nananom Farms';
            
            $htmlBody = $this->getAdminEnquiryNotificationTemplate($enquiryData, $userData);
            $textBody = $this->getAdminEnquiryNotificationTextTemplate($enquiryData, $userData);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Admin enquiry notification sent successfully'
            ];
            
        } catch (\Exception $e) {
            error_log("Admin enquiry notification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send admin enquiry notification: ' . $e->getMessage()
            ];
        }
    }
    
    // Email Templates
    
    private function getWelcomeEmailTemplate($userData) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to Nananom Farms</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c5530; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .btn { display: inline-block; padding: 12px 24px; background: #2c5530; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Nananom Farms!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userData['first_name']}!</h2>
                    <p>Thank you for registering with Nananom Farms. We're excited to have you as part of our community!</p>
                    
                    <p>Your account has been successfully created with the following details:</p>
                    <ul>
                        <li><strong>Name:</strong> {$userData['first_name']} {$userData['last_name']}</li>
                        <li><strong>Email:</strong> {$userData['email']}</li>
                        <li><strong>Phone:</strong> {$userData['phone']}</li>
                    </ul>
                    
                    <p>You can now:</p>
                    <ul>
                        <li>Browse our services</li>
                        <li>Make bookings</li>
                        <li>Submit enquiries</li>
                        <li>Manage your profile</li>
                    </ul>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to contact us.</p>
                    
                    <p>Best regards,<br>The Nananom Farms Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nananom Farms. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getWelcomeEmailTextTemplate($userData) {
        return "
        Welcome to Nananom Farms!
        
        Hello {$userData['first_name']}!
        
        Thank you for registering with Nananom Farms. We're excited to have you as part of our community!
        
        Your account has been successfully created with the following details:
        - Name: {$userData['first_name']} {$userData['last_name']}
        - Email: {$userData['email']}
        - Phone: {$userData['phone']}
        
        You can now:
        - Browse our services
        - Make bookings
        - Submit enquiries
        - Manage your profile
        
        If you have any questions or need assistance, please don't hesitate to contact us.
        
        Best regards,
        The Nananom Farms Team
        
        © 2025 Nananom Farms. All rights reserved.
        ";
    }
    
    private function getLoginNotificationTemplate($userData, $loginTime, $ipAddress) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Login Notification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c5530; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Login Notification</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userData['first_name']}!</h2>
                    <p>We wanted to let you know that your account was accessed:</p>
                    
                    <ul>
                        <li><strong>Time:</strong> {$loginTime}</li>
                        <li><strong>IP Address:</strong> {$ipAddress}</li>
                    </ul>
                    
                    <p>If this was you, no action is needed. If you didn't log in, please contact us immediately.</p>
                    
                    <p>Best regards,<br>The Nananom Farms Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nananom Farms. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getLoginNotificationTextTemplate($userData, $loginTime, $ipAddress) {
        return "
        Login Notification
        
        Hello {$userData['first_name']}!
        
        We wanted to let you know that your account was accessed:
        - Time: {$loginTime}
        - IP Address: {$ipAddress}
        
        If this was you, no action is needed. If you didn't log in, please contact us immediately.
        
        Best regards,
        The Nananom Farms Team
        
        © 2025 Nananom Farms. All rights reserved.
        ";
    }
    
    private function getBookingConfirmationTemplate($bookingData, $userData, $serviceData) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Booking Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c5530; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Booking Confirmation</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userData['first_name']}!</h2>
                    <p>Thank you for your booking with Nananom Farms. Your booking has been confirmed!</p>
                    
                    <div class='booking-details'>
                        <h3>Booking Details:</h3>
                        <ul>
                            <li><strong>Booking ID:</strong> #{$bookingData['id']}</li>
                            <li><strong>Service:</strong> {$serviceData['name']}</li>
                            <li><strong>Date:</strong> {$bookingData['booking_date']}</li>
                            <li><strong>Time:</strong> {$bookingData['booking_time']}</li>
                            <li><strong>Status:</strong> {$bookingData['status']}</li>
                        </ul>
                    </div>
                    
                    <p>We will contact you soon to confirm the details and provide any additional information.</p>
                    
                    <p>If you have any questions, please don't hesitate to contact us.</p>
                    
                    <p>Best regards,<br>The Nananom Farms Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nananom Farms. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getBookingConfirmationTextTemplate($bookingData, $userData, $serviceData) {
        return "
        Booking Confirmation
        
        Hello {$userData['first_name']}!
        
        Thank you for your booking with Nananom Farms. Your booking has been confirmed!
        
        Booking Details:
        - Booking ID: #{$bookingData['id']}
        - Service: {$serviceData['name']}
        - Date: {$bookingData['booking_date']}
        - Time: {$bookingData['booking_time']}
        - Status: {$bookingData['status']}
        
        We will contact you soon to confirm the details and provide any additional information.
        
        If you have any questions, please don't hesitate to contact us.
        
        Best regards,
        The Nananom Farms Team
        
        © 2025 Nananom Farms. All rights reserved.
        ";
    }
    
    private function getEnquiryAcknowledgmentTemplate($enquiryData, $userData) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Enquiry Received</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c5530; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .enquiry-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Enquiry Received</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userData['first_name']}!</h2>
                    <p>Thank you for your enquiry. We have received your message and will respond as soon as possible.</p>
                    
                    <div class='enquiry-details'>
                        <h3>Your Enquiry:</h3>
                        <ul>
                            <li><strong>Enquiry ID:</strong> #{$enquiryData['id']}</li>
                            <li><strong>Subject:</strong> {$enquiryData['subject']}</li>
                            <li><strong>Date:</strong> {$enquiryData['created_at']}</li>
                            <li><strong>Status:</strong> {$enquiryData['status']}</li>
                        </ul>
                        <p><strong>Message:</strong></p>
                        <p>{$enquiryData['message']}</p>
                    </div>
                    
                    <p>Our team will review your enquiry and get back to you within 24-48 hours.</p>
                    
                    <p>Best regards,<br>The Nananom Farms Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nananom Farms. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getEnquiryAcknowledgmentTextTemplate($enquiryData, $userData) {
        return "
        Enquiry Received
        
        Hello {$userData['first_name']}!
        
        Thank you for your enquiry. We have received your message and will respond as soon as possible.
        
        Your Enquiry:
        - Enquiry ID: #{$enquiryData['id']}
        - Subject: {$enquiryData['subject']}
        - Date: {$enquiryData['created_at']}
        - Status: {$enquiryData['status']}
        
        Message: {$enquiryData['message']}
        
        Our team will review your enquiry and get back to you within 24-48 hours.
        
        Best regards,
        The Nananom Farms Team
        
        © 2025 Nananom Farms. All rights reserved.
        ";
    }
    
    private function getAdminBookingNotificationTemplate($bookingData, $userData, $serviceData) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>New Booking Received</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c5530; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Booking Received</h1>
                </div>
                <div class='content'>
                    <h2>New Booking Alert</h2>
                    <p>A new booking has been received and requires your attention.</p>
                    
                    <div class='booking-details'>
                        <h3>Booking Details:</h3>
                        <ul>
                            <li><strong>Booking ID:</strong> #{$bookingData['id']}</li>
                            <li><strong>Customer:</strong> {$userData['first_name']} {$userData['last_name']}</li>
                            <li><strong>Email:</strong> {$userData['email']}</li>
                            <li><strong>Phone:</strong> {$userData['phone']}</li>
                            <li><strong>Service:</strong> {$serviceData['name']}</li>
                            <li><strong>Date:</strong> {$bookingData['booking_date']}</li>
                            <li><strong>Time:</strong> {$bookingData['booking_time']}</li>
                            <li><strong>Status:</strong> {$bookingData['status']}</li>
                        </ul>
                    </div>
                    
                    <p>Please log in to the admin panel to review and process this booking.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nananom Farms Admin System</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getAdminBookingNotificationTextTemplate($bookingData, $userData, $serviceData) {
        return "
        New Booking Received
        
        A new booking has been received and requires your attention.
        
        Booking Details:
        - Booking ID: #{$bookingData['id']}
        - Customer: {$userData['first_name']} {$userData['last_name']}
        - Email: {$userData['email']}
        - Phone: {$userData['phone']}
        - Service: {$serviceData['name']}
        - Date: {$bookingData['booking_date']}
        - Time: {$bookingData['booking_time']}
        - Status: {$bookingData['status']}
        
        Please log in to the admin panel to review and process this booking.
        
        © 2025 Nananom Farms Admin System
        ";
    }
    
    private function getAdminEnquiryNotificationTemplate($enquiryData, $userData) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>New Enquiry Received</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c5530; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .enquiry-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Enquiry Received</h1>
                </div>
                <div class='content'>
                    <h2>New Enquiry Alert</h2>
                    <p>A new enquiry has been received and requires your attention.</p>
                    
                    <div class='enquiry-details'>
                        <h3>Enquiry Details:</h3>
                        <ul>
                            <li><strong>Enquiry ID:</strong> #{$enquiryData['id']}</li>
                            <li><strong>Customer:</strong> {$userData['first_name']} {$userData['last_name']}</li>
                            <li><strong>Email:</strong> {$userData['email']}</li>
                            <li><strong>Phone:</strong> {$userData['phone']}</li>
                            <li><strong>Subject:</strong> {$enquiryData['subject']}</li>
                            <li><strong>Date:</strong> {$enquiryData['created_at']}</li>
                            <li><strong>Status:</strong> {$enquiryData['status']}</li>
                        </ul>
                        <p><strong>Message:</strong></p>
                        <p>{$enquiryData['message']}</p>
                    </div>
                    
                    <p>Please log in to the admin panel to review and respond to this enquiry.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nananom Farms Admin System</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getAdminEnquiryNotificationTextTemplate($enquiryData, $userData) {
        return "
        New Enquiry Received
        
        A new enquiry has been received and requires your attention.
        
        Enquiry Details:
        - Enquiry ID: #{$enquiryData['id']}
        - Customer: {$userData['first_name']} {$userData['last_name']}
        - Email: {$userData['email']}
        - Phone: {$userData['phone']}
        - Subject: {$enquiryData['subject']}
        - Date: {$enquiryData['created_at']}
        - Status: {$enquiryData['status']}
        
        Message: {$enquiryData['message']}
        
        Please log in to the admin panel to review and respond to this enquiry.
        
        © 2025 Nananom Farms Admin System
        ";
    }
}
?>