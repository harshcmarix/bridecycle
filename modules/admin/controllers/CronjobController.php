<?php

namespace app\modules\admin\controllers;

use app\models\Notification;
use app\models\SearchHistory;
use app\models\UserPurchasedSubscriptions;
use app\modules\api\v2\models\User;
use ReceiptValidator\GooglePlay\SubscriptionResponse;
use ReceiptValidator\iTunes\ProductionResponse;
use ReceiptValidator\iTunes\SandboxResponse;
use yii\base\Exception;
use yii\web\Controller;
use Yii;
use app\models\Product as DB_Product;
use yii\web\HttpException;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use ReceiptValidator\iTunes\Validator as iTunesValidator;

class CronjobController extends Controller
{

    /**
     * @throws \Google\Exception
     *
     *  Check subscription is valid or expire for user(shop owner).
     *
     *  It is execute at every 10 minute.
     *
     */
    public function actionCheckPlayStoreSubscriptionStatus()
    {
        /**
         *
         *  IMPORTANT: If you add any services back in composer.json, you will need to remove the vendor/google/apiclient-services directory explicity for the change you made to have effect:
         *
         *  rm -r vendor/google/apiclient-services
         *  composer update
         *
         *  WHERE `device_platform` = 'android'
         */
        $query = UserPurchasedSubscriptions::find();
        $query->where("`id` IN (SELECT MAX(`id`) AS `id`
             FROM `user_purchased_subscriptions` 
             
             GROUP BY `user_id`) 
        ORDER BY `created_at` DESC");
        $userSubscriptions = $query->all();

        if (!empty($userSubscriptions)) {

            foreach ($userSubscriptions as $key => $userSubscriptionRow) {

                if (!empty($userSubscriptionRow) && $userSubscriptionRow instanceof UserPurchasedSubscriptions) {

                    // Check for Android User subscription start.
                    if (strtolower($userSubscriptionRow->device_platform) == UserPurchasedSubscriptions::DEVICE_PLATFORM_ANDROID) {

                        $googlePlayResponseSuccess = "";
                        $googlePlayResponseFail = "";
                        $subscriptionRespose = [];
                        $purchaseToken = $product_id = "";
                        if (!empty($userSubscriptionRow->subscription_response)) {
                            $subscriptionRespose = json_decode($userSubscriptionRow->subscription_response);

                            if (!empty($subscriptionRespose)) {

                                // Set Purchase token data.
                                if (!empty($subscriptionRespose) && !empty($subscriptionRespose->purchaseToken)) {
                                    $purchaseToken = $subscriptionRespose->purchaseToken;
                                }

                                // Set product ID data.
                                if (!empty($subscriptionRespose) && !empty($subscriptionRespose->productId)) {
                                    $product_id = $subscriptionRespose->productId;
                                }
                            }
                        }

                        // Start Android subscription check
                        if (!empty($subscriptionRespose) && !empty($purchaseToken) && !empty($product_id)) {

                            /**
                             *  For Google Play subscription Use
                             *
                             *  App ID / Service account Id as Below to use for google play store subscription
                             *
                             *  https://console.cloud.google.com/apis/credentials/domainverification?project=pc-api-5945952200218555652-968
                             *
                             */

                            $path = Yii::getAlias('@uploadsRelativePath') . '/google-app-credentials.json';

                            $googleClient = new \Google_Client();
                            $googleClient->setScopes([\Google\Service\AndroidPublisher::ANDROIDPUBLISHER]);
                            $googleClient->setApplicationName(Yii::$app->params['google_play_store_subscription_app_name']);
                            $googleClient->setAuthConfig($path);

                            $googleAndroidPublisher = new \Google\Service\AndroidPublisher($googleClient);
                            $validator = new PlayValidator($googleAndroidPublisher);

                            try {

                                $response = $validator->setPackageName(Yii::$app->params['google_play_store_subscription_package_name'])
                                    ->setProductId($product_id)
                                    ->setPurchaseToken($purchaseToken)
                                    ->validateSubscription();
                                $googlePlayResponseSuccess = $response;

                            } catch (\Exception $e) {

                                $googlePlayResponseFail = $e->getMessage();

                                //echo "Error: " . $e->getMessage();

                                // \Yii::info("\n------------Fail Subscription ----------------\n" . "userId:" . $userSubscriptionRow->user_id . "\n" . $e->getMessage(), 'notifyUserBasedOnAndroidGooglePlaySubscription');
                            }
                        }
                        // End Android subscription check

                        // Update User subscription data (in our DATABASE) start.
                        if (!empty($googlePlayResponseSuccess) && (empty($googlePlayResponseFail) || $googlePlayResponseFail == "")) {

                            if ($googlePlayResponseSuccess instanceof SubscriptionResponse && !empty($googlePlayResponseSuccess->getExpiryTimeMillis())) {

                                // Expire time from google play store
                                $expireTime = $googlePlayResponseSuccess->getExpiryTimeMillis(); // in miliseconds

                                //if (!empty($userSubscriptionRow) && !empty($userSubscriptionRow->user) && $userSubscriptionRow->user instanceof User && $userSubscriptionRow->user->is_subscribed_user == User::IS_SUBSCRIBE_USER_YES) {
                                if (!empty($userSubscriptionRow) && !empty($userSubscriptionRow->user) && $userSubscriptionRow->user instanceof User) {

                                    $timezone = (!empty($userSubscriptionRow->user->timezone) && !empty($userSubscriptionRow->user->timezone->time_zone)) ? $userSubscriptionRow->user->timezone->time_zone : "";

                                    if (!empty($timezone)) {
                                        date_default_timezone_set("$timezone");
                                    }

                                    // current time get in miliseconds for user
                                    $milliseconds = round(microtime(true) * 1000);

                                    if ($expireTime <= $milliseconds) {
                                        $userSubscriptionRow->user->is_subscribed_user = User::IS_SUBSCRIBE_USER_NO;
                                    } else {
                                        $userSubscriptionRow->user->is_subscribed_user = User::IS_SUBSCRIBE_USER_YES;
                                    }
                                    $userSubscriptionRow->user->save(false);
                                }
                            }
                        } else {
                            $userSubscriptionRow->user->is_subscribed_user = User::IS_SUBSCRIBE_USER_NO;
                            $userSubscriptionRow->user->save(false);
                        }
                        // Update User subscription data (in our DATABASE) end.
                    }
                    // Check for Android User subscription end.

                    // Check for Ios User subscription start.
                    if (strtolower($userSubscriptionRow->device_platform) == UserPurchasedSubscriptions::DEVICE_PLATFORM_IOS) {

                        $appStoreResponseSuccess = "";
                        $appStoreResponseFail = "";

                        //$validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION); // Or iTunesValidator::ENDPOINT_SANDBOX if sandbox testing
                        $validator = new iTunesValidator(iTunesValidator::ENDPOINT_SANDBOX); // Or iTunesValidator::ENDPOINT_SANDBOX if sandbox testing

                        //$receiptBase64Data = 'ewoJInNpZ25hdHVyZSIgPSAiQXBNVUJDODZBbHpOaWtWNVl0clpBTWlKUWJLOEVkZVhrNjNrV0JBWHpsQzhkWEd1anE0N1puSVlLb0ZFMW9OL0ZTOGNYbEZmcDlZWHQ5aU1CZEwyNTBsUlJtaU5HYnloaXRyeVlWQVFvcmkzMlc5YVIwVDhML2FZVkJkZlcrT3kvUXlQWkVtb05LeGhudDJXTlNVRG9VaFo4Wis0cFA3MHBlNWtVUWxiZElWaEFBQURWekNDQTFNd2dnSTdvQU1DQVFJQ0NHVVVrVTNaV0FTMU1BMEdDU3FHU0liM0RRRUJCUVVBTUg4eEN6QUpCZ05WQkFZVEFsVlRNUk13RVFZRFZRUUtEQXBCY0hCc1pTQkpibU11TVNZd0pBWURWUVFMREIxQmNIQnNaU0JEWlhKMGFXWnBZMkYwYVc5dUlFRjFkR2h2Y21sMGVURXpNREVHQTFVRUF3d3FRWEJ3YkdVZ2FWUjFibVZ6SUZOMGIzSmxJRU5sY25ScFptbGpZWFJwYjI0Z1FYVjBhRzl5YVhSNU1CNFhEVEE1TURZeE5USXlNRFUxTmxvWERURTBNRFl4TkRJeU1EVTFObG93WkRFak1DRUdBMVVFQXd3YVVIVnlZMmhoYzJWU1pXTmxhWEIwUTJWeWRHbG1hV05oZEdVeEd6QVpCZ05WQkFzTUVrRndjR3hsSUdsVWRXNWxjeUJUZEc5eVpURVRNQkVHQTFVRUNnd0tRWEJ3YkdVZ1NXNWpMakVMTUFrR0ExVUVCaE1DVlZNd2daOHdEUVlKS29aSWh2Y05BUUVCQlFBRGdZMEFNSUdKQW9HQkFNclJqRjJjdDRJclNkaVRDaGFJMGc4cHd2L2NtSHM4cC9Sd1YvcnQvOTFYS1ZoTmw0WElCaW1LalFRTmZnSHNEczZ5anUrK0RyS0pFN3VLc3BoTWRkS1lmRkU1ckdYc0FkQkVqQndSSXhleFRldngzSExFRkdBdDFtb0t4NTA5ZGh4dGlJZERnSnYyWWFWczQ5QjB1SnZOZHk2U01xTk5MSHNETHpEUzlvWkhBZ01CQUFHamNqQndNQXdHQTFVZEV3RUIvd1FDTUFBd0h3WURWUjBqQkJnd0ZvQVVOaDNvNHAyQzBnRVl0VEpyRHRkREM1RllRem93RGdZRFZSMFBBUUgvQkFRREFnZUFNQjBHQTFVZERnUVdCQlNwZzRQeUdVakZQaEpYQ0JUTXphTittVjhrOVRBUUJnb3Foa2lHOTJOa0JnVUJCQUlGQURBTkJna3Foa2lHOXcwQkFRVUZBQU9DQVFFQUVhU2JQanRtTjRDL0lCM1FFcEszMlJ4YWNDRFhkVlhBZVZSZVM1RmFaeGMrdDg4cFFQOTNCaUF4dmRXLzNlVFNNR1k1RmJlQVlMM2V0cVA1Z204d3JGb2pYMGlreVZSU3RRKy9BUTBLRWp0cUIwN2tMczlRVWU4Y3pSOFVHZmRNMUV1bVYvVWd2RGQ0TndOWXhMUU1nNFdUUWZna1FRVnk4R1had1ZIZ2JFL1VDNlk3MDUzcEdYQms1MU5QTTN3b3hoZDNnU1JMdlhqK2xvSHNTdGNURXFlOXBCRHBtRzUrc2s0dHcrR0szR01lRU41LytlMVFUOW5wL0tsMW5qK2FCdzdDMHhzeTBiRm5hQWQxY1NTNnhkb3J5L0NVdk02Z3RLc21uT09kcVRlc2JwMGJzOHNuNldxczBDOWRnY3hSSHVPTVoydG04bnBMVW03YXJnT1N6UT09IjsKCSJwdXJjaGFzZS1pbmZvIiA9ICJld29KSW05eWFXZHBibUZzTFhCMWNtTm9ZWE5sTFdSaGRHVXRjSE4wSWlBOUlDSXlNREV5TFRBMExUTXdJREE0T2pBMU9qVTFJRUZ0WlhKcFkyRXZURzl6WDBGdVoyVnNaWE1pT3dvSkltOXlhV2RwYm1Gc0xYUnlZVzV6WVdOMGFXOXVMV2xrSWlBOUlDSXhNREF3TURBd01EUTJNVGM0T0RFM0lqc0tDU0ppZG5KeklpQTlJQ0l5TURFeU1EUXlOeUk3Q2draWRISmhibk5oWTNScGIyNHRhV1FpSUQwZ0lqRXdNREF3TURBd05EWXhOemc0TVRjaU93b0pJbkYxWVc1MGFYUjVJaUE5SUNJeElqc0tDU0p2Y21sbmFXNWhiQzF3ZFhKamFHRnpaUzFrWVhSbExXMXpJaUE5SUNJeE16TTFOems0TXpVMU9EWTRJanNLQ1NKd2NtOWtkV04wTFdsa0lpQTlJQ0pqYjIwdWJXbHVaRzF2WW1Gd2NDNWtiM2R1Ykc5aFpDSTdDZ2tpYVhSbGJTMXBaQ0lnUFNBaU5USXhNVEk1T0RFeUlqc0tDU0ppYVdRaUlEMGdJbU52YlM1dGFXNWtiVzlpWVhCd0xrMXBibVJOYjJJaU93b0pJbkIxY21Ob1lYTmxMV1JoZEdVdGJYTWlJRDBnSWpFek16VTNPVGd6TlRVNE5qZ2lPd29KSW5CMWNtTm9ZWE5sTFdSaGRHVWlJRDBnSWpJd01USXRNRFF0TXpBZ01UVTZNRFU2TlRVZ1JYUmpMMGROVkNJN0Nna2ljSFZ5WTJoaGMyVXRaR0YwWlMxd2MzUWlJRDBnSWpJd01USXRNRFF0TXpBZ01EZzZNRFU2TlRVZ1FXMWxjbWxqWVM5TWIzTmZRVzVuWld4bGN5STdDZ2tpYjNKcFoybHVZV3d0Y0hWeVkyaGhjMlV0WkdGMFpTSWdQU0FpTWpBeE1pMHdOQzB6TUNBeE5Ub3dOVG8xTlNCRmRHTXZSMDFVSWpzS2ZRPT0iOwoJImVudmlyb25tZW50IiA9ICJTYW5kYm94IjsKCSJwb2QiID0gIjEwMCI7Cgkic2lnbmluZy1zdGF0dXMiID0gIjAiOwp9';
                        $receiptBase64Data = $userSubscriptionRow->subscription_response;

                        try {
                            //$response = $validator->setReceiptData($receiptBase64Data)->validate();
                            // $sharedSecret = '1234...'; // Generated in iTunes Connect's In-App Purchase menu
                            $response = $validator->setSharedSecret(Yii::$app->params['app_store_subscription_shared_secret_key'])->setReceiptData($receiptBase64Data)->validate(); // use setSharedSecret() if for recurring subscriptions
                            $appStoreResponseSuccess = $response;
                        } catch (\Exception $e) {
                            $appStoreResponseFail = $e->getMessage();
                            //echo 'got error = ' . $e->getMessage() . PHP_EOL;
                        }

                        // Update User subscription data (in our DATABASE) start.
                        if (!empty($appStoreResponseSuccess) && $appStoreResponseSuccess->isValid() && (empty($appStoreResponseFail) || $appStoreResponseFail == "")) {

                            if (($appStoreResponseSuccess instanceof SandboxResponse || $appStoreResponseSuccess instanceof ProductionResponse) && !empty($appStoreResponseSuccess->getReceipt())) {

                                $inApp = !empty($appStoreResponseSuccess->getReceipt()['in_app']) ? $appStoreResponseSuccess->getReceipt()['in_app'] : [];

                                // Expire time from google play store
                                $expireTime = (!empty($inApp) && !empty($inApp[count($inApp) - 1]) && !empty($inApp[count($inApp) - 1]['expires_date_ms'])) ? $inApp[count($inApp) - 1]['expires_date_ms'] : strtotime('-1 days'); // in miliseconds

                                //if (!empty($userSubscriptionRow) && !empty($userSubscriptionRow->user) && $userSubscriptionRow->user instanceof User && $userSubscriptionRow->user->is_subscribed_user == User::IS_SUBSCRIBE_USER_YES) {
                                if (!empty($userSubscriptionRow) && !empty($userSubscriptionRow->user) && $userSubscriptionRow->user instanceof User) {

                                    $timezone = (!empty($userSubscriptionRow->user->timezone) && !empty($userSubscriptionRow->user->timezone->time_zone)) ? $userSubscriptionRow->user->timezone->time_zone : "";

                                    if (!empty($timezone)) {
                                        date_default_timezone_set("$timezone");
                                    }

                                    // current time get in miliseconds for user
                                    $milliseconds = round(microtime(true) * 1000);

                                    if ($expireTime <= $milliseconds) {
                                        $userSubscriptionRow->user->is_subscribed_user = User::IS_SUBSCRIBE_USER_NO;
                                    } else {
                                        $userSubscriptionRow->user->is_subscribed_user = User::IS_SUBSCRIBE_USER_YES;
                                    }
                                    $userSubscriptionRow->user->save(false);
                                }
                            }
                        } else {
                            $userSubscriptionRow->user->is_subscribed_user = User::IS_SUBSCRIBE_USER_NO;
                            $userSubscriptionRow->user->save(false);
                        }
                        // Update User subscription data (in our DATABASE) end.
                    }
                    // Check for Ios User subscription end.
                }
            }

            echo "Cron Executed successfully.";
        }
    }

