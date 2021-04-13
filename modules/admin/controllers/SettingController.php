<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Setting;
use app\models\search\SettingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\base\DynamicModel;
use kartik\growl\Growl;

/**
 * SettingController implements the CRUD actions for Setting model.
 */
class SettingController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
           'access' => [
                'class' => AccessControl::className(),
                'only' => ['update'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
     /**
     * update the Setting model based on its primary key value.
     * redirects index page
     */
    public function actionIndex()
    {
        $model = new DynamicModel([
            'transaction_fees', 'km_range'
        ]);
        $model->addRule(['transaction_fees', 'km_range'], 'integer');

        $model_fees = Setting::findOne(Yii::$app->params['transaction_fees']);
        if($model_fees instanceof Setting)
        {
                $model->transaction_fees = $model_fees->option_value;
        }
        $model_km = Setting::findOne(Yii::$app->params['km_range']);
        if($model_km instanceof Setting)
        {
                $model->km_range = $model_km->option_value;
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
                $model_fees->option_value = !empty($model->transaction_fees) ? $model->transaction_fees : null;
                $model_km->option_value = !empty($model->km_range) ? $model->km_range : null;

                if($model_km->save(false) && $model_fees->save(false)){
                     Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Settings updated successfully.");
                }else{
                     Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Settings.");
                }
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
    
    /**
     * Finds the Setting model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Setting the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Setting::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
