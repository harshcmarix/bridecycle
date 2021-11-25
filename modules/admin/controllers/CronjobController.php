<?php

namespace app\modules\admin\controllers;

use app\models\Notification;
use app\models\SearchHistory;
use app\modules\api\v2\models\User;
use yii\web\Controller;
use Yii;
use app\models\Product as DB_Product;
use yii\web\HttpException;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;

class CronjobController extends Controller
{
    public function actionCheckPlayStoreSubscriptionStatusBackup()
    {
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("SELECT *
        FROM `user_purchased_subscriptions` 
        WHERE `id` IN (SELECT MAX(`id`) AS `id`
             FROM `user_purchased_subscriptions` 
             WHERE `device_platform` = 'android'
             GROUP BY `user_id`) 
        ORDER BY `created_at` DESC");

        $userSubscriptions = $command->queryAll();
        //p($userSubscriptions);


        $request = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/com.bridecycle/purchases/subscriptions/com.bridecycle.three.month/tokens/gohhmiemjlhcgoflapbnjlfb.AO-J1OxLWiA0c8TPpD4wVWjYj6WT84mGTHp4t_DiEnfXWdz07OGvaSQB-N7SpmOYNhOcib44VzlHdZAeOOfDPq3u5J2ydLY23w";


//        $request = "https://iam.googleapis.com/v1/projects/pc-api-5945952200218555652-603/serviceAccounts";
        $result = file_get_contents($request);
        $res = json_decode($result, true);
        p($res);


        //$path = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsRelativePath') . '/google-app-credentials.json';
        $path = Yii::getAlias('@uploadsRelativePath') . '/google-app-credentials.json';

        //putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $path);
        putenv(sprintf("GOOGLE_APPLICATION_CREDENTIALS=%s", $path));


        try {

            //ini_set('max_execution_time', 3000);
            $client = new \Google_Client();
            if ($credentials_file = $path) {
                // set the location manually
                $client->setAuthConfig($credentials_file);
            } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
                // use the application default credentials
                $client->useApplicationDefaultCredentials();
            } else {
                $rv = "missingServiceAccountDetailsWarning()";
                return [$rv];
            }

            $client->addScope("https://www.googleapis.com/auth/androidpublisher");
            $serviceAndroidPublisher = new \Google_Service_AndroidPublisher($client);
            $servicePurchaseSubscription = $serviceAndroidPublisher->purchases_subscriptions;
            //p($servicePurchaseSubscription);
            $rv = $servicePurchaseSubscription->get(
                "com.bridecycle",
                "com.bridecycle.three.month",
                "gohhmiemjlhcgoflapbnjlfb.AO-J1OxLWiA0c8TPpD4wVWjYj6WT84mGTHp4t_DiEnfXWdz07OGvaSQB-N7SpmOYNhOcib44VzlHdZAeOOfDPq3u5J2ydLY23w"
            );
            p($rv);

        } catch (\Exception $e) {
            //return $e->getCode() . " " . $e->getMessage();
            return $e->getMessage();
        }


//        $client = ClientFactory::create([ClientFactory::SCOPE_ANDROID_PUBLISHER]);
//        $client->setAuthConfig($path);
//        $product = new Product($client, 'com.bridecycle', 'com.bridecycle.three.month', 'gohhmiemjlhcgoflapbnjlfb.AO-J1OxLWiA0c8TPpD4wVWjYj6WT84mGTHp4t_DiEnfXWdz07OGvaSQB-N7SpmOYNhOcib44VzlHdZAeOOfDPq3u5J2ydLY23w');
//        $product->acknowledge();


//        $client = ClientFactory::create([ClientFactory::SCOPE_ANDROID_PUBLISHER]);
//        $subscription = new Subscription($client, 'com.bridecycle', 'com.bridecycle.three.month', 'gohhmiemjlhcgoflapbnjlfb.AO-J1OxLWiA0c8TPpD4wVWjYj6WT84mGTHp4t_DiEnfXWdz07OGvaSQB-N7SpmOYNhOcib44VzlHdZAeOOfDPq3u5J2ydLY23w');
//        $subscription->acknowledge();
//        $resource = $subscription->get(); // Imdhemy\GooglePlay\Subscriptions\SubscriptionPurchase
//        p($resource);


    }


    public function actionCheckPlayStoreSubscriptionStatus()
    {
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("SELECT *
        FROM `user_purchased_subscriptions` 
        WHERE `id` IN (SELECT MAX(`id`) AS `id`
             FROM `user_purchased_subscriptions` 
             WHERE `device_platform` = 'android'
             GROUP BY `user_id`) 
        ORDER BY `created_at` DESC");

        $userSubscriptions = $command->queryAll();
        //p($userSubscriptions);


        $path = Yii::getAlias('@uploadsRelativePath') . '/google-app-credentials.json';

        $packageName = "com.bridecycle";
        $product_id = "com.bridecycle.six.month";
        $purchaseToken = "cmpdhgoifnklbhfeefbekfnn.AO-J1OztDGK2scDQxYgZIzM1wcIS38QnAhxyun7iLX-qZgpOlyQ-G_OmpgSgiaN_g1vb_HL2pUG0XGid6NQCRjgdMBiQbjadBg";


        $googleClient = new \Google_Client();
        $googleClient->setScopes([\Google\Service\AndroidPublisher::ANDROIDPUBLISHER]);
        $googleClient->setApplicationName('bridecycle');
        $googleClient->setAuthConfig($path);

        $googleAndroidPublisher = new \Google\Service\AndroidPublisher($googleClient);
        $validator = new \ReceiptValidator\GooglePlay\Validator($googleAndroidPublisher);

        try {
            $response = $validator->setPackageName($packageName)
                ->setProductId($product_id)
                ->setPurchaseToken($purchaseToken)
                ->validateSubscription();
            p($response);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            // example message: Error calling GET ....: (404) Product not found for this application.
        }

        // success


    }

    /**
     * Send Notification For saved Search user For new Added Product.
     *
     *  It is execute every minute.
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
                                                        \Yii::info("\n------------android notification ----------------\n" . "userId:" . $userROW->id . "\n" . $notification, 'notifyUserBasedOnsaveSearch');
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
                                                        \Yii::info("\n------------ios notification ----------------\n" . "userId:" . $userROW->id . "\n" . $result, 'notifyUserBasedOnsaveSearch');
                                                    }
                                                }
                                            }

                                            if (!empty($userROW->email) && $userROW->is_saved_searches_email_notification_on == User::IS_NOTIFICATION_ON) {
                                                $message = "Product is uploaded as per your saved search";
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

        die("Notification sent. \n");

    }

}