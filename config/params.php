<?php

return [
    'admin_email' => 'admin@example.com',
    'from_email' => 'noreply@example.com',
    'token_type' => 'bearer',
    //'token_expire_time' => 86400,// 1 day
    'token_expire_time' => (86400 * 365),// 365 day
    //'token_expire_time' => 3.154e+7,// 365 day
    'token_segment' => 1,
    //'default_page_size' => 10,
    'default_page_size' => 10000,
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
        'option_key' => 'transaction_fees'
    ],
    'km_range' => [
        'option_key' => 'km_range'
    ],


    //'mail_image_base_path' => Yii::$app->request->baseUrl . '/web',
    'mail_image_base_path' => 'http://203.109.113.157/bridecycle/web',


    // Recent time for general uses in system
    'recent_time' => '1 week',

    /**
     * Use for payment gateway (paypal)
     */
//    'paypal_Client_ID'=>'',
//    'paypal_Client_Secret'=>'',
//    'paypal_mode'=>'',
    'paypal_payment_currency' => 'USD',

    /**
     * Paypal (CTPL) sendbox account credential.
     *
     * Username: paypaltest.ctpl@gmail.com
     * Password: ctpl@dev
     *
     * Username: buyertest.gwm@gmail.com
     * Password: ctpl@dev
     *
     * Username: merchant.gwm@gmail.com
     * Password: ctpl@dev
     *
     * Username : harshil.cmarix-facilitator@gmail.com
     * Password: ctpl@dev
     *
     * @Note : CTPL Testing Account
     */
    'paypal_client_id' => 'AcjJ_Ugx14mHKvPuPuCK7Uii8I8SL1yzcW585G9zp6UjCXvVMga7CmBOSMH5HVO8fx4YXtY8LCPULZNm',
    'paypal_client_secret' => 'ELW5y0ZLN58ZkPSDIhDlC6-A6cIZWysgUxtlFq4e5mAAknDeSwtFFOfG4-IRgqE9Pb1w_6wtFFZXeRG4',
    'paypal_mode' => 'sandbox',

    /**
     * Mobile APi current latest version
     */
    'mobile_api_latest_version' => 'v1',



    // Live // client
    //'google_map_api_key'=>'AIzaSyBuITv7RS9RWyEoZkJ1df6-zrw6ZT_C36Q',
    'google_map_api_key'=>'AIzaSyAO1tfGWm30RyXQbiaVGvKxo0M5h9-4bP8',
    //'google_map_api_key'=>'AIzaSyB0WhTsW00dL_NTfkIbUgxXc25zUATbhAY',
    // Local // my
    //'google_map_api_key'=>'AIzaSyDTBLZMRefUIIM9f0StWgeQ1lVglay22wk',


];
