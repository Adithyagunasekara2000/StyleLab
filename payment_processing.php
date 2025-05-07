<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once 'cart_functions.php';

/**
 * Process payment and complete order
 * 
 * @param array $payment_data Payment details from form
 * @return array Response with status and message
 */
function processPayment($payment_data) {
    // Validate payment data
    $errors = validatePaymentData($payment_data);
    if (!empty($errors)) {
        return [
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ];
    }

    $cart_total = calculateCartTotal();
    
    if ($cart_total <= 0 || empty($_SESSION["shopping_cart"])) {
        return [
            'status' => 'error',
            'message' => 'Your cart is empty or the total is invalid'
        ];
    }

    switch ($payment_data['payment_method']) {
        case 'credit_card':
            $result = processCreditCardPayment($payment_data, $cart_total);
            break;
        case 'paypal':
            $result = processPayPalPayment($payment_data, $cart_total);
            break;
        case 'stripe':
            $result = processStripePayment($payment_data, $cart_total);
            break;
        default:
            return [
                'status' => 'error',
                'message' => 'Invalid payment method selected'
            ];
    }

    if ($result['status'] === 'success') {
        $order_id = createOrder($payment_data, $cart_total);
        
        if ($order_id) {
            $_SESSION["shopping_cart"] = [];
            
            return [
                'status' => 'success',
                'message' => 'Payment processed successfully!',
                'order_id' => $order_id
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Payment was processed but order could not be created. Please contact support.'
            ];
        }
    }

    return $result;
}

/**
 * Validate payment form data
 * 
 * @param array $data Payment form data
 * @return array Validation errors
 */
function validatePaymentData($data) {
    $errors = [];
    
    // Basic validation for credit card payments
    if ($data['payment_method'] === 'credit_card') {
        if (empty($data['card_number']) || !preg_match('/^[0-9]{13,19}$/', preg_replace('/\s+/', '', $data['card_number']))) {
            $errors['card_number'] = 'Please enter a valid card number';
        }
        
        if (empty($data['card_expiry']) || !preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $data['card_expiry'])) {
            $errors['card_expiry'] = 'Please enter a valid expiry date (MM/YY)';
        }
        
        if (empty($data['card_cvv']) || !preg_match('/^[0-9]{3,4}$/', $data['card_cvv'])) {
            $errors['card_cvv'] = 'Please enter a valid CVV code';
        }
    }
    
    // Basic validation for billing information
    if (empty($data['billing_name'])) {
        $errors['billing_name'] = 'Please enter your full name';
    }
    
    if (empty($data['billing_email']) || !filter_var($data['billing_email'], FILTER_VALIDATE_EMAIL)) {
        $errors['billing_email'] = 'Please enter a valid email address';
    }
    
    if (empty($data['billing_address'])) {
        $errors['billing_address'] = 'Please enter your billing address';
    }
    
    return $errors;
}

/**
 * Process credit card payment
 * NOTE: In production, this should use a secure payment gateway
 * 
 * @param array $payment_data Payment details
 * @param float $amount Amount to charge
 * @return array Response with status and message
 */
function processCreditCardPayment($payment_data, $amount) {
  
    $card_number = preg_replace('/\s+/', '', $payment_data['card_number']);
    $last_four = substr($card_number, -4);
    
    if ($last_four === '0000') {
        return [
            'status' => 'error',
            'message' => 'Your card was declined. Please try another payment method.'
        ];
    }
    
    return [
        'status' => 'success',
        'message' => 'Credit card payment processed successfully',
        'transaction_id' => 'CC-' . time() . '-' . rand(1000, 9999)
    ];
}

/**
 * Process PayPal payment
 * 
 * @param array $payment_data Payment details
 * @param float $amount Amount to charge
 * @return array Response with status and message
 */
function processPayPalPayment($payment_data, $amount) {
 
    return [
        'status' => 'success',
        'message' => 'PayPal payment processed successfully',
        'transaction_id' => 'PP-' . time() . '-' . rand(1000, 9999)
    ];
}

/**
 * Process Stripe payment
 * 
 * @param array $payment_data Payment details
 * @param float $amount Amount to charge
 * @return array Response with status and message
 */
function processStripePayment($payment_data, $amount) {
   
    return [
        'status' => 'success',
        'message' => 'Stripe payment processed successfully',
        'transaction_id' => 'ST-' . time() . '-' . rand(1000, 9999)
    ];
}

/**
 * Create order in the database after successful payment
 * 
 * @param array $payment_data Payment and customer details
 * @param float $total Order total
 * @return int|bool Order ID on success, false on failure
 */
function createOrder($payment_data, $total) {
 
    $order_id = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
 
    return $order_id;
}