<?php

namespace app\modules\api\v2\controllers;

use app\models\BlockUser;
use app\models\Notification;
use app\models\ProductReceipt;
use app\models\ProductTracking;
use app\models\SearchHistory;
use app\models\ShippingPrice;
use app\modules\api\v2\models\User;
use Yii;
use app\models\{
    Product,
    ProductImage,
    ProductStatus,
    UserAddress
};

use yii\web\{
    NotFoundHttpException,
    UploadedFile
};
use yii\filters\auth\{
    CompositeAuth,
    HttpBasicAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\base\BaseObject;
use yii\filters\Cors;
use yii\imagine\Image;
use yii\rest\ActiveController;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Product';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\ProductSearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'index-list' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete-product-receipt' => ['POST', 'DELETE'],
            'delete' => ['POST', 'DELETE'],
            'add-product-receipt' => ['POST', 'OPTIONS'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $auth = $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'only' => ['index-list', 'view', 'view-product', 'create', 'update', 'delete', 'add-product-receipt', 'delete-product-receipt'],//'index'
            'authMethods' => [
                HttpBasicAuth::class,
                HttpBearerAuth::class,
                QueryParamAuth::class,
            ]
        ];

        unset($behaviors['authenticator']);
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;

        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Access-Control-Expose-Headers' => ['X-Pagination-Per-Page', 'X-Pagination-Current-Page', 'X-Pagination-Total-Count ', 'X-Pagination-Page-Count'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        //unset($actions['index-list']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['delete-product-receipt']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        return $model->search($requestParams);
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndexList()
    {

        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        return $model->search($requestParams, Yii::$app->user->identity->id);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = Product::findOne($id);

        if (!$model instanceof Product) {
            throw new NotFoundHttpException('Product doesn\'t exist.');
        }
        // $model['productTracking'] = [];
        // if ($model->type == Product::PRODUCT_TYPE_USED) {
        //     if (!empty($model->productTracking)) {
        //         $modelProductTracking = ProductTracking::find()->where(['parent_id' => $model->product_tracking_id])->orderBy('created_at')->all();
        //         $model['productTracking'] = array_merge($model->productTracking->toArray(), $modelProductTracking->toArray());
        //     }
        // }
        return $model;
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewProduct($id)
    {
        $model = Product::findOne($id);
        if (!$model instanceof Product) {
            throw new NotFoundHttpException('Product doesn\'t exist.');
        }
        return $model;
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();

        $postData = \Yii::$app->request->post();
        $productData['Product'] = $postData;

        $model->gender = Product::GENDER_FOR_FEMALE;
        $model->status_id = ProductStatus::STATUS_PENDING_APPROVAL;

        $images = UploadedFile::getInstancesByName('images');
        $ReceiptImages = UploadedFile::getInstancesByName('receipt');

        $model->images = $images;
        $model->receipt = $ReceiptImages;
        $productData['Product']['user_id'] = Yii::$app->user->identity->id;
        if ($model->load($productData) && $model->validate()) {

            $model->type = (!empty($productData['Product']['type'])) ? $productData['Product']['type'] : Product::PRODUCT_TYPE_NEW;

            $model->shipping_country_id = (!empty($productData['Product']['shipping_country_id'])) ? $productData['Product']['shipping_country_id'] : "";
            $model->shipping_country_price = (!empty($productData['Product']['shipping_country_price'])) ? $productData['Product']['shipping_country_price'] : "";

            if (!empty($model->option_size)) {
                $model->option_size = strtolower($model->option_size);
            }
            if ($model->save()) {

                /* Product Image */
                if (!empty($images)) {
                    foreach ($images as $img) {
                        $modelImage = new ProductImage();

                        $uploadDirPath = Yii::getAlias('@productImageRelativePath');
                        $uploadThumbDirPath = Yii::getAlias('@productImageThumbRelativePath');
                        $thumbImagePath = '';

                        // Create product upload directory if not exist
                        if (!is_dir($uploadDirPath)) {
                            mkdir($uploadDirPath, 0777);
                        }

                        // Create product thumb upload directory if not exist
                        if (!is_dir($uploadThumbDirPath)) {
                            mkdir($uploadThumbDirPath, 0777);
                        }

                        $fileName = time() . rand(99999, 88888) . '.' . $img->extension;
                        // Upload product picture
                        $img->saveAs($uploadDirPath . '/' . $fileName);
                        // Create thumb of product picture
                        $actualImagePath = $uploadDirPath . '/' . $fileName;
                        $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                        Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert product picture name into database

                        $modelImage->product_id = $model->id;
                        $modelImage->name = $fileName;
                        $modelImage->save(false);
                    }
                }

                /* Receipt Image */
                if (!empty($ReceiptImages)) {
                    foreach ($ReceiptImages as $imgRow) {
                        $modelImageReceipt = new ProductReceipt();

                        $uploadDirPathReceipt = Yii::getAlias('@productReceiptImageRelativePath');
                        $uploadThumbDirPathReceipt = Yii::getAlias('@productReceiptImageThumbRelativePath');

                        // Create product upload directory if not exist
                        if (!is_dir($uploadDirPathReceipt)) {
                            mkdir($uploadDirPathReceipt, 0777);
                        }

                        // Create product thumb upload directory if not exist
                        if (!is_dir($uploadThumbDirPathReceipt)) {
                            mkdir($uploadThumbDirPathReceipt, 0777);
                        }

                        $fileNameReceipt = time() . rand(99999, 88888) . '.' . $imgRow->extension;
                        // Upload product picture
                        $imgRow->saveAs($uploadDirPathReceipt . '/' . $fileNameReceipt);
                        // Create thumb of product picture
                        $actualImagePathReceipt = $uploadDirPathReceipt . '/' . $fileNameReceipt;
                        $thumbImagePathReceipt = $uploadThumbDirPathReceipt . '/' . $fileNameReceipt;

                        chmod($actualImagePathReceipt, 0777);

                        Image::thumbnail($actualImagePathReceipt, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePathReceipt, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        chmod($thumbImagePathReceipt, 0777);

                        // Insert product picture name into database
                        $modelImageReceipt->product_id = $model->id;
                        $modelImageReceipt->file = $fileNameReceipt;
                        $modelImageReceipt->save(false);
                    }
                }

                $modelAddress = "";
                if (!empty($productData['Product']['is_profile_address']) && ($productData['Product']['is_profile_address'] == 1 || $productData['Product']['is_profile_address'] == "1")) {
                    $modelAddress = UserAddress::find()->where(['user_id' => Yii::$app->user->identity->id, 'is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES])->one();
                }
                if (empty($modelAddress)) {
                    $modelAddress = new UserAddress();
                    $addressData['UserAddress'] = $postData;
                    $modelAddress->user_id = Yii::$app->user->identity->id;

                    $modelAddress->type = UserAddress::TYPE_SHOP;
                    if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                        $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->zip_code;
                        $modelAddress->type = UserAddress::TYPE_SHOP;
                        if ($modelAddress->save(false)) {
                            $model->address_id = $modelAddress->id;
                            $model->save(false);
                        }
                    }
                } else {
                    $model->address_id = $modelAddress->id;
                    $model->save(false);
                }


                //  shipping cost
                if (!empty($model->shipping_country_id) && !empty($model->shipping_country_price)) {
                    $shippingCosts = explode(",", $model->shipping_country_price);
                    $shippingCountries = explode(",", $model->shipping_country_id);
                    foreach ($shippingCountries as $key => $shippingCountry) {
                        $shippingPrice = new ShippingPrice();
                        $shippingPrice->product_id = $model->id;
                        $shippingPrice->shipping_cost_id = $shippingCountry;
                        $shippingPrice->price = $shippingCosts[$key];
                        $shippingPrice->save(false);
                    }
                }

                // Send Push Notification start

                $brandName = (!empty($model->brand) && !empty($model->brand->name)) ? $model->brand->name : "";
                $categoryName = (!empty($model->category) && !empty($model->category->name)) ? $model->category->name : "";

                $query = SearchHistory::find();
                $query->where('user_id!=' . Yii::$app->user->identity->id);
                if (!empty($model->name)) {
                    $query->andFilterWhere([
                        'or',
                        ['like', 'search_text', $model->name],
                        ['like', 'search_text', $brandName],
                        ['like', 'search_text', $categoryName],
                    ]);
                }
                $modelsSearch = $query->all();

                if (!empty($modelsSearch)) {
                    foreach ($modelsSearch as $key => $modelSearchRow) {
                        if (!empty($modelSearchRow) && $modelSearchRow instanceof SearchHistory) {

                            $getUsers[] = $modelSearchRow->user;

                            if (!empty($getUsers)) {
                                foreach ($getUsers as $keys => $userROW) {
                                    if ($userROW instanceof User && ($model->user_id != $userROW->id)) {
                                        if ($userROW->is_saved_searches_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                            $userDevice = $userROW->userDevice;

                                            // Insert into notification.
                                            $notificationText = "Product is uploaded as per your saved search";
                                            $modelNotification = new Notification();
                                            $modelNotification->owner_id = $model->user_id;
                                            $modelNotification->notification_receiver_id = $userROW->id;
                                            $modelNotification->ref_id = $model->id;
                                            $modelNotification->notification_text = $notificationText;
                                            $modelNotification->action = "Add";
                                            $modelNotification->ref_type = "products"; // For seller rate review
                                            //$modelNotification->created_at = time();
                                            $modelNotification->save(false);

                                            $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                            if ($userDevice->device_platform == 'android') {
                                                $notificationToken = array($userDevice->notification_token);
                                                $senderName = $model->user->first_name . " " . $model->user->last_name;
                                                $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName);
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
                                                ]);
                                                $response = Yii::$app->fcm->send($message);
                                            }
                                        }

                                        if ($userROW->is_saved_searches_email_notification_on == User::IS_NOTIFICATION_ON) {
                                            $message = "Product is uploaded as per your saved search";
//                                            if (!empty($userROW->email)) {
//                                                Yii::$app->mailer->compose('api/addNewProductForSaveSearch', ['sender' => $model->user, 'receiver' => $userROW, 'message' => $message])
//                                                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
//                                                    ->setTo($userROW->email)
//                                                    ->setSubject('New product added same as your search!')
//                                                    ->send();
//                                            }


                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Send Push Notification end
            }
        }

        return $model;
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Product::findOne($id);
        if (!$model instanceof Product) {
            throw new NotFoundHttpException('Product doesn\'t exist.');
        }
        $postData = Yii::$app->request->post();
        $productData['Product'] = $postData;

        $model->gender = Product::GENDER_FOR_FEMALE;

        if ($model->load($productData) && $model->validate()) {

            $model->type = (!empty($productData['Product']['type'])) ? $productData['Product']['type'] : Product::PRODUCT_TYPE_NEW;


            if (!empty($model->option_size)) {
                $model->option_size = strtolower($model->option_size);
            }

            if (!empty($productData['Product']['shipping_country_id'])) {
                $model->shipping_country_id = $productData['Product']['shipping_country_id'];
            }
            if (!empty($productData['Product']['shipping_country_price'])) {
                $model->shipping_country_price = $productData['Product']['shipping_country_price'];
            }

            if ($model->save(false)) {

                $modelAddress = $model->address;
                if (empty($modelAddress) && empty($productData['Product']['is_profile_address'])) {
                    $modelAddress = new UserAddress();
                    $modelAddress->user_id = Yii::$app->user->identity->id;
                    $addressData['UserAddress'] = $postData;
                    $modelAddress->type = UserAddress::TYPE_SHOP;
                    if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                        $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->zip_code;
                        if ($modelAddress->save(false)) {
                            $model->address_id = $modelAddress->id;
                            $model->save(false);
                        }
                    }
                } elseif (empty($modelAddress) && !empty($productData['Product']['is_profile_address']) && ($productData['Product']['is_profile_address'] == 1 || $productData['Product']['is_profile_address'] == "1")) {
                    $modelAddress = UserAddress::find()->where(['user_id' => Yii::$app->user->identity->id, 'is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES])->one();
                    if (!empty($modelAddress)) {
                        $model->address_id = $modelAddress->id;
                        $model->save(false);
                    } else {
                        $modelAddress = new UserAddress();
                        $modelAddress->user_id = Yii::$app->user->identity->id;
                        $addressData['UserAddress'] = $postData;
                        $modelAddress->type = UserAddress::TYPE_SHOP;
                        if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                            $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->zip_code;
                            if ($modelAddress->save(false)) {
                                $model->address_id = $modelAddress->id;
                                $model->save(false);
                            }
                        }
                    }

                } elseif (!empty($modelAddress) && !empty($productData['Product']['is_profile_address']) && ($productData['Product']['is_profile_address'] == 1 || $productData['Product']['is_profile_address'] == "1")) {
                    $modelAddress = UserAddress::find()->where(['user_id' => Yii::$app->user->identity->id, 'is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES])->one();
                    $model->address_id = $modelAddress->id;
                    $model->save(false);
                } elseif (!empty($modelAddress) && empty($productData['Product']['is_profile_address'])) {
                    $addressData['UserAddress'] = $postData;
                    $modelAddress->type = UserAddress::TYPE_SHOP;
                    if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                        $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->zip_code;
                        if ($modelAddress->save(false)) {
                            $model->address_id = $modelAddress->id;
                            $model->save(false);
                        }
                    }
                }

                //  shipping cost
                if (!empty($model->shipping_country_id) && !empty($model->shipping_country_price)) {
                    $shippingCosts = explode(",", $model->shipping_country_price);
                    $shippingCountries = explode(",", $model->shipping_country_id);

                    $modelsShippingPrice = ShippingPrice::find()->where(['in', 'shipping_cost_id', $shippingCountries])->andWhere(['product_id' => $model->id])->all();
                    if (!empty($modelsShippingPrice)) {
                        foreach ($modelsShippingPrice as $keys => $modelShippingPrice) {
                            $modelShippingPrice->delete();
                        }
                    }

                    foreach ($shippingCountries as $key => $shippingCountry) {
                        $shippingPrice = new ShippingPrice();
                        $shippingPrice->product_id = $model->id;
                        $shippingPrice->shipping_cost_id = $shippingCountry;
                        $shippingPrice->price = $shippingCosts[$key];
                        $shippingPrice->save(false);
                    }
                }
            }
        }
        return $model;
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = Product::findOne($id);
        if (!$model instanceof Product) {
            throw new NotFoundHttpException('Product doesn\'t exist.');
        }
        if (!empty($model) && !empty($model->productImages)) {
            foreach ($model->productImages as $key => $imageRow) {
                if ($imageRow instanceof ProductImage) {
                    if (!empty($imageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $imageRow->name)) {
                        unlink(Yii::getAlias('@productImageRelativePath') . "/" . $imageRow->name);
                    }

                    if (!empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $imageRow->name)) {
                        unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $imageRow->name);
                    }
                    $imageRow->delete();
                }
            }
        }

        $model->delete();
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @return ProductReceipt|array
     */
    public function actionAddProductReceipt()
    {
        $model = new ProductReceipt();
        $postData = Yii::$app->request->post();
        $productImage['ProductReceipt'] = $postData;
        $images = UploadedFile::getInstancesByName('file');
        $model->file = $images;
        $arrayImage = [];
        if ($model->load($productImage) && $model->validate()) {
            if (!empty($images)) {
//                $deleteImages = ProductReceipt::find()->where(['product_id' => $productImage['ProductReceipt']['product_id']]);
//                $modelsOldImg = $deleteImages->all();
//                if (!empty($modelsOldImg)) {
//                    foreach ($modelsOldImg as $key => $modelOldImgRow) {
//                        if (!empty($modelOldImgRow) && $modelOldImgRow instanceof ProductReceipt) {
//
//                            if (!empty($modelOldImgRow->file) && file_exists(Yii::getAlias('@productReceiptImageRelativePath') . "/" . $modelOldImgRow->file)) {
//                                unlink(Yii::getAlias('@productReceiptImageRelativePath') . "/" . $modelOldImgRow->file);
//                            }
//
//                            if (!empty($modelOldImgRow->file) && file_exists(Yii::getAlias('@productReceiptImageThumbRelativePath') . "/" . $modelOldImgRow->file)) {
//                                unlink(Yii::getAlias('@productReceiptImageThumbRelativePath') . "/" . $modelOldImgRow->file);
//                            }
//                        }
//                    }
//                    ProductReceipt::deleteAll(['product_id' => $productImage['ProductReceipt']['product_id']]);
//                }

                foreach ($images as $img) {
                    $modelImage = new ProductReceipt();
                    $uploadDirPath = Yii::getAlias('@productReceiptImageRelativePath');
                    $uploadThumbDirPath = Yii::getAlias('@productReceiptImageThumbRelativePath');
                    $thumbImagePath = '';

                    // Create product upload directory if not exist
                    if (!is_dir($uploadDirPath)) {
                        mkdir($uploadDirPath, 777);
                    }

                    // Create product thumb upload directory if not exist
                    if (!is_dir($uploadThumbDirPath)) {
                        mkdir($uploadThumbDirPath, 777);
                    }

                    $fileName = time() . rand(99999, 88888) . '.' . $img->extension;
                    // Upload product picture
                    $img->saveAs($uploadDirPath . '/' . $fileName);
                    // Create thumb of product picture
                    $actualImagePath = $uploadDirPath . '/' . $fileName;
                    $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                    Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                    // Insert product picture name into database

                    $modelImage->product_id = $productImage['ProductReceipt']['product_id'];
                    $modelImage->file = $fileName;
                    $modelImage->save(false);
                    $arrayImage[] = $modelImage;
                }
            }

            if (!empty($arrayImage)) {
                $thumbImagePath = Yii::getAlias('@productReceiptImageThumbAbsolutePath');
                $thumbImagePathRelative = Yii::getAlias('@productReceiptImageThumbRelativePath');
                foreach ($arrayImage as $images) {
                    if (!empty($images->file) && file_exists($thumbImagePathRelative . "/" . $images->file)) {
                        $images->file = Yii::$app->request->getHostInfo() . $thumbImagePath . '/' . $images->file;
                    } else {
                        $images->file = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                }
                $model = $arrayImage;
            }
        }
        return $model;
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteProductReceipt($id)
    {
        $model = ProductReceipt::findOne($id);

        if (empty($model) && !$model instanceof ProductReceipt) {
            throw new NotFoundHttpException('Product receipt doesn\'t exist.');
        }

        if (!empty($model->file) && file_exists(Yii::getAlias('@productReceiptImageRelativePath') . "/" . $model->file)) {
            unlink(Yii::getAlias('@productReceiptImageRelativePath') . "/" . $model->file);
        }

        if (!empty($model->file) && file_exists(Yii::getAlias('@productReceiptImageThumbRelativePath') . "/" . $model->file)) {
            unlink(Yii::getAlias('@productReceiptImageThumbRelativePath') . "/" . $model->file);
        }

        $model->delete();
    }

}
