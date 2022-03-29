<?php

return [
    'admin_email' => 'admin@example.com',
    'from_email' => 'noreply@example.com',
    'token_type' => 'bearer',
    //'token_expire_time' => 86400,// 1 day
    'token_expire_time' => (86400 * 365),// 365 day
    'token_segment' => 1,
    //'default_page_size' => 10,
    'default_page_size' => 10000,
    'default_page_size_for_backend' => 20,
    // Used in reset password expire token
    'password_reset_token_expire_time' => 3600, // in seconds
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
        'option_key' => 'transaction_fees'
    ],
    'km_range' => [
        'option_key' => 'km_range'
    ],
    //'mail_image_base_path' => Yii::$app->request->baseUrl . '/web',
    'mail_image_base_path' => 'http://203.109.113.157/bridecycle/web',
    //'mail_image_base_path' => 'http://bridecycle.tk/bridecycle/web',

    // Recent time for general uses in system
    'recent_time' => '1 week',

    // Recent search product list default limit (use for API)
    'recent_search_product_list_default_limit' => 20,


    'allow_return_product_days' => '14',

    /**
     * Use for payment gateway (paypal)
     */
    'paypal_payment_currency' => 'USD',

    /**
     * Paypal sendbox/live account credential.
     *
     * Username: laura@bridecycle.com
     * Password: Bridecycle2021!
     *
     */

//    'paypal_client_id' => 'AcjJ_Ugx14mHKvPuPuCK7Uii8I8SL1yzcW585G9zp6UjCXvVMga7CmBOSMH5HVO8fx4YXtY8LCPULZNm',
//    'paypal_client_secret' => 'ELW5y0ZLN58ZkPSDIhDlC6-A6cIZWysgUxtlFq4e5mAAknDeSwtFFOfG4-IRgqE9Pb1w_6wtFFZXeRG4',
//    'paypal_mode' => 'sandbox',

    'paypal_client_id' => 'AfDLxm3137oFQRoYX2ve1tmmtuvjJy4me5yjkMMe_Z0o8-y0W9yOUIU_7hBZYqx-AbqcJW2xJnspmK0U',
    'paypal_client_secret' => 'EO5-jpNp_g7mt3uNeF8XNxIieZWshUVSMiP4Vgiri0FeFqyqPFA4LOyHU2nZ-dnFYvpp6CN38sO3eX5q',
    'paypal_mode' => 'sandbox',

    /**
     * Mobile API current latest version
     */
    //'mobile_api_latest_version' => 'v1',
    'mobile_api_latest_version' => 'v2',

    /**
     *  Credential of google
     *
     *  email: bridecycle@googlemail.com
     *  password: You$hop3000
     *
     */

    // Live // client - BC
    //'google_map_api_key'=>'AIzaSyBuITv7RS9RWyEoZkJ1df6-zrw6ZT_C36Q', // Not working Now
    // Live // client -BT
    'google_map_api_key' => 'AIzaSyAO1tfGWm30RyXQbiaVGvKxo0M5h9-4bP8',

    'bridecycle_product_sell_charges_percentage' => '5%',

    // Per product order charges
    //'bridecycle_product_order_charge_percentage' => '11',
    'bridecycle_product_order_charge_percentage' => '8',

    // Google Play store subscription package name, Its Used for check subscription expire status cronjob.
    'google_play_store_subscription_package_name' => 'com.bridecycle',
    'google_play_store_subscription_app_name' => 'bridecycle',

    'app_store_subscription_shared_secret_key' => '15ac9910d3a84fa89cad2d9736b3391f', // For Production/sandbox
    // Make changes in cronjob controller from admin for production Mode

    /**
     *  Stripe Credentials
     *
     */
    /**
     * Test/Sandbox account
     * ------------------------
     * username : mrugen.cmarix+01@gmail.com
     * password : ctpl@dev123456
     *
     * username : laura@bridecycle.com
     * password : BrideCycle2022_You$hop3000
     *
     * URL : https://dashboard.stripe.com/test/dashboard
     *
     *  Two step verification mobile number : 80008 53403 (Mrugen Patel)
     *
     */

    //  'stripe_publishable_Key' => 'pk_test_51KOHmqF7nc7QCOpKmxWAvH8eyuiMRQgMh3Bk3VZwipJdi5ySHFUDrx2PfnC0HlhWvkbGbLP0LOAA9BUOvtzFdyWM00UsaqoTNN',
//    'stripe_secret_key' => 'sk_test_51KOHmqF7nc7QCOpK8atLEEHbBPPEJcY9LNCtt75gdUJXGl1wWrdkCEaZtl8YU0O9t0Y1FcA19oSyIsu1YQO1E9oI00gmui63oG',

//    'stripe_publishable_Key' => 'pk_test_51Kaw45SHffITXCbkuSa0AdD3iRzL56s2xoh4446bPYFKVP8CvU0gxkbR15TS7b0IblMAIHGblAfo5V9Aum5HeegV00TcU0ROYD',
//    'stripe_secret_key' => 'sk_test_51Kaw45SHffITXCbkqmKh6JeHwIl3PB5sAbPXXOUA4UGambjBzUy9Vch5uY5CtsIQoaK3MxzUXqqXbpshqOF4ep7f00AUHiOmgl',

// Laura Test
    'stripe_publishable_Key' => 'pk_test_51KKNVyAvFy5NACFpFkGdLGGEmD9LwgHYMtmXwtknuhzZqaaWgZZCjJcvSiNvUqcHiuYqDEw8aCoEnnHVMR6YmIFc00qi9yi9zm',
    'stripe_secret_key' => 'sk_test_51KKNVyAvFy5NACFpRzFxqPpQjEYDMnc0SOuCV1VOt8lbNyVISP7TlcaXOteHTcd2uK7mCRR7gZSlvj1rSjpCCAZv00H3DG2OUw',
    // Live
    'client_id' => 'ca_L4rzTsUAjbt0UXJw4wYt6vK4LxaGKVPu',
    //'stripe_publishable_Key' => 'pk_live_51KKNVyAvFy5NACFpAgr2TKYYBoluv3okrwVpX4Ukg03b5Avw4cxJQCDHdXX8NIBEqwkWWr69KONtCMEroPUq8F0H008qyaRymi',
    //'stripe_secret_key' => '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',

    'other_support_language' => ['english', 'german'],


];
