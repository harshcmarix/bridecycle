<?php

namespace app\modules\api\v2\controllers;

use Yii;
use app\models\Brand;
use yii\imagine\Image;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;


/**
 * BrandController implements the CRUD actions for Brand model.
 */
class BrandController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Brand';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\BrandSearch';

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
            'delete' => ['POST', 'DELETE'],
            'update-brand-image' => ['POST', 'OPTIONS'],
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
            'only' => ['index-list', 'view', 'create', 'update', 'delete', 'update-brand-image'],//index
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
     * Lists all Brand models.
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
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndexList()
    {
        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        $from = "";
        $product_id = "";
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
            $from = (!empty(Yii::$app->request->get('from')) && Yii::$app->request->get('from') == 'edit_product') ? Yii::$app->request->get('from') : "";
            $product_id = (!empty(Yii::$app->request->get('product_id'))) ? Yii::$app->request->get('product_id') : "";
        }
        return $model->search($requestParams, $from, $product_id, Yii::$app->user->identity->id);
    }

    /**
     * Creates a new Brand model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Brand();

        $postData = \Yii::$app->request->post();
        $brandData['Brand'] = $postData;

        $brand_image = UploadedFile::getInstanceByName('image');
        $model->image = $brand_image;
        if ($model->load($brandData) && $model->validate()) {

            if (!empty($brand_image)) {
                $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $brand_image->extension;
                $fileName = time() . rand(99999, 88888) . '.' . $ext;

                // Upload profile picture
                $brand_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            }

            if ($model->save()) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageThumbRelativePath') . '/' . $model->image)) {
                    $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $model->image;
                }
                $model->image = $brandImage;
            }
        }
        return $model;
    }

    /**
     * Updates an existing Brand model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Brand::findOne($id);
        if (!$model instanceof Brand) {
            throw new NotFoundHttpException('Brand doesn\'t exist.');
        }
        $postData = \Yii::$app->request->post();

        $brandData['Brand'] = $postData;

        if ($model->load($brandData) && $model->validate()) {
            if ($model->save(false)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageThumbRelativePath') . '/' . $model->image)) {
                    $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $model->image;
                }
                $model->image = $brandImage;
            }
        }
        return $model;
    }

    /**
     * @param $id
     * @return Brand
     * @throws NotFoundHttpException
     */
    public function actionUpdateBrandImage($id)
    {
        $model = Brand::findOne($id);
        if (!$model instanceof Brand) {
            throw new NotFoundHttpException('Brand doesn\'t exist.');
        }
        $postData = \Yii::$app->request->post();

        $brandData['Brand'] = $postData;
        $model->scenario = Brand::SCENARIO_CREATE_API;
        $oldFile = $model->image;

        $image = UploadedFile::getInstanceByName('image');
        $model->image = $image;
        if ($model->load($brandData) && $model->validate()) {

            if (!empty($image)) {
                $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');

                // Create product upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create product thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $image->extension;
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                $image->saveAs($uploadDirPath . '/' . $fileName);
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                $model->image = $fileName;

                if (!empty($oldFile) && file_exists($uploadDirPath . "/" . $oldFile)) {
                    unlink($uploadDirPath . "/" . $oldFile);
                }

                if (!empty($oldFile) && file_exists($uploadThumbDirPath . "/" . $oldFile)) {
                    unlink($uploadThumbDirPath . "/" . $oldFile);
                }
            }
            if ($model->save()) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageThumbRelativePath') . '/' . $model->image)) {
                    $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $model->image;
                }
                $model->image = $brandImage;
            }
        }
        return $model;
    }

    /**
     * Deletes an existing Brand model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = Brand::findOne($id);
        if (!$model instanceof Brand) {
            throw new NotFoundHttpException('Brand doesn\'t exist.');
        }
        $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');

        if (!empty($model->image) && file_exists($uploadDirPath . "/" . $model->image)) {
            unlink($uploadDirPath . "/" . $model->image);
        }

        if (!empty($model->image) && file_exists($uploadThumbDirPath . "/" . $model->image)) {
            unlink($uploadThumbDirPath . "/" . $model->image);
        }

        $model->delete();
    }

    /**
     * Finds the Brand model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Brand the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Brand::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
