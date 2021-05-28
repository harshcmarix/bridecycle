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
        'option_key' => 'transaction_fees'
    ],
    'km_range' => [
        'option_key' => 'km_range'
    ],

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
    'paypal_client_id' => 'AVh7TWRjOcq7DkACNDj_8SFKlVkL520yJH-Km5NwnvmTvsucgbTM4bAqKizRD1TxvJ_ZJiNzmqxma09F',
    'paypal_client_secret' => 'EH0j7EnQLstPr2On0pj5lgcND8dygD9xz_NGELdvUq4GWylKOuUxNJwVqd95yw5KmCWcOIfjxVfBIRds',
    'paypal_mode' => 'sandbox',

    /**
     * Mobile APi current latest version
     */
    'mobile_api_latest_version' => 'v1'

];
