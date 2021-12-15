<?php

namespace app\modules\admin\controllers;

use app\models\Notification;
use app\models\SearchHistory;
use app\models\UserPurchasedSubscriptions;
use app\modules\api\v2\models\User;
use ReceiptValidator\GooglePlay\SubscriptionResponse;
use yii\web\Controller;
use Yii;
use app\models\Product as DB_Product;
use yii\web\HttpException;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;

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

                                // Set Purchase token.
                                if (!empty($subscriptionRespose) && !empty($subscriptionRespose->purchaseToken)) {
                                    $purchaseToken = $subscriptionRespose->purchaseToken;
                                }

                                // Set product ID.
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

                                echo "Error: " . $e->getMessage();

                               // \Yii::info("\n------------Fail Subscription ----------------\n" . "userId:" . $userSubscriptionRow->user_id . "\n" . $e->getMessage(), 'notifyUserBasedOnAndroidGooglePlaySubscription');
                            }
                        }
                        // End Android subscription check

                        // Update User subscription data (in our DATABASE) start.
                        if (!empty($googlePlayResponseSuccess) && (empty($googlePlayResponseFail) || $googlePlayResponseFail == "")) {
                            if ($googlePlayResponseSuccess instanceof SubscriptionResponse && !empty($googlePlayResponseSuccess->getExpiryTimeMillis())) {

                                // Expire time from google play store
                                $expireTime = $googlePlayResponseSuccess->getExpiryTimeMillis(); // in miliseconds

                                if (!empty($userSubscriptionRow) && !empty($userSubscriptionRow->user) && $userSubscriptionRow->user instanceof User && $userSubscriptionRow->user->is_subscribed_user == User::IS_SUBSCRIBE_USER_YES) {

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
                    if(strtolower($userSubscriptionRow->device_platform) == UserPurchasedSubscriptions::DEVICE_PLATFORM_IOS){

                    }
                    // Check for Ios User subscription end.

                }

            }

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

                    if (!empty($model->name)) {

                        $query->andFilterWhere([
                            'or',
                            ['like', 'search_text', "%" . $model->name . "%", false],
                            ['like', 'search_text', "%" . $brandName . "%", false],
                            ['like', 'search_text', "%" . $categoryName . "%", false],
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