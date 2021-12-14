<?php

namespace app\modules\api\v2\controllers;

use Yii;
use app\models\ProductCategory;
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
 * ProductCategoryController implements the CRUD actions for ProductCategory model.
 */
class ProductCategoryController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\ProductCategory';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\ProductCategorySearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
            'update-image' => ['POST', 'OPTIONS'],
            'sub-category' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
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
            'only' => ['update-image', 'view', 'create', 'update', 'delete'],
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
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    /**
     * Lists all ProductCategory models.
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
     * @param $category_id
     * @return array|mixed|null
     * @throws NotFoundHttpException
     */
    public function actionSubCategory($category_id)
    {
        $model = ProductCategory::findOne($category_id);
        if (!$model instanceof ProductCategory) {
            throw new NotFoundHttpException('Product sub category doesn\'t exist.');
        }
        $moldelsSubcategory = [];
        if (!empty($model)) {
            $moldelsSubcategory = $model->children;
            if (!empty($moldelsSubcategory)) {
                foreach ($moldelsSubcategory as $key => $modelRow) {
                    if ($modelRow->status == 2) {
                        if (!empty($modelRow) && $modelRow instanceof ProductCategory && !empty($modelRow->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $modelRow->image)) {
                            $modelRow->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $modelRow->image;
                        } else {
                            $modelRow->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                        }
                    }
                }
            }
        }
        return $moldelsSubcategory;
    }

    /**
     * Displays a single ProductCategory model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = ProductCategory::findOne($id);
        if (!$model instanceof ProductCategory) {
            throw new NotFoundHttpException('Product category doesn\'t exist.');
        }
        if (!empty($model) && !empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $model->image)) {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $model->image;
        } else {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        }
        return $model;
    }

    /**
     * Creates a new ProductCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductCategory();
        $postData = Yii::$app->request->post();
        $productCategoryData['ProductCategory'] = $postData;
        $model->scenario = ProductCategory::SCENARIO_CREATE;
        $image = UploadedFile::getInstanceByName('image');
        $model->image = $image;
        if ($model->load($productCategoryData) && $model->validate()) {
            if (!empty($image)) {

                $uploadDirPath = Yii::getAlias('@productCategoryImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@productCategoryImageThumbRelativePath');

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $image->extension;
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            }
            $model->save();
        }

        if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $model->image)) {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $model->image;
        } else {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        }

        return $model;
    }

    /**
     * Updates an existing ProductCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = ProductCategory::findOne($id);
        if (!$model instanceof ProductCategory) {
            throw new NotFoundHttpException('Product category doesn\'t exist.');
        }

        $postData = Yii::$app->request->post();
        $productCategoryData['ProductCategory'] = $postData;

        if ($model->load($productCategoryData) && $model->validate()) {
            $model->save();
        }

        if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $model->image)) {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $model->image;
        } else {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        }

        return $model;
    }

    /**
     * @param $id
     * @return ProductCategory
     * @throws NotFoundHttpException
     */
    public function actionUpdateImage($id)
    {
        $model = ProductCategory::findOne($id);
        if (!$model instanceof ProductCategory) {
            throw new NotFoundHttpException('Product category doesn\'t exist.');
        }
        $oldFile = $model->image;
        $postData = Yii::$app->request->post();
        $productCategoryData['ProductCategory'] = $postData;
        $model->scenario = ProductCategory::SCENARIO_CREATE;
        $image = UploadedFile::getInstanceByName('image');
        if (!empty($image)) {
            $model->image = $image;
        } else {
            $model->image = '';
        }

        if ($model->load($productCategoryData) && $model->validate()) {
            if (!empty($image)) {
                $uploadDirPath = Yii::getAlias('@productCategoryImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@productCategoryImageThumbRelativePath');

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $image->extension;
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;

                if (!empty($oldFile)) {
                    //unlink real image if update
                    if (file_exists($uploadDirPath . '/' . $oldFile)) {
                        unlink($uploadDirPath . '/' . $oldFile);
                    }

                    //unlink thumb image if update
                    if (file_exists($uploadThumbDirPath . '/' . $oldFile)) {
                        unlink($uploadThumbDirPath . '/' . $oldFile);
                    }
                }
            }
            $model->save();
        }

        if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $model->image)) {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $model->image;
        } else {
            $model->image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        }

        return $model;
    }

    /**
     * Deletes an existing ProductCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = ProductCategory::findOne($id);
        if (!$model instanceof ProductCategory) {
            throw new NotFoundHttpException('Product category doesn\'t exist.');
        }
        if (!empty($model) && !empty($model->image)) {

            $uploadDirPath = Yii::getAlias('@productCategoryImageRelativePath');
            $uploadThumbDirPath = Yii::getAlias('@productCategoryImageThumbRelativePath');

            //unlink real image if update
            if (file_exists($uploadDirPath . '/' . $model->image)) {
                unlink($uploadDirPath . '/' . $model->image);
            }

            //unlink thumb image if update
            if (file_exists($uploadThumbDirPath . '/' . $model->image)) {
                unlink($uploadThumbDirPath . '/' . $model->image);
            }
        }
        $model->delete();
    }

    /**
     * Finds the ProductCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
