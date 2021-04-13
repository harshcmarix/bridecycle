<?php

return [
    'admin_email' => 'admin@example.com',
    'from_email' => 'noreply@example.com',
    'token_type' => 'bearer',
    'token_expire_time' => 86400,
    'token_segment' => 1,
    'default_page_size' => 10,
    // Used in reset password expire token
    'password_reset_token_expire_time' => 3600,
    'support_email' => 'robot@devreadwrite.com',
    /**
     * Used for profile picture thumb
     */
    'profile_picture_thumb_width' => 200,
    'profile_picture_thumb_height' => 200,
    'profile_picture_thumb_quality' => 80,

    // Send mail from email id
    'adminEmail' => 'admin@yopmail.com',
    //settings attributes
    'transaction_fees' => [
          'option_key'=>'transaction_fees'
    ],
    'km_range' => [
          'option_key'=>'km_range'
    ],
];
