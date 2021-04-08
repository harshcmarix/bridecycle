<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Brand;
use app\models\search\BrandSearch;
use yii\web\{
    Controller,
    NotFoundHttpException,
    UploadedFile
};
use yii\imagine\Image;
use yii\filters\AccessControl;
use \yii\helpers\Json;

/**
 * BrandController implements the CRUD actions for Brand model.
 */
class BrandController extends Controller
{
    /**
     * {@inheritdoc}
     */
     public function behaviors()
    {
        return [
           'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update','view','delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update','view','delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Brand models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Brand model.
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
     * Creates a new Brand model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Brand();
        $model->scenario = Brand::SCENARIO_BRAND_CREATE;
        $brand_image = UploadedFile::getInstance($model, 'image');
        if ($model->load(Yii::$app->request->post())) {
            
            if(!empty($brand_image)){
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
                    $fileName = pathinfo($brand_image->name, PATHINFO_FILENAME);
                    $fileName = $fileName . '_' . time() . '.' . $ext;
                    // Upload profile picture
                    $brand_image->saveAs($uploadDirPath . '/' . $fileName);
                    // Create thumb of profile picture
                    $actualImagePath = $uploadDirPath . '/' . $fileName;
                    $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                    // p($actualImagePath);
                    Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                    // Insert profile picture name into database
                    $model->image = $fileName;
            }

            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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
            $model = $this->findModel($id);
            $old_image = $model->image;

            $new_image = UploadedFile::getInstance($model, 'image');

        if ($model->load(Yii::$app->request->post())) {
           
            if (!empty($new_image)) {
                    $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
                    $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
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
        ]);
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
        $model = $this->findModel($id);
         $image = $model->image;
         $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
         $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
         // unlink images with thumb
         if(file_exists($uploadDirPath.'/'.$image) && !empty($image)){
                unlink($uploadDirPath.'/'.$image);
         }
         if(file_exists($uploadThumbDirPath.'/'.$image) && !empty($image)){
                unlink($uploadThumbDirPath.'/'.$image);
         }
        $model->delete();

        return $this->redirect(['index']);
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
     /**
     * Deletes an existing image from perticular field.
     * If deletion is successful, success message will get in update page result.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionImageDelete($id){
        $model = $this->findModel($id);
        
         $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
         $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
         // unlink images with thumb
         if(file_exists($uploadDirPath.'/'.$model->image) && !empty($model->image)){
                unlink($uploadDirPath.'/'.$model->image);
         }
         if(file_exists($uploadThumbDirPath.'/'.$model->image) && !empty($model->image)){
                unlink($uploadThumbDirPath.'/'.$model->image);
         }
         $model->image = null;
        if($model->save()){
           return Json::encode(['success'=>'image successfully deleted']);
        }
    }
}
