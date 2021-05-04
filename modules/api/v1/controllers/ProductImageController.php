<?php

namespace app\modules\api\v1\controllers;

use Yii;
use app\models\ProductImage;
use app\models\ProductImageSearch;
use yii\web\Controller;
use yii\filters\VerbFilter;
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
 * ProductImageController implements the CRUD actions for ProductImage model.
 */
class ProductImageController extends ActiveController
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
           
            'update' => ['POST', 'OPTIONS'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete'],
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
     * Updates an existing ProductImage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $model = new ProductImage();
        $postData = Yii::$app->request->post();
        $productImage['ProductImage'] = $postData;       
        $images = UploadedFile::getInstancesByName('images');
        $model->name = $images;
        if ($model->load($productImage) && $model->validate()) {
                if (!empty($images)) {
                    $deleteImages  = ProductImage::find()->where(['product_id'=>$productImage['ProductImage']['product_id']]);
                    $modelsOldImg = $deleteImages->all();
                    if (!empty($modelsOldImg)) {
                        foreach ($modelsOldImg as $key => $modelOldImgRow) {
                            if (!empty($modelOldImgRow) && $modelOldImgRow instanceof ProductImage) {

                                if (!empty($modelOldImgRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $modelOldImgRow->name)) {
                                    unlink(Yii::getAlias('@productImageRelativePath') . "/" . $modelOldImgRow->name);
                                }

                                if (!empty($modelOldImgRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $modelOldImgRow->name)) {
                                    unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $modelOldImgRow->name);
                                }
                               
                            }
                        }
                       ProductImage::deleteAll(['product_id'=>$productImage['ProductImage']['product_id']]);
                    }
                    $arrayImage = [];
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

                        $modelImage->product_id = $productImage['ProductImage']['product_id'];
                        $modelImage->name = $fileName;
                        $modelImage->save(false);
                        $arrayImage[] =$modelImage; 
                    }
                }
        }
        $thumbImagePath = Yii::getAlias('@productImageThumbAbsolutePath');
        foreach($arrayImage as $images){
            if(!empty($images->name)){
                $images->name = Yii::$app->request->getHostInfo() . $thumbImagePath.'/'.$images->name;
            }
        }
        return $arrayImage;
    }

    /**
     * Deletes an existing ProductImage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = ProductImage::findOne($id);
        if (empty($model) && !$model instanceof ProductImage) {
            throw new NotFoundHttpException('Product image doesn\'t exist.');
        }

        if (!empty($model->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $model->name)) {
            unlink(Yii::getAlias('@productImageRelativePath') . "/" . $model->name);
        }

        if (!empty($model->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $model->name)) {
            unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $model->name);
        }
        $model->delete();
    }

    /**
     * Finds the ProductImage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductImage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductImage::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}