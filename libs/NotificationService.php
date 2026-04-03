<?php
/**
 * Centralized Notification Service for Groceesary
 * Handles SMS and WhatsApp integration.
 */
class NotificationService {
    
    private static $instance = null;

    private function __construct() {
        // Private constructor for Singleton
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send a welcome message to a new user/vendor
     * @param string $phone The mobile number (e.g. 9876543210)
     * @param string $name  The user's name
     * @param string $role  The role (customer or vendor)
     */
    public function sendWelcomeMessage($phone, $name, $role) {
        if (!defined('ENABLE_NOTIFICATIONS') || !ENABLE_NOTIFICATIONS) {
            return false;
        }

        $app_name = defined('APP_NAME') ? APP_NAME : 'Groceesary';
        $message = "";

        if ($role === 'vendor') {
            $message = "नमस्कार {$name}! स्वागत है {$app_name} परिवार में। अब आप अपनी दुकान ऑनलाइन चला सकते हैं। कृपया अपना स्टोर सेटअप पूरा करें।";
        } else {
            $message = "नमस्ते {$name}! {$app_name} पर रजिस्टर करने के लिए धन्यवाद। अब खरीदें ताज़ी सब्जियाँ और राशन सबसे कम दामों पर।";
        }

        return $this->dispatch($phone, $message);
    }

    /**
     * Send a 6-digit OTP for phone verification
     * @param string $phone The mobile number
     * @param string $otp   The 6-digit code
     */
    public function sendOTP($phone, $otp) {
        if (!defined('ENABLE_NOTIFICATIONS') || !ENABLE_NOTIFICATIONS) {
            return false;
        }

        $app_name = defined('APP_NAME') ? APP_NAME : 'Groceesary';
        $message = "आपका {$app_name} वेरिफिकेशन कोड है: {$otp}। यह कोड 10 मिनट के लिए मान्य है। कृपया इसे किसी के साथ साझा न करें।";

        return $this->dispatch($phone, $message);
    }

    /**
     * Internal dispatcher for SMS/WhatsApp API
     * Currently implemented as a logger for local development.
     */
    private function dispatch($phone, $message) {
        // In local/development mode, we log the message to a file instead of making real API calls.
        $log_entry = "[" . date('Y-m-d H:i:s') . "] TO: {$phone} | MSG: {$message}\n";
        
        $log_file = defined('LOG_PATH') ? LOG_PATH . 'notifications.log' : __DIR__ . '/../logs/notifications.log';
        @file_put_contents($log_file, $log_entry, FILE_APPEND);

        // --- Production Integration Example (e.g. Twilio or Msg91) ---
        /*
        $apiKey = SMS_API_KEY;
        $url = "https://api.provider.com/send?apiKey={$apiKey}&to={$phone}&msg=" . urlencode($message);
        $response = @file_get_contents($url);
        return $response ? true : false;
        */

        return true; 
    }
}
