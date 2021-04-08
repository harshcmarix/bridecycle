<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\ProductCategory;
use app\models\search\ProductCategorySearch;
use yii\web\{
    Controller,
    UploadedFile,
    NotFoundHttpException
};
use yii\imagine\Image;
use yii\filters\AccessControl;
use \yii\helpers\Json;

/**
 * ProductCategoryController implements the CRUD actions for ProductCategory model.
 */
class ProductCategoryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'view', 'delete', 'picture'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update', 'view', 'delete', 'picture'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all ProductCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductCategorySearch();
        $parent_category = ProductCategory::find()->where(['parent_category_id' => null])->all();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'parent_category' => $parent_category,
        ]);
    }

    /**
     * Displays a single ProductCategory model.
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
     * Creates a new ProductCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductCategory();
        $parent_category = ProductCategory::find()->where(['parent_category_id' => null])->all();
        $model->scenario = ProductCategory::SCENARIO_PRODUCT_CATEGORY_CREATE;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        $img = UploadedFile::getInstance($model, 'image');
        if ($model->load(Yii::$app->request->post()) ) {

            if (!empty($img)) {
                $uploadDirPath = Yii::getAlias('@productCategoryImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@productCategoryImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $img->extension;
                $fileName = pathinfo($img->name, PATHINFO_FILENAME);
                $fileName = $fileName . '_' . time() . '.' . $ext;
                // Upload profile picture
                $img->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                // p($actualImagePath);
                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            }

            $model->save();
            return $this->redirect(['view', 'id' => $model->id,]);
        }

        return $this->render('create', [
            'model' => $model,
            'parent_category' => $parent_category
        ]);
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
        $model = $this->findModel($id);
        $exist_image = '';
        $parent_category = ProductCategory::find()->where(['parent_category_id' => null])->all();
        $old_image = $model->image;
        // p($old_image);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $new_image = UploadedFile::getInstance($model, 'image');
            if (!empty($new_image)) {
                $uploadDirPath = Yii::getAlias('@productCategoryImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@productCategoryImageThumbRelativePath');
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
                $fileName = pathinfo($new_image->name, PATHINFO_FILENAME);
                $fileName = $fileName . '_' . time() . '.' . $ext;
                // Upload profile picture
                $new_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                // p($actualImagePath);
                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;

            } else {
                $model->image = $old_image;
            }
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'parent_category' => $parent_category,
            // 'exist_image'=> $exist_image
        ]);
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
        $model = $this->findModel($id);
        $image = $model->image;
        $uploadDirPath = Yii::getAlias('@productCategoryImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@productCategoryImageThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadDirPath . '/' . $image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadThumbDirPath . '/' . $image);
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    public function actionImageDelete($id)
    {
        $model = $this->findModel($id);

        $uploadDirPath = Yii::getAlias('@productCategoryImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@productCategoryImageThumbRelativePath');
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
