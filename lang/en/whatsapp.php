<?php

/** @return array<string, string> */
return [
    'otp_message' => "🔐 *Your OTP Code:* :otp\n\nCode is valid for 5 minutes.\nDo not share this code with anyone.",
    'payment_success' => "✅ *Payment Successful!*\n\nHello :name,\n\nPayment for order *:order* has been received.\n\n📋 *:item*\n💰 *:total*\n📌 Status: *PAID*\n\nThank you for your payment. Your order will be processed soon.\n\n:app",
    'payment_pending' => "🕓 *Awaiting Admin Verification*\n\nHello :name,\n\nPayment confirmation for order *:order* has been received.\n\n📋 *:item*\n💰 *:total*\n\nYour payment will be verified by admin, usually within 24 hours.\n\n:app",
    'new_message_from' => "New message from :name\n\n",
    'new_message_reply' => "\n\nReply in the app to respond.",
];
