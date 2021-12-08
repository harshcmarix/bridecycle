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

    // Live // client
    //'google_map_api_key'=>'AIzaSyBuITv7RS9RWyEoZkJ1df6-zrw6ZT_C36Q',
    'google_map_api_key' => 'AIzaSyAO1tfGWm30RyXQbiaVGvKxo0M5h9-4bP8',

    'bridecycle_product_sell_charges_percentage' => '5%',

    // Per product order charges
    //'bridecycle_product_order_charge_percentage' => '11',
    'bridecycle_product_order_charge_percentage' => '8',

    // Google Play store subscription package name, Used for check subscription expire status cronjob.
    'google_play_store_subscription_package_name' => 'com.bridecycle',
    'google_play_store_subscription_app_name' => 'bridecycle',

];
