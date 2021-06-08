<?php

namespace app\modules\api\v1\controllers;

use app\models\ProductReceipt;
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
    public $searchModelClass = 'app\modules\api\v1\models\search\ProductSearch';


    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete',],
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

                        chmod($actualImagePathReceipt,0777);

                        Image::thumbnail($actualImagePathReceipt, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePathReceipt, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        chmod($thumbImagePathReceipt,0777);

                        // Insert product picture name into database
                        $modelImageReceipt->product_id = $model->id;
                        $modelImageReceipt->file = $fileNameReceipt;
                        $modelImageReceipt->save(false);
                    }
                }

                $modelAddress = new UserAddress();
                $addressData['UserAddress'] = $postData;
                $modelAddress->user_id = Yii::$app->user->identity->id;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                    $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->zip_code;
                    if ($modelAddress->save()) {
                        $model->address_id = $modelAddress->id;
                        $model->save(false);
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
            throw new NotFoundHttpException('Product doesn\'t exist.');
        }
        $postData = Yii::$app->request->post();
        $productData['Product'] = $postData;

        $model->gender = Product::GENDER_FOR_FEMALE;

        if ($model->load($productData) && $model->validate()) {

            if ($model->save(false)) {

                $modelAddress = $model->address;
                if (empty($modelAddress)) {
                    $modelAddress = new UserAddress();
                    $modelAddress->user_id = Yii::$app->user->identity->id;
                }
                $addressData['UserAddress'] = $postData;
                $modelAddress->type = UserAddress::TYPE_SHOP;
                if ($modelAddress->load($addressData) && $modelAddress->validate()) {
                    $modelAddress->address = $modelAddress->street . "," . $modelAddress->city . "," . $modelAddress->zip_code;
                    if ($modelAddress->save()) {
                        $model->address_id = $modelAddress->id;
                        $model->save(false);
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
}
