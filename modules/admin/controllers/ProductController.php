<?php

namespace app\modules\admin\controllers;

use app\models\Brand;
use app\models\CartItem;
use app\models\Color;
use app\models\MakeOffer;
use app\models\Notification;
use app\models\ProductCategory;
use app\models\ProductImage;
use app\models\ProductReceipt;
use app\models\ProductSizes;
use app\models\ProductStatus;
use app\models\search\ProductSearch;
use app\models\ShippingCost;
use app\models\ShippingPrice;
use app\models\Sizes;
use app\models\Trial;
use app\modules\api\v2\models\UserAddress;
use app\modules\api\v2\models\User;
use Imagine\Image\Box;
use Yii;
use app\modules\admin\models\Product;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use kartik\growl\Growl;
use yii\web\UploadedFile;

/**
 * ProductsController implements the CRUD actions for Products model.
 */
class ProductController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'new-product', 'create', 'new-product-create', 'update', 'new-product-update', 'view', 'new-product-view', 'delete', 'new-product-delete', 'get-sub-category-list', 'update-top-selling', 'update-top-trending', 'delete-product-image', 'delete-product-receipt-image', 'delete-multiple'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'new-product', 'create', 'new-product-create', 'update', 'new-product-update', 'view', 'new-product-view', 'delete', 'new-product-delete', 'get-sub-category-list', 'update-top-selling', 'update-top-trending', 'delete-product-image', 'delete-product-receipt-image', 'delete-multiple'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Products models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //p($dataProvider->getModels());

        $categories = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', function ($data) {
            return $data['name'];
        });

        $subCategories = ArrayHelper::map(ProductCategory::find()->where(['IS NOT', 'parent_category_id', null])->all(), 'id', function ($data) {
            return $data['name'];
        });

        $productType = [Product::PRODUCT_TYPE_NEW => 'New', Product::PRODUCT_TYPE_USED => 'Used'];

        $statuses = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'productType' => $productType,
            'arrStatus' => $statuses
        ]);
    }

    /**
     * @return string
     */
    public function actionNewProduct()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $categories = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', function ($data) {
            return $data['name'];
        });

        $subCategories = ArrayHelper::map(ProductCategory::find()->where(['IS NOT', 'parent_category_id', null])->all(), 'id', function ($data) {
            return $data['name'];
        });

        $productType = [Product::PRODUCT_TYPE_NEW => 'New', Product::PRODUCT_TYPE_USED => 'Used'];

        $statuses = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');

        return $this->render('new-product', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'productType' => $productType,
            'arrStatus' => $statuses
        ]);
    }

    /**
     * Displays a single Products model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionNewProductView($id)
    {
        return $this->render('new-product-view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Products model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = [];
        $size = [];
        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');
        $color = ArrayHelper::map(Color::find()->where(['not in', 'status', [Color::STATUS_DECLINE]])->all(), 'id', 'name');
        $status = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');
        $shippingCountry = ArrayHelper::map(ShippingCost::find()->all(), 'id', 'name');
        $shippingPrice = $model->shippingCost;

        $postData = Yii::$app->request->post('Product');

        $model->scenario = Product::SCENARIO_CREATE;
        $model->is_top_selling = Product::IS_TOP_SELLING_NO;
        if (!empty($postData['is_top_selling'])) {
            $model->is_top_selling = $postData['is_top_selling'];
        }

        $model->is_top_trending = Product::IS_TOP_TRENDING_NO;
        if (!empty($postData['is_top_trending'])) {
            $model->is_top_trending = $postData['is_top_trending'];
        }

        $model->is_admin_favourite = Product::IS_ADMIN_FAVOURITE_NO;
        if (!empty($postData['is_admin_favourite'])) {
            $model->is_admin_favourite = $postData['is_admin_favourite'];
        }

        $model->user_id = Yii::$app->user->identity->id;

        $ReceiptImages = UploadedFile::getInstancesByName('receipt');
        $model->receipt = $ReceiptImages;

        $model->gender = Product::GENDER_FOR_FEMALE;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            } else {
                $model->option_show_only = '0';
            }

            $images = UploadedFile::getInstances($model, 'images');

            $model->option_color = implode(",", $postData['option_color']);

            $sizeIds = (!empty($postData['option_size'])) ? $postData['option_size'] : [];
            $model->option_size = (!empty($sizeIds)) ? implode(",", $sizeIds) : "";

            if ($model->save()) {

                // Product Size DB entry start.
                if (!empty($postData['option_size']) && is_array($postData['option_size'])) {
                    $productSizes = $postData['option_size'];
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

                if (!empty($postData['shipping_country_price'])) {
                    foreach ($postData['shipping_country_price'] as $keyPrice => $countryPrice) {
                        if (!empty($countryPrice)) {
                            $countryId = $keyPrice + 1;
                            $modelCountry = ShippingCost::find()->where(['id' => $countryId])->one();
                            if (!empty($modelCountry) && $modelCountry instanceof ShippingCost) {
                                $modelPrice = new ShippingPrice();
                                $modelPrice->shipping_cost_id = $modelCountry->id;
                                $modelPrice->price = $countryPrice;
                                $modelPrice->product_id = $model->id;
                                $modelPrice->save(false);
                            }
                        }
                    }
                }

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

                        Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert product picture name into database

                        $modelImage->product_id = $model->id;
                        $modelImage->name = $fileName;
                        $modelImage->name = $fileName;
                        $modelImage->created_at = date('Y-m-d H:i:s');
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
                        Image::getImagine()->open($actualImagePathReceipt)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePathReceipt, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        chmod($thumbImagePathReceipt, 0777);

                        // Insert product picture name into database
                        $modelImageReceipt->product_id = $model->id;
                        $modelImageReceipt->file = $fileNameReceipt;
                        $modelImageReceipt->save(false);
                    }
                }

                // For address Start
                $addressData['UserAddress'] = $postData;

                $addressModel = UserAddress::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['street' => $addressData['UserAddress']['street'], 'city' => $addressData['UserAddress']['city'], 'state' => $addressData['UserAddress']['state'], 'country' => $addressData['UserAddress']['country'], 'zip_code' => $addressData['UserAddress']['zip_code']])->one();
                if (empty($addressModel)) {
                    $modelAddress = new UserAddress();
                    $modelAddress->street = $addressData['UserAddress']['street'];
                    $modelAddress->city = $addressData['UserAddress']['city'];
                    $modelAddress->state = $addressData['UserAddress']['state'];
                    $modelAddress->country = $addressData['UserAddress']['country'];
                    $modelAddress->zip_code = $addressData['UserAddress']['zip_code'];
                } else {
                    $modelAddress = UserAddress::find()->where(['id' => $addressModel->id])->one();
                }
                $modelAddress->user_id = Yii::$app->user->identity->id;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                //if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->state . ", " . $modelAddress->country . ", " . $modelAddress->zip_code;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                if ($modelAddress->save(false)) {
                    $model->address_id = $modelAddress->id;
                    $model->save(false);
                }
                //}
                // For address End
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'Product created successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'size' => $size,
            'brand' => $brand,
            'color' => $color,
            'status' => $status,
            'shippingCountry' => $shippingCountry,
            'shippingPrice' => $shippingPrice
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionNewProductCreate()
    {
        $model = new Product();
        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = [];
        $size = [];
        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');
        $color = ArrayHelper::map(Color::find()->where(['not in', 'status', [Color::STATUS_DECLINE]])->all(), 'id', 'name');
        $status = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');
        $shippingCountry = ArrayHelper::map(ShippingCost::find()->all(), 'id', 'name');
        $shippingPrice = $model->shippingCost;

        $postData = Yii::$app->request->post('Product');

        $model->scenario = Product::SCENARIO_CREATE;
        $model->is_top_selling = Product::IS_TOP_SELLING_NO;
        if (!empty($postData['is_top_selling'])) {
            $model->is_top_selling = $postData['is_top_selling'];
        }

        $model->is_top_trending = Product::IS_TOP_TRENDING_NO;
        if (!empty($postData['is_top_trending'])) {
            $model->is_top_trending = $postData['is_top_trending'];
        }

        $model->is_admin_favourite = Product::IS_ADMIN_FAVOURITE_NO;
        if (!empty($postData['is_admin_favourite'])) {
            $model->is_admin_favourite = $postData['is_admin_favourite'];
        }

        $model->user_id = Yii::$app->user->identity->id;

        $ReceiptImages = UploadedFile::getInstancesByName('receipt');
        $model->receipt = $ReceiptImages;

        $model->gender = Product::GENDER_FOR_FEMALE;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            } else {
                $model->option_show_only = '0';
            }

            $images = UploadedFile::getInstances($model, 'images');

            $model->option_color = implode(",", $postData['option_color']);

            $sizeIds = (!empty($postData['option_size'])) ? $postData['option_size'] : [];
            $model->option_size = (!empty($sizeIds)) ? implode(",", $sizeIds) : "";

            if ($model->save()) {

                // Product Size DB entry start.
                if (!empty($postData['option_size']) && is_array($postData['option_size'])) {
                    $productSizes = $postData['option_size'];
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

                if (!empty($postData['shipping_country_price'])) {
                    foreach ($postData['shipping_country_price'] as $keyPrice => $countryPrice) {
                        if (!empty($countryPrice)) {
                            $countryId = $keyPrice + 1;
                            $modelCountry = ShippingCost::find()->where(['id' => $countryId])->one();
                            if (!empty($modelCountry) && $modelCountry instanceof ShippingCost) {
                                $modelPrice = new ShippingPrice();
                                $modelPrice->shipping_cost_id = $modelCountry->id;
                                $modelPrice->price = $countryPrice;
                                $modelPrice->product_id = $model->id;
                                $modelPrice->save(false);
                            }
                        }
                    }
                }

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

                        Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert product picture name into database

                        $modelImage->product_id = $model->id;
                        $modelImage->name = $fileName;
                        $modelImage->name = $fileName;
                        $modelImage->created_at = date('Y-m-d H:i:s');
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
                        Image::getImagine()->open($actualImagePathReceipt)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePathReceipt, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        chmod($thumbImagePathReceipt, 0777);

                        // Insert product picture name into database
                        $modelImageReceipt->product_id = $model->id;
                        $modelImageReceipt->file = $fileNameReceipt;
                        $modelImageReceipt->save(false);
                    }
                }

                // For address Start
                $addressData['UserAddress'] = $postData;

                $addressModel = UserAddress::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['street' => $addressData['UserAddress']['street'], 'city' => $addressData['UserAddress']['city'], 'state' => $addressData['UserAddress']['state'], 'country' => $addressData['UserAddress']['country'], 'zip_code' => $addressData['UserAddress']['zip_code']])->one();
                if (empty($addressModel)) {
                    $modelAddress = new UserAddress();
                    $modelAddress->street = $addressData['UserAddress']['street'];
                    $modelAddress->city = $addressData['UserAddress']['city'];
                    $modelAddress->state = $addressData['UserAddress']['state'];
                    $modelAddress->country = $addressData['UserAddress']['country'];
                    $modelAddress->zip_code = $addressData['UserAddress']['zip_code'];
                } else {
                    $modelAddress = UserAddress::find()->where(['id' => $addressModel->id])->one();
                }
                $modelAddress->user_id = Yii::$app->user->identity->id;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                //if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->state . ", " . $modelAddress->country . ", " . $modelAddress->zip_code;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                if ($modelAddress->save(false)) {
                    $model->address_id = $modelAddress->id;
                    $model->save(false);
                }
                //}
                // For address End
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'New product created successfully.');
            return $this->redirect(['new-product']);
        }

        return $this->render('new-product-create', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'size' => $size,
            'brand' => $brand,
            'color' => $color,
            'status' => $status,
            'shippingCountry' => $shippingCountry,
            'shippingPrice' => $shippingPrice
        ]);
    }

    /**
     * Updates an existing Products model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        //p(Yii::$app->request->post());
        $model = $this->findModel($id);
        $oldUserId = $model->user_id;

        $modelAddress = $model->address;
        //p($modelAddress);
        if (!empty($modelAddress) && $modelAddress instanceof UserAddress) {
            $model->street = $modelAddress->street;
            $model->city = $modelAddress->city;
            $model->state = $modelAddress->state;
            $model->country = $modelAddress->country;
            $model->zip_code = $modelAddress->zip_code;
        }
        //p($model);

        //$shippingCountry = ArrayHelper::map(ShippingCost::find()->leftJoin('shipping_price', 'shipping_price.shipping_cost_id = shipping_cost.id')->where(['shipping_price.product_id' => $id])->all(), 'id', 'name');
        $shippingCountry = ArrayHelper::map(ShippingCost::find()->all(), 'id', 'name');
        $shippingPrice = $model->shippingCost;

        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $model->category_id])->all(), 'id', 'name');;
        if (empty($subcategory)) {
            $subcategory = ArrayHelper::map(ProductCategory::find()->where(['IS NOT', 'parent_category_id', null])->all(), 'id', 'name');;
        }

        $size = ArrayHelper::map(Sizes::find()->where(['product_category_id' => $model->category_id, 'status' => Sizes::STATUS_ACTIVE])->all(), 'id', 'size');
        if (empty($size)) {
            $size = ArrayHelper::map(ProductSizes::find()->where(['product_id' => $model->id])->all(), 'id', 'size');
        }

        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');
        $color = ArrayHelper::map(Color::find()->where(['not in', 'status', [Color::STATUS_DECLINE]])->all(), 'id', 'name');
        $status = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');

        $postData = Yii::$app->request->post('Product');

        $productSizes = $model->productSizes;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (!empty($postData['available_quantity']) && $postData['available_quantity'] >= 1) {
                $model->available_quantity = $postData['available_quantity'];

                //if(!in_array($model->available_quantity,[ProductStatus::STATUS_ARCHIVED,ProductStatus::STATUS_PENDING_APPROVAL])){
                if (in_array($model->status_id, [ProductStatus::STATUS_SOLD])) {
                    $model->status_id = ProductStatus::STATUS_IN_STOCK;
                }
            }

            if (empty($postData['available_quantity']) || $postData['available_quantity'] <= 0) {
                //$model->available_quantity = $postData['available_quantity'];
                $model->available_quantity = 0;

                //if(!in_array($model->available_quantity,[ProductStatus::STATUS_ARCHIVED,ProductStatus::STATUS_PENDING_APPROVAL])){
                if (in_array($model->status_id, [ProductStatus::STATUS_IN_STOCK])) {
                    $model->status_id = ProductStatus::STATUS_SOLD;
                }
            }

            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            } else {
                $model->option_show_only = '0';
            }

            $model->option_color = implode(",", $postData['option_color']);
            if (empty($oldUserId)) {
                $model->user_id = Yii::$app->user->identity->id;
            }

            $model->is_top_selling = Product::IS_TOP_SELLING_NO;
            if (!empty($postData['is_top_selling'])) {
                $model->is_top_selling = $postData['is_top_selling'];
            }

            $model->is_top_trending = Product::IS_TOP_TRENDING_NO;
            if (!empty($postData['is_top_trending'])) {
                $model->is_top_trending = $postData['is_top_trending'];
            }

            $images = UploadedFile::getInstances($model, 'images');

            $ReceiptImages = UploadedFile::getInstances($model, 'receipt');

            if (!empty($postData['type'])) {
                $model->type = $postData['type'];
            }

            $sizeIds = (!empty($postData['option_size'])) ? $postData['option_size'] : [];
            $model->option_size = (!empty($sizeIds)) ? implode(",", $sizeIds) : "";

            if ($model->save(false)) {

                // Updated sizes start
                if (!empty($postData['option_size']) && is_array($postData['option_size'])) {
                    $oldSizeIds = array_column($productSizes, 'size_id');
                    $newSizeIds = array_values($postData['option_size']);
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
                } elseif ((isset($postData['option_size']) && $postData['option_size'] == "") || $postData['option_size'] == "" || $postData['option_size'] == []) {
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

                if (!empty($postData['shipping_country_ids']) && !empty($postData['shipping_country_price'])) {

                    $modelOldPrice = ShippingPrice::find()->where(['product_id' => $model->id])->all();
                    if (!empty($modelOldPrice)) {
                        foreach ($modelOldPrice as $oldKey => $modelOldPriceRow) {
                            if (!empty($modelOldPriceRow) && $modelOldPriceRow instanceof ShippingPrice) {
                                $modelOldPriceRow->delete();
                            }
                        }
                    }

                    $shippingCountryIds = explode(",", $postData['shipping_country_ids']);

                    if (!empty($shippingCountryIds)) {
                        foreach ($postData['shipping_country_price'] as $keyPrice => $countryPrice) {
                            if (!empty($countryPrice)) {
                                $countryId = (!empty($shippingCountryIds[$keyPrice])) ? $shippingCountryIds[$keyPrice] : $keyPrice + 1;

                                $modelCountry = ShippingCost::find()->where(['id' => $countryId])->one();
                                if (!empty($modelCountry) && $modelCountry instanceof ShippingCost) {
                                    $modelPrice = new ShippingPrice();
                                    $modelPrice->shipping_cost_id = $modelCountry->id;
                                    $modelPrice->price = $countryPrice;
                                    $modelPrice->product_id = $model->id;
                                    $modelPrice->save(false);
                                }
                            }
                        }
                    }
                }

                if (!empty($images)) {

                    $oldImages = $model->productImages;
                    if (!empty($oldImages)) {
                        foreach ($oldImages as $oldImageRow) {

                            if (!empty($oldImageRow) && $oldImageRow instanceof ProductImage) {

                                if (!empty($oldImageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $oldImageRow->name)) {
                                    unlink(Yii::getAlias('@productImageRelativePath') . "/" . $oldImageRow->name);
                                }

                                if (!empty($oldImageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $oldImageRow->name)) {
                                    unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $oldImageRow->name);
                                }
                                $oldImageRow->delete();
                            }
                        }
                    }

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

                        Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert product picture name into database

                        $modelImage->product_id = $model->id;
                        $modelImage->name = $fileName;
                        $modelImage->created_at = date('Y-m-d H:i:s');
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

                        Image::getImagine()->open($actualImagePathReceipt)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePathReceipt, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        chmod($thumbImagePathReceipt, 0777);

                        // Insert product picture name into database
                        $modelImageReceipt->product_id = $model->id;
                        $modelImageReceipt->file = $fileNameReceipt;
                        $modelImageReceipt->save(false);
                    }
                }

                // For address Start
                $addressData['UserAddress'] = $postData;

                $idUser = Yii::$app->user->identity->id;
                if (!empty($oldUserId)) {
                    $idUser = $oldUserId;
                }
                //p($idUser,0);
                //p($addressData['UserAddress'],0);

                $addressModel = UserAddress::find()->where(['user_id' => $idUser])->andWhere(['street' => $addressData['UserAddress']['street'], 'city' => $addressData['UserAddress']['city'], 'state' => $addressData['UserAddress']['state'], 'country' => $addressData['UserAddress']['country'], 'zip_code' => $addressData['UserAddress']['zip_code']])->one();
                //p($addressModel);
                if (empty($addressModel)) {
                    $modelAddress = new UserAddress();
                    $modelAddress->street = $addressData['UserAddress']['street'];
                    $modelAddress->city = $addressData['UserAddress']['city'];
                    $modelAddress->state = $addressData['UserAddress']['state'];
                    $modelAddress->country = $addressData['UserAddress']['country'];
                    $modelAddress->zip_code = $addressData['UserAddress']['zip_code'];
                } else {
                    $modelAddress = UserAddress::find()->where(['id' => $addressModel->id])->one();
                }
                $modelAddress->user_id = $idUser;
                $modelAddress->type = UserAddress::TYPE_SHOP;

                //if ($modelAddress->load($addressData) && $modelAddress->validate()) {

                $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->state . ", " . $modelAddress->country . ", " . $modelAddress->zip_code;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                if ($modelAddress->save(false)) {
                    $model->address_id = $modelAddress->id;
                    $model->save(false);
                }
                //}
                // For address End

                // Status of product is Approve then color/brand status approved START.
                if (in_array($model->status_id, [ProductStatus::STATUS_APPROVED])) {
                    $arrColors = explode(",", $model->option_color);
                    if (!empty($arrColors)) {
                        $getUsers = [];
                        $modelColors = Color::find()->where(['in', 'id', $arrColors])->all();
                        if (!empty($modelColors)) {
                            $colorIds = [];
                            foreach ($modelColors as $c => $modelColorsRow) {
                                if (!empty($modelColorsRow) && $modelColorsRow instanceof Color && in_array($modelColorsRow->status, [Color::STATUS_PENDING_APPROVAL])) {
                                    $colorIds[] = $modelColorsRow->id;
                                    $modelColorsRow->status = Color::STATUS_APPROVE;
                                    $modelColorsRow->save(false);
                                }
                            }

                            if (!empty($colorIds) && count($colorIds) > 0) {
                                $query = Product::find();
                                if (!empty($colorIds)) {
                                    foreach ($colorIds as $keyColor => $colorRow) {
                                        if ($keyColor > 0) {
                                            $query->orFilterWhere([
                                                'or',
                                                ['OR LIKE', 'products.option_color', $colorRow, false],
                                            ]);
                                        } else {
                                            $query->andFilterWhere([
                                                'or',
                                                ['LIKE', 'products.option_color', $colorRow, false],
                                            ]);
                                        }
                                    }
                                }
                                $modelProductsBasedOnColor = $query->all();

                                if (!empty($modelProductsBasedOnColor)) {
                                    foreach ($modelProductsBasedOnColor as $keyProd => $modelProductsBasedOnColorRow) {
                                        if (!empty($modelProductsBasedOnColorRow) && $modelProductsBasedOnColorRow instanceof Product) {
                                            $userModel = $modelProductsBasedOnColorRow->user;
                                            if (!empty($getUsers)) {
                                                unset($getUsers);
                                            }
                                            // Send push notification Start
                                            $getUsers[] = $userModel;
                                            if (!empty($getUsers)) {
                                                foreach ($getUsers as $userROW) {
                                                    if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                                        $notificationText = "";
                                                        if (!empty($userROW->userDevice)) {
                                                            $userDevice = $userROW->userDevice;

                                                            if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                                // Insert into notification.
                                                                $notificationText = "Color has been approved, Which you have selected for your product.";
                                                                $modelNotification = new Notification();
                                                                $modelNotification->owner_id = Yii::$app->user->identity->id;
                                                                $modelNotification->notification_receiver_id = $userROW->id;
                                                                $modelNotification->ref_id = $modelProductsBasedOnColorRow->id;
                                                                $modelNotification->notification_text = $notificationText;
                                                                $modelNotification->action = "product_color_approve";
                                                                $modelNotification->ref_type = "products";
                                                                $modelNotification->product_id = $modelProductsBasedOnColorRow->id;
                                                                $modelNotification->save(false);

                                                                $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                                if ($userDevice->device_platform == 'android') {
                                                                    $notificationToken = array($userDevice->notification_token);
                                                                    $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                                                    $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
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
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            // Send push notification End

                                            // Send Email notification Start
                                            if (!empty($userModel) && $userModel instanceof User && !empty($userModel->email)) {
                                                try {
                                                    $message = "Color has been approved, that has been added by you.";
                                                    Yii::$app->mailer->compose('admin/general-info-send-to-user-html', ['userModel' => $userModel, 'message' => $message])
                                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                        ->setTo($userModel->email)
                                                        ->setSubject('Color approved!')
                                                        ->send();
                                                } catch (HttpException $e) {
                                                    echo "Error: " . $e->getMessage();
                                                }
                                            }
                                            // Send Email notification End
                                        }
                                    }
                                }

                            }

                        }
                    }

                    $modelBrand = Brand::findOne($model->brand_id);
                    if (!empty($modelBrand) && $modelBrand instanceof Brand && in_array($modelBrand->status, [Brand::STATUS_PENDING_APPROVAL])) {
                        $getUsers = [];
                        $modelBrand->status = Brand::STATUS_APPROVE;
                        $modelBrand->save(false);

                        $modelProductsBasedOnBrand = Product::find()->where(['brand_id' => $model->brand_id])->all();

                        if (!empty($modelProductsBasedOnBrand)) {
                            foreach ($modelProductsBasedOnBrand as $keyProdBrand => $modelProductsBasedOnBrandRow) {
                                if (!empty($modelProductsBasedOnBrandRow) && $modelProductsBasedOnBrandRow instanceof Product) {
                                    $userDataModel = $modelProductsBasedOnBrandRow->user;
                                    if (!empty($getUsers)) {
                                        unset($getUsers);
                                    }
                                    // Send push notification Start
                                    $getUsers[] = $userDataModel;
                                    if (!empty($getUsers)) {
                                        foreach ($getUsers as $userROW) {
                                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                                $notificationText = "";
                                                if (!empty($userROW->userDevice)) {
                                                    $userDevice = $userROW->userDevice;

                                                    if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                        // Insert into notification.
                                                        $notificationText = "Brand has been approved, Which you have selected for your product.";
                                                        $modelNotification = new Notification();
                                                        $modelNotification->owner_id = Yii::$app->user->identity->id;
                                                        $modelNotification->notification_receiver_id = $userROW->id;
                                                        $modelNotification->ref_id = $modelProductsBasedOnColorRow->id;
                                                        $modelNotification->notification_text = $notificationText;
                                                        $modelNotification->action = "product_brand_approve";
                                                        $modelNotification->ref_type = "products";
                                                        $modelNotification->product_id = $modelProductsBasedOnBrandRow->id;
                                                        $modelNotification->save(false);

                                                        $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                        if ($userDevice->device_platform == 'android') {
                                                            $notificationToken = array($userDevice->notification_token);
                                                            $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                                            $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
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
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    // Send push notification End

                                    // Send Email notification Start
                                    if (!empty($userDataModel) && $userDataModel instanceof User && !empty($userDataModel->email)) {
                                        try {
                                            $message = "Brand has been approved, Which you have selected for your product.";
                                            Yii::$app->mailer->compose('admin/general-info-send-to-user-html', ['userModel' => $userDataModel, 'message' => $message])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($userDataModel->email)
                                                ->setSubject('Brand approved!')
                                                ->send();
                                        } catch (HttpException $e) {
                                            echo "Error: " . $e->getMessage();
                                        }
                                    }
                                    // Send Email notification End
                                }
                            }
                        }
                    }
                }
                // Status of product is Approve then color/brand status approved END.
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'Product updated successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'size' => $size,
            'brand' => $brand,
            'color' => $color,
            'status' => $status,
            'shippingCountry' => $shippingCountry,
            'shippingPrice' => $shippingPrice
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionNewProductUpdate($id)
    {
        $model = $this->findModel($id);
        $oldUserId = $model->user_id;

        $modelAddress = $model->address;
        //p($modelAddress);
        if (!empty($modelAddress) && $modelAddress instanceof UserAddress) {
            $model->street = $modelAddress->street;
            $model->city = $modelAddress->city;
            $model->state = $modelAddress->state;
            $model->country = $modelAddress->country;
            $model->zip_code = $modelAddress->zip_code;
        }
        //p($model);

        //$shippingCountry = ArrayHelper::map(ShippingCost::find()->all(), 'id', 'name');
        //$shippingCountry = ArrayHelper::map(ShippingCost::find()->leftJoin('shipping_price', 'shipping_price.shipping_cost_id = shipping_cost.id')->where(['shipping_price.product_id' => $id])->all(), 'id', 'name');
        $shippingCountry = ArrayHelper::map(ShippingCost::find()->all(), 'id', 'name');
        $shippingPrice = $model->shippingCost;

        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $model->category_id])->all(), 'id', 'name');;
        if (empty($subcategory)) {
            $subcategory = ArrayHelper::map(ProductCategory::find()->where(['IS NOT', 'parent_category_id', null])->all(), 'id', 'name');;
        }

        $size = ArrayHelper::map(Sizes::find()->where(['product_category_id' => $model->category_id, 'status' => Sizes::STATUS_ACTIVE])->all(), 'id', 'size');
        if (empty($size)) {
            $size = ArrayHelper::map(ProductSizes::find()->where(['product_id' => $model->id])->all(), 'id', 'size');
        }

        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');
        $color = ArrayHelper::map(Color::find()->where(['not in', 'status', [Color::STATUS_DECLINE]])->all(), 'id', 'name');
        $status = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');

        $postData = Yii::$app->request->post('Product');

        $productSizes = $model->productSizes;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (!empty($postData['available_quantity']) && $postData['available_quantity'] >= 1) {
                $model->available_quantity = $postData['available_quantity'];

                //if(!in_array($model->available_quantity,[ProductStatus::STATUS_ARCHIVED,ProductStatus::STATUS_PENDING_APPROVAL])){
                if (in_array($model->status_id, [ProductStatus::STATUS_SOLD])) {
                    $model->status_id = ProductStatus::STATUS_IN_STOCK;
                }
            }

            if (empty($postData['available_quantity']) || $postData['available_quantity'] <= 0) {
                //$model->available_quantity = $postData['available_quantity'];
                $model->available_quantity = 0;

                //if(!in_array($model->available_quantity,[ProductStatus::STATUS_ARCHIVED,ProductStatus::STATUS_PENDING_APPROVAL])){
                if (in_array($model->status_id, [ProductStatus::STATUS_IN_STOCK])) {
                    $model->status_id = ProductStatus::STATUS_SOLD;
                }
            }

            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            } else {
                $model->option_show_only = '0';
            }

            $model->option_color = implode(",", $postData['option_color']);
            if (empty($oldUserId)) {
                $model->user_id = Yii::$app->user->identity->id;
            }

            $model->is_top_selling = Product::IS_TOP_SELLING_NO;
            if (!empty($postData['is_top_selling'])) {
                $model->is_top_selling = $postData['is_top_selling'];
            }

            $model->is_top_trending = Product::IS_TOP_TRENDING_NO;
            if (!empty($postData['is_top_trending'])) {
                $model->is_top_trending = $postData['is_top_trending'];
            }

            $images = UploadedFile::getInstances($model, 'images');

            $ReceiptImages = UploadedFile::getInstances($model, 'receipt');

            if (!empty($postData['type'])) {
                $model->type = $postData['type'];
            }

            $sizeIds = (!empty($postData['option_size'])) ? $postData['option_size'] : [];
            $model->option_size = (!empty($sizeIds)) ? implode(",", $sizeIds) : "";

            if ($model->save(false)) {

                // Updated sizes start
                if (!empty($postData['option_size']) && is_array($postData['option_size'])) {
                    $oldSizeIds = array_column($productSizes, 'size_id');
                    $newSizeIds = array_values($postData['option_size']);
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
                } elseif ((isset($postData['option_size']) && $postData['option_size'] == "") || $postData['option_size'] == "" || $postData['option_size'] == []) {
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

                //
                if (!empty($postData['shipping_country_price'])) {

                    $modelOldPrice = ShippingPrice::find()->where(['product_id' => $model->id])->all();
                    if (!empty($modelOldPrice)) {
                        foreach ($modelOldPrice as $oldKey => $modelOldPriceRow) {
                            if (!empty($modelOldPriceRow) && $modelOldPriceRow instanceof ShippingPrice) {
                                $modelOldPriceRow->delete();
                            }
                        }
                    }

                    foreach ($postData['shipping_country_price'] as $keyPrice => $countryPrice) {
                        if (!empty($countryPrice)) {
                            $countryId = $keyPrice + 1;
                            $modelCountry = ShippingCost::find()->where(['id' => $countryId])->one();
                            if (!empty($modelCountry) && $modelCountry instanceof ShippingCost) {
                                $modelPrice = new ShippingPrice();
                                $modelPrice->shipping_cost_id = $modelCountry->id;
                                $modelPrice->price = $countryPrice;
                                $modelPrice->product_id = $model->id;
                                $modelPrice->save(false);
                            }
                        }
                    }
                }

                //
                if (!empty($images)) {

                    $oldImages = $model->productImages;
                    if (!empty($oldImages)) {
                        foreach ($oldImages as $oldImageRow) {

                            if (!empty($oldImageRow) && $oldImageRow instanceof ProductImage) {

                                if (!empty($oldImageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $oldImageRow->name)) {
                                    unlink(Yii::getAlias('@productImageRelativePath') . "/" . $oldImageRow->name);
                                }

                                if (!empty($oldImageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $oldImageRow->name)) {
                                    unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $oldImageRow->name);
                                }
                                $oldImageRow->delete();
                            }
                        }
                    }

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

                        Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert product picture name into database

                        $modelImage->product_id = $model->id;
                        $modelImage->name = $fileName;
                        $modelImage->created_at = date('Y-m-d H:i:s');
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

                        Image::getImagine()->open($actualImagePathReceipt)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePathReceipt, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        chmod($thumbImagePathReceipt, 0777);

                        // Insert product picture name into database
                        $modelImageReceipt->product_id = $model->id;
                        $modelImageReceipt->file = $fileNameReceipt;
                        $modelImageReceipt->save(false);
                    }
                }


                // For address Start
                $addressData['UserAddress'] = $postData;

                $idUser = Yii::$app->user->identity->id;
                if (!empty($oldUserId)) {
                    $idUser = $oldUserId;
                }
                //p($idUser,0);
                //p($addressData['UserAddress'],0);

                $addressModel = UserAddress::find()->where(['user_id' => $idUser])->andWhere(['street' => $addressData['UserAddress']['street'], 'city' => $addressData['UserAddress']['city'], 'state' => $addressData['UserAddress']['state'], 'country' => $addressData['UserAddress']['country'], 'zip_code' => $addressData['UserAddress']['zip_code']])->one();
                //p($addressModel);
                if (empty($addressModel)) {
                    $modelAddress = new UserAddress();
                    $modelAddress->street = $addressData['UserAddress']['street'];
                    $modelAddress->city = $addressData['UserAddress']['city'];
                    $modelAddress->state = $addressData['UserAddress']['state'];
                    $modelAddress->country = $addressData['UserAddress']['country'];
                    $modelAddress->zip_code = $addressData['UserAddress']['zip_code'];
                } else {
                    $modelAddress = UserAddress::find()->where(['id' => $addressModel->id])->one();
                }
                $modelAddress->user_id = $idUser;
                $modelAddress->type = UserAddress::TYPE_SHOP;

                //if ($modelAddress->load($addressData) && $modelAddress->validate()) {

                $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->state . ", " . $modelAddress->country . ", " . $modelAddress->zip_code;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                if ($modelAddress->save(false)) {
                    $model->address_id = $modelAddress->id;
                    $model->save(false);
                }
                //}
                // For address End


                // Status of product is Approve then color/brand status approved START.
                if (in_array($model->status_id, [ProductStatus::STATUS_APPROVED])) {

                    $arrColors = explode(",", $model->option_color);
                    if (!empty($arrColors)) {
                        $getUsers = [];
                        $modelColors = Color::find()->where(['in', 'id', $arrColors])->all();
                        if (!empty($modelColors)) {
                            $colorIds = [];
                            foreach ($modelColors as $c => $modelColorsRow) {
                                if (!empty($modelColorsRow) && $modelColorsRow instanceof Color && in_array($modelColorsRow->status, [Color::STATUS_PENDING_APPROVAL])) {
                                    $colorIds[] = $modelColorsRow->id;
                                    $modelColorsRow->status = Color::STATUS_APPROVE;
                                    $modelColorsRow->save(false);
                                }
                            }

                            if (!empty($colorIds) && count($colorIds) > 0) {
                                $query = Product::find();
                                if (!empty($colorIds)) {
                                    foreach ($colorIds as $keyColor => $colorRow) {
                                        if ($keyColor > 0) {
                                            $query->orFilterWhere([
                                                'or',
                                                ['OR LIKE', 'products.option_color', $colorRow, false],
                                            ]);
                                        } else {
                                            $query->andFilterWhere([
                                                'or',
                                                ['LIKE', 'products.option_color', $colorRow, false],
                                            ]);
                                        }
                                    }
                                }
                                $modelProductsBasedOnColor = $query->all();

                                if (!empty($modelProductsBasedOnColor)) {
                                    foreach ($modelProductsBasedOnColor as $keyProd => $modelProductsBasedOnColorRow) {
                                        if (!empty($modelProductsBasedOnColorRow) && $modelProductsBasedOnColorRow instanceof Product) {
                                            $userModel = $modelProductsBasedOnColorRow->user;
                                            if (!empty($getUsers)) {
                                                unset($getUsers);
                                            }
                                            // Send push notification Start
                                            $getUsers[] = $userModel;
                                            if (!empty($getUsers)) {
                                                foreach ($getUsers as $userROW) {
                                                    if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                                        $notificationText = "";
                                                        if (!empty($userROW->userDevice)) {
                                                            $userDevice = $userROW->userDevice;

                                                            if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                                // Insert into notification.
                                                                $notificationText = "Color has been approved, Which you have selected for your product.";
                                                                $modelNotification = new Notification();
                                                                $modelNotification->owner_id = Yii::$app->user->identity->id;
                                                                $modelNotification->notification_receiver_id = $userROW->id;
                                                                $modelNotification->ref_id = $modelProductsBasedOnColorRow->id;
                                                                $modelNotification->notification_text = $notificationText;
                                                                $modelNotification->action = "product_color_approve";
                                                                $modelNotification->ref_type = "products";
                                                                $modelNotification->product_id = $modelProductsBasedOnColorRow->id;
                                                                $modelNotification->save(false);

                                                                $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                                if ($userDevice->device_platform == 'android') {
                                                                    $notificationToken = array($userDevice->notification_token);
                                                                    $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                                                    $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
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
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            // Send push notification End

                                            // Send Email notification Start
                                            if (!empty($userModel) && $userModel instanceof User && !empty($userModel->email)) {
                                                try {
                                                    $message = "Color has been approved, that has been added by you.";
                                                    Yii::$app->mailer->compose('admin/general-info-send-to-user-html', ['userModel' => $userModel, 'message' => $message])
                                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                        ->setTo($userModel->email)
                                                        ->setSubject('Color approved!')
                                                        ->send();
                                                } catch (HttpException $e) {
                                                    echo "Error: " . $e->getMessage();
                                                }
                                            }
                                            // Send Email notification End
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $modelBrand = Brand::findOne($model->brand_id);
                    if (!empty($modelBrand) && $modelBrand instanceof Brand && in_array($modelBrand->status, [Brand::STATUS_PENDING_APPROVAL])) {
                        $modelBrand->status = Brand::STATUS_APPROVE;
                        $modelBrand->save(false);

                        $modelProductsBasedOnBrand = Product::find()->where(['brand_id' => $model->brand_id])->all();

                        $getUsers = [];
                        if (!empty($modelProductsBasedOnBrand)) {
                            foreach ($modelProductsBasedOnBrand as $keyProdBrand => $modelProductsBasedOnBrandRow) {
                                if (!empty($modelProductsBasedOnBrandRow) && $modelProductsBasedOnBrandRow instanceof Product) {
                                    $userDataModel = $modelProductsBasedOnBrandRow->user;
                                    if (!empty($getUsers)) {
                                        unset($getUsers);
                                    }
                                    // Send push notification Start
                                    $getUsers[] = $userDataModel;
                                    if (!empty($getUsers)) {
                                        foreach ($getUsers as $userROW) {
                                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                                $notificationText = "";
                                                if (!empty($userROW->userDevice)) {
                                                    $userDevice = $userROW->userDevice;

                                                    if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                        // Insert into notification.
                                                        $notificationText = "Brand has been approved, Which you have selected for your product.";
                                                        $modelNotification = new Notification();
                                                        $modelNotification->owner_id = Yii::$app->user->identity->id;
                                                        $modelNotification->notification_receiver_id = $userROW->id;
                                                        $modelNotification->ref_id = $modelProductsBasedOnBrandRow->id;
                                                        $modelNotification->notification_text = $notificationText;
                                                        $modelNotification->action = "product_brand_approve";
                                                        $modelNotification->ref_type = "products";
                                                        $modelNotification->product_id = $modelProductsBasedOnBrandRow->id;
                                                        $modelNotification->save(false);

                                                        $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                        if ($userDevice->device_platform == 'android') {
                                                            $notificationToken = array($userDevice->notification_token);
                                                            $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                                            $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
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
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    // Send push notification End

                                    // Send Email notification Start
                                    if (!empty($userDataModel) && $userDataModel instanceof User && !empty($userDataModel->email)) {
                                        try {
                                            $message = "Brand has been approved, Which you have selected for your product.";
                                            Yii::$app->mailer->compose('admin/general-info-send-to-user-html', ['userModel' => $userDataModel, 'message' => $message])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($userDataModel->email)
                                                ->setSubject('Brand approved!')
                                                ->send();
                                        } catch (HttpException $e) {
                                            echo "Error: " . $e->getMessage();
                                        }
                                    }
                                    // Send Email notification End
                                }
                            }
                        }
                    }
                }
                // Status of product is Approve then color/brand status approved END.
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'New Product updated successfully.');
            return $this->redirect(['new-product']);
        }

        return $this->render('new-product-update', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'size' => $size,
            'brand' => $brand,
            'color' => $color,
            'status' => $status,
            'shippingCountry' => $shippingCountry,
            'shippingPrice' => $shippingPrice
        ]);
    }

    /**
     * Deletes an existing Products model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status_id = ProductStatus::STATUS_ARCHIVED;
        $model->save(false);

        $modelCartItems = CartItem::find()->where(['product_id' => $id])->all();
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

        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Product deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionNewProductDelete($id)
    {
        $model = $this->findModel($id);
        $model->status_id = ProductStatus::STATUS_ARCHIVED;
        $model->save(false);

        $modelCartItems = CartItem::find()->where(['product_id' => $id])->all();
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

        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'New product deleted successfully.');
        return $this->redirect(['new-product']);
    }

    /**
     * Finds the Products model based on its primary key value.
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
     * @param $category_id
     * @return array
     */
    public function actionGetSubCategoryList($category_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $subCategoryList = [];
        $sizeList = [];
        if (!empty($category_id)) {
            $subCategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $category_id])->all(), 'id', 'name');
            if (!empty($subCategory)) {
                foreach ($subCategory as $key => $subCategoryRow) {
                    $subCategoryList[] = "<option value='" . $key . "'>" . $subCategoryRow . "</option>";
                }
            }

            $sizes = ArrayHelper::map(Sizes::find()->where(['product_category_id' => $category_id])->andWhere(['status' => Sizes::STATUS_ACTIVE])->all(), 'id', 'size');
            if (!empty($sizes)) {
                foreach ($sizes as $keySize => $sizesRow) {
                    $sizeList[] = "<option value='" . $keySize . "'>" . $sizesRow . "</option>";
                }
            }
        }
        return ['success' => true, 'dataList' => $subCategoryList, 'dataSizeList' => $sizeList];
    }

    /**
     * @return bool[]|false[]
     * @throws NotFoundHttpException
     */
    public function actionUpdateTopSelling()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $is_top_selling = Yii::$app->request->post('is_top_selling');

        $response = ['success' => false];
        if (!empty($id)) {
            $model = $this->findModel($id);
            if ($model) {
                $model->is_top_selling = (!empty($is_top_selling) && $is_top_selling == 1) ? Product::IS_TOP_SELLING_YES : Product::IS_TOP_SELLING_NO;
                $model->save(false);
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Product updated successfully.');
                $response = ['success' => true];
            }
        }
        return $response;
    }

    /**
     * @return bool[]|false[]
     * @throws NotFoundHttpException
     */
    public function actionUpdateTopTrending()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $is_top_trending = Yii::$app->request->post('is_top_trending');

        $response = ['success' => false];
        if (!empty($id)) {
            $model = $this->findModel($id);
            if ($model) {
                $model->is_top_trending = (!empty($is_top_trending) && $is_top_trending == 1) ? Product::IS_TOP_TRENDING_YES : Product::IS_TOP_TRENDING_NO;
                $model->save(false);
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Product updated successfully.');
                $response = ['success' => true];
            }
        }
        return $response;
    }

    /**
     * @param $id
     * @param $product_id
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteProductImage($id, $product_id)
    {
        $model = ProductImage::findOne($id);
        if (!empty($model)) {
            if (!empty($model->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $model->name)) {
                unlink(Yii::getAlias('@productImageRelativePath') . "/" . $model->name);
            }

            if (!empty($model->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $model->name)) {
                unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $model->name);
            }
            $model->delete();
            \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Product image deleted successfully.');
        }
        return $this->redirect(['update', 'id' => $product_id]);
    }

    /**
     * @param $id
     * @param $product_id
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteProductReceiptImage($id, $product_id)
    {
        $model = ProductReceipt::findOne($id);

        $receiptImagePathRelative = Yii::getAlias('@productReceiptImageRelativePath');
        $receiptThumbImagePathRelative = Yii::getAlias('@productReceiptImageThumbRelativePath');

        if (!empty($model)) {
            if (!empty($model->file) && file_exists($receiptImagePathRelative . "/" . $model->file)) {
                unlink($receiptImagePathRelative . "/" . $model->file);
            }

            if (!empty($model->name) && file_exists($receiptThumbImagePathRelative . "/" . $model->file)) {
                unlink($receiptThumbImagePathRelative . "/" . $model->file);
            }
            $model->delete();
            \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Product receipt deleted successfully.');
        }
        return $this->redirect(['update', 'id' => $product_id]);
    }

    /**
     * @return false|string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteMultiple()
    {
        $selection = (array)Yii::$app->request->post('ids');//typecasting
        $models = Product::find()->where(['IN', 'id', $selection])->all();

        if (!empty($models)) {
            foreach ($models as $model) {
                if (!empty($model) && $model instanceof Product) {
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
            }
            \Yii::$app->session->setFlash(\kartik\growl\Growl::TYPE_SUCCESS, 'Products deleted successfully!');
        } else {
            \Yii::$app->session->setFlash(\kartik\growl\Growl::TYPE_DANGER, 'Please try again!');
        }
        $respondse = ['success' => true];
        return json_encode($respondse);
    }

}
