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
                'class' => AccessControl::class,
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
        // for transection fees
        $model_fees = Setting::findOne(Yii::$app->params['transaction_fees']);
        if (empty($model_fees)) {
            $fees = new Setting();
            $fees->option_key = Yii::$app->params['transaction_fees']['option_key'];
            $fees->save();
        }
        if ($model_fees instanceof Setting) {
            $model->transaction_fees = $model_fees->option_value;
        }
        // for km range
        $model_km = Setting::findOne(Yii::$app->params['km_range']);
        if (empty($model_km)) {
            $km = new Setting();
            $km->option_key = Yii::$app->params['km_range']['option_key'];
            $km->save();
        }

        if ($model_km instanceof Setting) {
            $model->km_range = $model_km->option_value;
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {


            $model_fees->option_value = !empty($model->transaction_fees) ? $model->transaction_fees : null;
            $model_km->option_value = !empty($model->km_range) ? $model->km_range : null;

            if ($model_km->save(false) && $model_fees->save(false)) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Settings updated successfully.");
            } else {
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
