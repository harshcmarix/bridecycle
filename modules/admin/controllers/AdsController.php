<?php

namespace app\modules\admin\controllers;

use app\models\Brand;
use app\models\Product;
use app\models\ProductCategory;
use app\models\ProductStatus;
use Imagine\Image\Box;
use kartik\growl\Growl;
use Yii;
use app\models\Ads;
use app\models\search\AdsSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * AdsController implements the CRUD actions for Ads model.
 */
class AdsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Ads models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AdsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $product = ArrayHelper::map(Product::find()->where(['IN', 'status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]])->all(), 'id', 'name');
        $brand = ArrayHelper::map(Brand::find()->where(['IN', 'status', [Brand::STATUS_APPROVE]])->all(), 'id', 'name');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'product' => $product,
            'brand' => $brand
        ]);
    }

    /**
     * Displays a single Ads model.
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
     * Creates a new Ads model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ads();
        $model->scenario = Ads::SCENARIO_CREATE;
        $ad_image = UploadedFile::getInstance($model, 'image');

        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->andWhere(['IN', 'status', [ProductCategory::STATUS_APPROVE]])->all(), 'id', 'name');
        $subCategory = ArrayHelper::map(ProductCategory::find()->where(['>', 'parent_category_id', 0])->andWhere(['IN', 'status', [ProductCategory::STATUS_APPROVE]])->all(), 'id', 'name');
        $product = ArrayHelper::map(Product::find()->where(['IN', 'status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]])->all(), 'id', 'name');
        $brand = ArrayHelper::map(Brand::find()->where(['IN', 'status', [Brand::STATUS_APPROVE]])->all(), 'id', 'name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!empty($ad_image)) {
                $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $ad_image->extension;
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $ad_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Ads created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating Ads.");
            }
            return $this->redirect(['index']);
        }
        return $this->render('create', [
            'model' => $model,
            'category' => $category,
            'subCategory' => $subCategory,
            'product' => $product,
            'brand' => $brand,
        ]);
    }

    /**
     * Updates an existing Ads model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $old_image = $model->image;

        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->andWhere(['IN', 'status', [ProductCategory::STATUS_APPROVE]])->all(), 'id', 'name');
        $subCategory = ArrayHelper::map(ProductCategory::find()->where(['>', 'parent_category_id', 0])->andWhere(['IN', 'status', [ProductCategory::STATUS_APPROVE]])->all(), 'id', 'name');
        $product = ArrayHelper::map(Product::find()->where(['IN', 'status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]])->all(), 'id', 'name');
        $brand = ArrayHelper::map(Brand::find()->where(['IN', 'status', [Brand::STATUS_APPROVE]])->all(), 'id', 'name');

        $new_image = UploadedFile::getInstance($model, 'image');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            if (!empty($new_image)) {
                $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');
                $thumbImagePath = '';

                // Create product image upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }
                //unlink real image if update
                if (file_exists($uploadDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadDirPath . '/' . $old_image);
                }
                // Create product image thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }
                //unlink thumb image if update
                if (file_exists($uploadThumbDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadThumbDirPath . '/' . $old_image);
                }

                $ext = $new_image->extension;
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $new_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            } else {
                $model->image = $old_image;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Ads updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Ads.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'category' => $category,
            'subCategory' => $subCategory,
            'product' => $product,
            'brand' => $brand,
        ]);
    }

    /**
     * Deletes an existing Ads model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $image = $model->image;
        $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');

        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadDirPath . '/' . $image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadThumbDirPath . '/' . $image);
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Ads deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting Ads.");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Ads model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ads the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ads::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionImageDelete($id)
    {
        $model = $this->findModel($id);

        $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $model->image) && !empty($model->image)) {
            unlink($uploadDirPath . '/' . $model->image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $model->image) && !empty($model->image)) {
            unlink($uploadThumbDirPath . '/' . $model->image);
        }
        $model->image = null;
        if ($model->save()) {
            return Json::encode(['success' => 'image successfully deleted']);
        }
    }
}
