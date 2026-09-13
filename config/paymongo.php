<?php
/**
 * PAYMONGO INTEGRATION SERVICE
 * Handles PayMongo API communication for W Island Hub Backpackers Inn.
 * Supports Checkout Sessions (Cards, GCash, Maya, GrabPay), Verification, and Webhooks.
 */

// PayMongo Test API Credentials
defined('PAYMONGO_SECRET_KEY') || define('PAYMONGO_SECRET_KEY', 'sk_test_WF6HWnFXZACjnNfRrCkjF2eW');
defined('PAYMONGO_PUBLIC_KEY') || define('PAYMONGO_PUBLIC_KEY', 'pk_test_gh33x7D1tsYhNafxorEReG7k');
defined('PAYMONGO_API_BASE')   || define('PAYMONGO_API_BASE', 'https://api.paymongo.com/v1/');

class PayMongoService {
    /**
     * Send an HTTP request to PayMongo REST API.
     *
     * @param string $endpoint e.g. 'checkout_sessions', 'links'
     * @param string $method 'POST', 'GET', etc.
     * @param array|null $payload Data array for POST/PUT
     * @return array Decoded JSON response or ['error' => '...']
     */
    public static function request(string $endpoint, string $method = 'GET', ?array $payload = null): array {
        $url = rtrim(PAYMONGO_API_BASE, '/') . '/' . ltrim($endpoint, '/');
        
        $ch = curl_init($url);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => PAYMONGO_SECRET_KEY . ':sk_test_WF6HWnFXZACjnNfRrCkjF2eW',
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        } elseif (strtoupper($method) !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        }

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $curlError
            ];
        }

        $decoded = json_decode($rawResponse, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'http_code' => $httpCode,
                'data' => $decoded['data'] ?? $decoded
            ];
        }

        $errorMessage = 'PayMongo API request failed with code ' . $httpCode;
        if (isset($decoded['errors']) && is_array($decoded['errors']) && !empty($decoded['errors'])) {
            $errList = [];
            foreach ($decoded['errors'] as $err) {
                $errList[] = ($err['detail'] ?? ($err['code'] ?? 'Unknown error'));
            }
            $errorMessage = implode('; ', $errList);
        }

        return [
            'success' => false,
            'http_code' => $httpCode,
            'error' => $errorMessage,
            'raw' => $decoded
        ];
    }

    /**
     * Convert PHP Peso amount to PayMongo Centavos (Integer).
     * e.g., 150.50 -> 15050
     */
    public static function toCentavos(float $amount): int {
        return (int) round($amount * 100);
    }

    /**
     * Convert PayMongo Centavos back to PHP Peso float.
     * e.g., 15050 -> 150.50
     */
    public static function toPesos(int $centavos): float {
        return (float) ($centavos / 100);
    }

    /**
     * Create a PayMongo Hosted Checkout Session for a reservation.
     *
     * @param array $bookingData
     * @param string $successUrl
     * @param string $cancelUrl
     * @return array
     */
    public static function createCheckoutSession(array $bookingData, string $successUrl, string $cancelUrl): array {
        $amount = (float) ($bookingData['total_amount'] ?? $bookingData['total'] ?? 0.00);
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Invalid booking amount.'];
        }

        $roomName = $bookingData['room_name'] ?? 'Room Stay';
        $nights   = (int) ($bookingData['nights'] ?? 1);
        $roomsCount = (int) ($bookingData['rooms_count'] ?? 1);
        $refId    = $bookingData['ref_id'] ?? 'WIH-REF';
        $guestName = $bookingData['guest_name'] ?? 'Guest';
        $guestEmail = $bookingData['guest_email'] ?? 'guest@wislandhub.ph';
        $guestPhone = $bookingData['guest_phone'] ?? '';

        // Line items breakdown
        $lineItems = [
            [
                'currency'    => 'PHP',
                'amount'      => self::toCentavos($amount),
                'name'        => "W Island Hub: {$roomName} ({$nights} Night" . ($nights > 1 ? 's' : '') . ")",
                'quantity'    => 1,
                'description' => "Booking Ref: {$refId} • {$roomsCount} Room(s) • Check-in: " . ($bookingData['check_in'] ?? 'Pending')
            ]
        ];

        // Format phone safely for PayMongo (optional)
        $cleanPhone = preg_replace('/[^0-9+]/', '', $guestPhone);
        if (empty($cleanPhone) || strlen($cleanPhone) < 7) {
            $cleanPhone = '09753614030';
        }

        $payload = [
            'data' => [
                'attributes' => [
                    'billing' => [
                        'name'  => $guestName,
                        'email' => !empty($guestEmail) ? $guestEmail : 'guest@wislandhub.ph',
                        'phone' => $cleanPhone
                    ],
                    'line_items' => $lineItems,
                    'payment_method_types' => [
                        'card',
                        'gcash',
                        'paymaya',
                        'grab_pay'
                    ],
                    'description'         => "Reservation #{$refId} - {$roomName} at W Island Hub Backpackers Inn",
                    'send_email_receipt'  => true,
                    'show_description'    => true,
                    'show_line_items'     => true,
                    'success_url'         => $successUrl,
                    'cancel_url'          => $cancelUrl,
                    'metadata'            => [
                        'ref_id'      => $refId,
                        'booking_id'  => $bookingData['id'] ?? null,
                        'room_id'     => $bookingData['room_id'] ?? null,
                        'user_id'     => $bookingData['user_id'] ?? null
                    ]
                ]
            ]
        ];

        $res = self::request('checkout_sessions', 'POST', $payload);
        if (!$res['success']) {
            return $res;
        }

        $data = $res['data'];
        $sessionId   = $data['id'] ?? null;
        $checkoutUrl = $data['attributes']['checkout_url'] ?? null;

        return [
            'success'      => true,
            'session_id'   => $sessionId,
            'checkout_url' => $checkoutUrl,
            'raw'          => $data
        ];
    }

    /**
     * Retrieve a Checkout Session from PayMongo by session ID.
     *
     * @param string $sessionId
     * @return array
     */
    public static function getCheckoutSession(string $sessionId): array {
        return self::request('checkout_sessions/' . urlencode($sessionId), 'GET');
    }

    /**
     * Retrieve a Payment by payment ID.
     *
     * @param string $paymentId
     * @return array
     */
    public static function getPayment(string $paymentId): array {
        return self::request('payments/' . urlencode($paymentId), 'GET');
    }
}

