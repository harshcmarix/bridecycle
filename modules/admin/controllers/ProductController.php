<?php

namespace app\modules\admin\controllers;

use app\models\Brand;
use app\models\Color;
use app\models\ProductCategory;
use app\models\ProductImage;
use app\models\ProductReceipt;
use app\models\ProductStatus;
use app\models\search\ProductSearch;
use app\models\ShippingCost;
use app\models\ShippingPrice;
use Imagine\Image\Box;
use Yii;
use app\models\Product;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\web\Controller;
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
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'view', 'delete', 'get-sub-category-list', 'update-top-selling', 'update-top-trending', 'delete-product-image', 'delete-product-receipt-image', 'delete-multiple'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update', 'view', 'delete', 'get-sub-category-list', 'update-top-selling', 'update-top-trending', 'delete-product-image', 'delete-product-receipt-image', 'delete-multiple'],
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
     * Creates a new Products model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = [];
        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');
        $color = ArrayHelper::map(Color::find()->all(), 'id', 'name');
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

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            }else{
                $model->option_show_only = '0';
            }

            $images = UploadedFile::getInstances($model, 'images');

            $model->option_color = implode(",", $postData['option_color']);
            if ($model->save()) {

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
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'Product created successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
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
        $model = $this->findModel($id);
        $oldUserId = $model->user_id;

        $shippingCountry = ArrayHelper::map(ShippingCost::find()->all(), 'id', 'name');
        $shippingPrice = $model->shippingCost;

        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $model->category_id])->all(), 'id', 'name');;
        if (empty($subcategory)) {
            $subcategory = ArrayHelper::map(ProductCategory::find()->where(['IS NOT', 'parent_category_id', null])->all(), 'id', 'name');;
        }
        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');
        $color = ArrayHelper::map(Color::find()->all(), 'id', 'name');
        $status = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');

        $postData = Yii::$app->request->post('Product');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (!empty($postData['option_show_only'])) {
                $model->option_show_only = $postData['option_show_only'];
            }else{
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

            if ($model->save(false)) {

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
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'Product updated successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
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
        if (!empty($model->productImages)) {
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
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Product deleted successfully.');
        return $this->redirect(['index']);
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
        if (!empty($category_id)) {
            $subCategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $category_id])->all(), 'id', 'name');
            if (!empty($subCategory)) {
                foreach ($subCategory as $key => $subCategoryRow) {
                    $subCategoryList[] = "<option value='" . $key . "'>" . $subCategoryRow . "</option>";
                }
            }
        }
        return ['success' => true, 'dataList' => $subCategoryList];
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
                    if (!empty($model->productImages)) {
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

                    if (!empty($model->productReceipt)) {
                        foreach ($model->productReceipt as $keys => $dataRow) {
                            if (!empty($dataRow) && $dataRow instanceof ProductReceipt) {
                                $receiptImagePathRelative = Yii::getAlias('@productReceiptImageRelativePath');
                                $receiptThumbImagePathRelative = Yii::getAlias('@productReceiptImageThumbRelativePath');

                                if (!empty($dataRow->file) && file_exists($receiptImagePathRelative . "/" . $dataRow->file)) {
                                    unlink($receiptImagePathRelative . "/" . $dataRow->file);
                                }

                                if (!empty($dataRow->file) && file_exists($receiptThumbImagePathRelative . "/" . $dataRow->file)) {
                                    unlink($receiptThumbImagePathRelative . "/" . $dataRow->file);
                                }
                            }
                        }
                    }
                    $model->delete();
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