    /**
     * Send Notification For saved Search user For new Added Product.
     *
     *  It is execute at every minute.
     */
    public function actionSendSavedSearchNotificationForAddProduct()
    {
        $modelsProduct = DB_Product::find()->where(['is_saved_search_notification_sent' => DB_Product::IS_SAVED_SEARCH_NOTIFICATION_SENT_FALSE])->groupBy('id')->all();

        if (!empty($modelsProduct)) {

            foreach ($modelsProduct as $key => $model) {

                if (!empty($model) && $model instanceof DB_Product) {

                    // Send Push Notification and Email notification start

                    $brandName = (!empty($model->brand) && !empty($model->brand->name)) ? $model->brand->name : "";
                    $categoryName = (!empty($model->category) && !empty($model->category->name)) ? $model->category->name : "";

                    $query = SearchHistory::find();

                    $query->where('user_id!=' . $model->user_id);

//                    if (!empty($model->name)) {
//
//                        $query->andFilterWhere([
//                            'or',
//                            ['like', 'search_text', "%" . $model->name . "%", false],
//                            ['like', 'search_text', "%" . $brandName . "%", false],
//                            ['like', 'search_text', "%" . $categoryName . "%", false],
//                        ]);
//                    }

                    if (!empty($model->name)) {

                        $query->andFilterWhere([
                            'or',
                            ['like', 'search_text', $model->name . "%", false],
                            ['like', 'search_text', $brandName . "%", false],
                            ['like', 'search_text', $categoryName . "%", false],
                        ]);
                    }

                    $modelsSearch = $query->groupBy(['user_id', 'search_text'])->all();

                    if (!empty($modelsSearch)) {

                        foreach ($modelsSearch as $keys => $modelSearchRow) {

                            $getUsers = [];

                            if (!empty($modelSearchRow) && $modelSearchRow instanceof SearchHistory) {

                                $getUsers[] = $modelSearchRow->user;

                                if (!empty($getUsers)) {

                                    foreach ($getUsers as $keys1 => $userROW) {
                                        if ($userROW instanceof User && ($model->user_id != $userROW->id)) {

                                            if ($userROW->is_saved_searches_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                                $userDevice = $userROW->userDevice;

                                                if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                    // Insert into notification.
                                                    $notificationText = "Product is uploaded as per your saved search";
                                                    $modelNotification = new Notification();
                                                    $modelNotification->owner_id = $model->user_id;
                                                    $modelNotification->notification_receiver_id = $userROW->id;
                                                    $modelNotification->ref_id = $model->id;
                                                    $modelNotification->notification_text = $notificationText;
                                                    $modelNotification->action = "Add";
                                                    $modelNotification->ref_type = "products"; // For add new product
                                                    $modelNotification->product_id = $model->id; // For add new product
                                                    $modelNotification->save(false);

                                                    $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                    if ($userDevice->device_platform == 'android') {
                                                        $notificationToken = array($userDevice->notification_token);
                                                        $senderName = $model->user->first_name . " " . $model->user->last_name;
                                                        $notification = $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
                                                        // \Yii::info("\n------------android notification ----------------\n" . "userId:" . $userROW->id . "\n" . $notification, 'notifyUserBasedOnsaveSearch');
                                                    } else {
                                                        $note = Yii::$app->fcm->createNotification(Yii::$app->name, $notificationText);
                                                        $note->setBadge($badge);
                                                        $note->setSound('default');
                                                        $message = Yii::$app->fcm->createMessage();
                                                        $message->addRecipient(new \paragraph1\phpFCM\Recipient\Device($userDevice->notification_token));
                                                        $message->setNotification($note)
                                                            ->setData([
                                                                'id' => $modelNotification->ref_id,
                                                                'type' => $modelNotification->ref_type,
                                                                'message' => $notificationText,
                                                                'action' => (!empty($modelNotification) && !empty($modelNotification->action)) ? $modelNotification->action : "",
                                                            ]);
                                                        $response = Yii::$app->fcm->send($message);
                                                        $result = $response->getStatusCode();
                                                        // \Yii::info("\n------------ios notification ----------------\n" . "userId:" . $userROW->id . "\n" . $result, 'notifyUserBasedOnsaveSearch');
                                                    }
                                                }
                                            }

                                            if (!empty($userROW->email) && $userROW->is_saved_searches_email_notification_on == User::IS_NOTIFICATION_ON) {
                                                $message = "Product is uploaded as per your saved search.";
                                                if (!empty($userROW->email)) {
                                                    try {
                                                        Yii::$app->mailer->compose('api/addNewProductForSaveSearch', ['sender' => $model->user, 'receiver' => $userROW, 'message' => $message])
                                                            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                            ->setTo($userROW->email)
                                                            ->setSubject('New product added same as your search!')
                                                            ->send();
                                                    } catch (HttpException $e) {
                                                        echo "Error: " . $e->getMessage();

                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Send Push Notification and Email notification end

                    $model->is_saved_search_notification_sent = DB_Product::IS_SAVED_SEARCH_NOTIFICATION_SENT_TRUE;
                    $model->save(false);
                }
            }
        }

        //die("Notification sent. \n");

    }

}