<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Banner;
use app\models\search\BannerSearch;
use yii\web\{
    Controller,
    NotFoundHttpException,
    UploadedFile
};
use yii\filters\AccessControl;
use yii\imagine\Image;
use kartik\growl\Growl;
use \yii\helpers\Json;

/**
 * BannerController implements the CRUD actions for Banner model.
 */
class BannerController extends Controller
{
    /**
     * {@inheritdoc}
     */
     public function behaviors()
    {
        return [
           'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update','view','delete','image-delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update','view','delete','image-delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Banner models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BannerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Banner model.
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
     * Creates a new Banner model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Banner();
        $model->scenario = Banner::SCENARIO_CREATE;
        $banner_image = UploadedFile::getInstance($model, 'image');
        if ($model->load(Yii::$app->request->post())) {
            
            if(!empty($banner_image)){
                $uploadDirPath = Yii::getAlias('@bannerImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@bannerImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                    $ext = $banner_image->extension;
                    $fileName = pathinfo($banner_image->name, PATHINFO_FILENAME);
                    $fileName = $fileName . '_' . time() . '.' . $ext;
                    // Upload profile picture
                    $banner_image->saveAs($uploadDirPath . '/' . $fileName);
                    // Create thumb of profile picture
                    $actualImagePath = $uploadDirPath . '/' . $fileName;
                    $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                    // p($actualImagePath);
                    Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                    // Insert profile picture name into database
                    $model->image = $fileName;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Banner created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating Banner.");
            }
             return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Banner model.
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
                    $uploadDirPath = Yii::getAlias('@bannerImageRelativePath');
                    $uploadThumbDirPath = Yii::getAlias('@bannerImageThumbRelativePath');
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
            
            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Banner updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Banner.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Banner model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
         $model = $this->findModel($id);
         $image = $model->image;
         $uploadDirPath = Yii::getAlias('@bannerImageRelativePath');
         $uploadThumbDirPath = Yii::getAlias('@bannerImageThumbRelativePath');
         // unlink images with thumb
         if(file_exists($uploadDirPath.'/'.$image) && !empty($image)){
                unlink($uploadDirPath.'/'.$image);
         }
         if(file_exists($uploadThumbDirPath.'/'.$image) && !empty($image)){
                unlink($uploadThumbDirPath.'/'.$image);
         }
        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Banner deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting Banner.");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Banner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Banner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Banner::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionImageDelete($id)
    {
         $model = $this->findModel($id);
         $uploadDirPath = Yii::getAlias('@bannerImageRelativePath');
         $uploadThumbDirPath = Yii::getAlias('@bannerImageThumbRelativePath');
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
