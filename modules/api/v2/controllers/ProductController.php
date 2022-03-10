<?php

namespace app\modules\api\v2\controllers;

use app\models\Brand;
use app\models\CartItem;
use app\models\Color;
use app\models\MakeOffer;
use app\models\Product;
use app\models\ProductImage;
use app\models\ProductReceipt;
use app\models\ProductSizes;
use app\models\ProductStatus;
use app\models\ShippingPrice;
use app\models\Trial;
use app\models\UserAddress;
use app\modules\admin\models\search\SizesSearch;
use app\modules\api\v2\models\User;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\imagine\Image;
use yii\rest\ActiveController;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

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
            'size-list' => ['GET', 'HEAD', 'OPTIONS'],
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
            'only' => ['index-list', 'view', 'view-product', 'create', 'update', 'delete', 'add-product-receipt', 'delete-product-receipt'],//'index', 'size-list'
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
            throw new NotFoundHttpException(getValidationErrorMsg('product_not_exist', Yii::$app->language));
        }
        $model->price = $model->getReferPrice();
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
            throw new NotFoundHttpException(getValidationErrorMsg('product_not_exist', Yii::$app->language));
        }

        $model->price = $model->getReferPrice();
        return $model;
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        //ini_set('max_execution_time', 300);
        $model = new Product();
        $postData = \Yii::$app->request->post();
        $productData['Product'] = $postData;

        // check for user is subscriber or not
        if (!empty(Yii::$app->user->identity) && (!empty(Yii::$app->user->identity->is_shop_owner) || Yii::$app->user->identity->is_shop_owner == User::SHOP_OWNER_YES) && (Yii::$app->user->identity->is_subscribed_user == '0' || Yii::$app->user->identity->is_subscribed_user == "" || empty(Yii::$app->user->identity->is_subscribed_user))) {
            throw new HttpException(403, getValidationErrorMsg('subscription_required_validation', Yii::$app->language));
        }

        $model->gender = Product::GENDER_FOR_FEMALE;
        $model->status_id = ProductStatus::STATUS_PENDING_APPROVAL;

        $images = UploadedFile::getInstancesByName('images');

        $ReceiptImages = UploadedFile::getInstancesByName('receipt');

        $model->images = $images;
        $model->receipt = $ReceiptImages;

        $productData['Product']['user_id'] = Yii::$app->user->identity->id;

        $productData['Product']['images'] = $images;
        $productData['Product']['receipt'] = $ReceiptImages;

        if ($model->load($productData) && $model->validate()) {

            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            } else {
                $model->option_show_only = '0';
            }

            $model->type = (!empty($productData['Product']['type'])) ? $productData['Product']['type'] : Product::PRODUCT_TYPE_NEW;

            $model->shipping_country_id = (!empty($productData['Product']['shipping_country_id'])) ? $productData['Product']['shipping_country_id'] : "";
            $model->shipping_country_price = (!empty($productData['Product']['shipping_country_price'])) ? $productData['Product']['shipping_country_price'] : "";

            $sizeIds = (!empty($productData['Product']['option_size'])) ? $productData['Product']['option_size'] : "";
            $model->option_size = (!empty($sizeIds)) ? $sizeIds : "";

            if ($model->save(false)) {

                // Product Size DB entry start.
                if (!empty($productData['Product']['option_size'])) {
                    $productSizes = explode(",", $productData['Product']['option_size']);
                    if (!empty($productSizes)) {
                        foreach ($productSizes as $key => $productSizesRow) {
                            if (!empty($productSizesRow)) {
                                $modelProductSize = new ProductSizes();
                                $modelProductSize->product_id = $model->id;
                                $modelProductSize->size_id = $productSizesRow;
                                $modelProductSize->save(false);
                            }
                        }
                    }
                }
                // Product Size DB entry end.

                // Status of product color/brand status approved START.
                $isPendingApprovalColor = 0;
                $arrColors = explode(",", $model->option_color);
                if (!empty($arrColors)) {
                    $modelColors = Color::find()->where(['in', 'id', $arrColors])->all();
                    if (!empty($modelColors)) {
                        foreach ($modelColors as $c => $modelColorsRow) {
                            if (!empty($modelColorsRow) && $modelColorsRow instanceof Color && in_array($modelColorsRow->status, [Color::STATUS_PENDING_APPROVAL, Color::STATUS_DECLINE])) {
                                $isPendingApprovalColor += 1;
                            }
                        }
                    }
                }

                $isPendingApprovalBrand = 0;
                $modelBrand = Brand::findOne($model->brand_id);
                if (!empty($modelBrand) && $modelBrand instanceof Brand && in_array($modelBrand->status, [Brand::STATUS_PENDING_APPROVAL, Brand::STATUS_DECLINE])) {
                    $isPendingApprovalBrand += 1;
                }

                if ($isPendingApprovalColor == 0 && $isPendingApprovalBrand == 0) {
                    $model->status_id = ProductStatus::STATUS_APPROVED;
                    $model->save(false);
                }
                // Status of product color/brand status approved END.

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
                    $addressData['UserAddress'] = $postData;

                    $addressModel = UserAddress::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['street' => $addressData['UserAddress']['street'], 'city' => $addressData['UserAddress']['city'], 'state' => $addressData['UserAddress']['state'], 'country' => $addressData['UserAddress']['country'], 'zip_code' => $addressData['UserAddress']['zip_code']])->one();
                    if (empty($addressModel)) {
                        $modelAddress = new UserAddress();
                    } else {
                        $modelAddress = UserAddress::find()->where(['id' => $addressModel->id])->one();
                    }
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
                    if (!empty($shippingCountries) && !empty($shippingCosts)) {
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
            throw new NotFoundHttpException(getValidationErrorMsg('product_not_exist', Yii::$app->language));
        }
        $postData = Yii::$app->request->post();
        $productData['Product'] = $postData;

        $model->gender = Product::GENDER_FOR_FEMALE;

        $productSizes = $model->productSizes;

        if ($model->load($productData) && $model->validate()) {
            $model->user_id = Yii::$app->user->identity->id;
            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            } else {
                $model->option_show_only = '0';
            }

            if (!empty($postData['is_top_selling'])) {
                $model->is_top_selling = $postData['is_top_selling'];
            }

            if (!empty($postData['option_color'])) {
                $model->option_color = $postData['option_color'];
            }

            if (!empty($postData['other_info'])) {
                $model->other_info = $postData['other_info'];
            }

            if (!empty($postData['description'])) {
                $model->description = $postData['description'];
            }

            if (!empty($postData['available_quantity'])) {
                $model->available_quantity = $postData['available_quantity'];
                if ($postData['available_quantity'] == '1' && !empty($productData['Product']) && !empty($productData['Product']['type']) && $productData['Product']['type'] == 'u') {
                    $model->status_id = ProductStatus::STATUS_APPROVED;
                }
            }

            if (!empty($postData['brand_id'])) {
                $model->brand_id = $postData['brand_id'];
            }

            if (!empty($postData['is_cleaned'])) {
                $model->is_cleaned = $postData['is_cleaned'];
            }

            if (!empty($postData['height'])) {
                $model->height = $postData['height'];
            }

            if (!empty($productData['Product']['type']) && in_array($productData['Product']['type'], ['n', 'u'])) {
                $model->type = $productData['Product']['type'];
            }

            $sizeIds = (!empty($productData['Product']['option_size'])) ? $productData['Product']['option_size'] : '';
            $model->option_size = (!empty($sizeIds)) ? $sizeIds : "";

            if (!empty($productData['Product']['shipping_country_id'])) {
                $model->shipping_country_id = $productData['Product']['shipping_country_id'];
            }
            if (!empty($productData['Product']['shipping_country_price'])) {
                $model->shipping_country_price = $productData['Product']['shipping_country_price'];
            }

            if ($model->save(false)) {

                // Updated sizes start
                if (!empty($productData['Product']['option_size'])) {
                    $arrSizes = explode(",", $productData['Product']['option_size']);
                    $oldSizeIds = array_column($productSizes, 'size_id');
                    $newSizeIds = array_values($arrSizes);
                    $deleteDiffIds = array_diff(array_values($oldSizeIds), array_values($newSizeIds));
                    $newDiffIds = array_diff(array_values($newSizeIds), array_values($oldSizeIds));

                    if (!empty($deleteDiffIds)) {
                        $modelOldEnteries = ProductSizes::find()->where(['in', 'size_id', $deleteDiffIds])->andWhere(['product_id' => $model->id])->all();
                        if (!empty($modelOldEnteries)) {
                            foreach ($modelOldEnteries as $keys => $modelOldEnteriesRow) {
                                if (!empty($modelOldEnteriesRow) && $modelOldEnteriesRow instanceof ProductSizes) {
                                    $modelOldEnteriesRow->delete();
                                }
                            }
                        }
                    }
                    if (!empty($newDiffIds)) {
                        foreach ($newDiffIds as $keyNew => $newDiffIdsRow) {
                            $modelOldEntery = new ProductSizes();
                            $modelOldEntery->product_id = $model->id;
                            $modelOldEntery->size_id = $newDiffIdsRow;
                            $modelOldEntery->save(false);
                        }
                    }
                } elseif ((isset($productData['Product']['option_size']) && $productData['Product']['option_size'] == "") || isNull($productData['Product']['option_size'])) {
                    $modelOldEnteries = ProductSizes::find()->andWhere(['product_id' => $model->id])->all();
                    if (!empty($modelOldEnteries)) {
                        foreach ($modelOldEnteries as $keys => $modelOldEnteriesRow) {
                            if (!empty($modelOldEnteriesRow) && $modelOldEnteriesRow instanceof ProductSizes) {
                                $modelOldEnteriesRow->delete();
                            }
                        }
                    }
                }
                // Updated sizes end

                // Status of product if color/brand status approved START.
                $isPendingApprovalColor = 0;
                $arrColors = explode(",", $model->option_color);
                if (!empty($arrColors)) {
                    $modelColors = Color::find()->where(['in', 'id', $arrColors])->all();
                    if (!empty($modelColors)) {
                        foreach ($modelColors as $c => $modelColorsRow) {
                            if (!empty($modelColorsRow) && $modelColorsRow instanceof Color && in_array($modelColorsRow->status, [Color::STATUS_PENDING_APPROVAL, Color::STATUS_DECLINE])) {
                                $isPendingApprovalColor += 1;
                            }
                        }
                    }
                }

                $isPendingApprovalBrand = 0;
                $modelBrand = Brand::findOne($model->brand_id);
                if (!empty($modelBrand) && $modelBrand instanceof Brand && in_array($modelBrand->status, [Brand::STATUS_PENDING_APPROVAL, Brand::STATUS_DECLINE])) {
                    $isPendingApprovalBrand += 1;
                }

                if ($isPendingApprovalColor == 0 && $isPendingApprovalBrand == 0 && ($model->status_id == ProductStatus::STATUS_PENDING_APPROVAL && !in_array($model->status_id, [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK, ProductStatus::STATUS_SOLD, ProductStatus::STATUS_ARCHIVED]))) {
                    $model->status_id = ProductStatus::STATUS_APPROVED;
                    $model->save(false);
                }
                // Status of product if color/brand status approved END.

                $modelAddress = $model->address;
                if (empty($modelAddress) && empty($productData['Product']['is_profile_address'])) {
                    $modelAddress = new UserAddress();
                    $modelAddress->user_id = Yii::$app->user->identity->id;
                    $addressData['UserAddress'] = $postData;
                    $addressData['UserAddress']['type'] = UserAddress::TYPE_SHOP;
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
                        $addressData['UserAddress']['type'] = UserAddress::TYPE_SHOP;
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
                    $addressData['UserAddress']['type'] = UserAddress::TYPE_SHOP;
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

                    if (!empty($shippingCountries) && !empty($shippingCosts)) {
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
            throw new NotFoundHttpException(getValidationErrorMsg('product_not_exist', Yii::$app->language));
        }

        $model->status_id = ProductStatus::STATUS_ARCHIVED;
        $model->save(false);

        $modelCartItems = CartItem::find()->where(['product_id' => $model->id])->all();
        if (!empty($modelCartItems)) {
            foreach ($modelCartItems as $key => $modelCartItem) {
                if (!empty($modelCartItem) && $modelCartItem instanceof CartItem) {
                    $modelCartItem->delete();
                }
            }
        }

        $modelMakeOffers = MakeOffer::find()->where(['product_id' => $model->id])->all();
        if (!empty($modelMakeOffers)) {
            foreach ($modelMakeOffers as $key1 => $modelMakeOffer) {
                if (!empty($modelMakeOffer) && $modelMakeOffer instanceof MakeOffer) {
                    $modelMakeOffer->delete();
                }
            }
        }

        $modelTrials = Trial::find()->where(['product_id' => $model->id])->all();
        if (!empty($modelTrials)) {
            foreach ($modelTrials as $key2 => $modelTrial) {
                if (!empty($modelTrial) && $modelTrial instanceof Trial) {
                    $modelTrial->delete();
                }
            }
        }

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

        throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist', Yii::$app->language));
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
            throw new NotFoundHttpException(getValidationErrorMsg('product_receipt_not_exist', Yii::$app->language));
        }

        if (!empty($model->file) && file_exists(Yii::getAlias('@productReceiptImageRelativePath') . "/" . $model->file)) {
            unlink(Yii::getAlias('@productReceiptImageRelativePath') . "/" . $model->file);
        }

        if (!empty($model->file) && file_exists(Yii::getAlias('@productReceiptImageThumbRelativePath') . "/" . $model->file)) {
            unlink(Yii::getAlias('@productReceiptImageThumbRelativePath') . "/" . $model->file);
        }

        $model->delete();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionSizeList()
    {
        $model = new SizesSearch();
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        return $model->searchForMobile($requestParams);
    }

}
