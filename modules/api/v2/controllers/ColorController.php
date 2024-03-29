<?php

namespace app\modules\api\v2\controllers;

use Yii;
use app\models\Color;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

/**
 * ColorController implements the CRUD actions for Color model.
 */
class ColorController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Color';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\ColorSearch';

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
            'only' => ['view', 'create', 'update', 'delete'],
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

        $from = "";
        $product_id = "";
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
            $from = (!empty(Yii::$app->request->get('from')) && Yii::$app->request->get('from') == 'edit_product') ? Yii::$app->request->get('from') : "";
            $product_id = (!empty(Yii::$app->request->get('product_id'))) ? Yii::$app->request->get('product_id') : "";
        }
        return $model->search($requestParams, $from, $product_id);
    }

    /**
     * Displays a single Color model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = Color::findOne($id);
        if (!$model instanceof Color) {
            throw new NotFoundHttpException(getValidationErrorMsg('color_not_exist', Yii::$app->language));
        }
        return $model;
    }

    /**
     * Creates a new Color model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Color();

        $postData = Yii::$app->request->post();
        $colorData['Color'] = $postData;

        if ($model->load($colorData) && $model->validate()) {

            if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'de' || \Yii::$app->language == 'german') {
                $model->german_name = $model->name;
                $model->name = NULL;
            }

            if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'en' || \Yii::$app->language == 'english') {
                $model->german_name = NULL;
            }

            $model->save(false);

            $colorName = "";
            if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'en' || \Yii::$app->language == 'english') {
                if (!empty($model->name)) {
                    $colorName = $model->name;
                } elseif (empty($model->name) && !empty($model->german_name)) {
                    $colorName = $model->german_name;
                }
            }

            if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'de' || \Yii::$app->language == 'german') {
                if (!empty($model->german_name)) {
                    $colorName = $model->german_name;
                } elseif (empty($model->german_name) && !empty($model->name)) {
                    $colorName = $model->name;
                }
            }
            $model->name = $colorName;
        }

        return $model;
    }

    /**
     * Updates an existing Color model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Color::findOne($id);
        if (!$model instanceof Color) {
            throw new NotFoundHttpException(getValidationErrorMsg('color_not_exist', Yii::$app->language));
        }
        $postData = Yii::$app->request->post();
        $colorData['Color'] = $postData;

        if ($model->load($colorData) && $model->validate()) {
            $model->save(false);

            $colorName = "";
            if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'en' || \Yii::$app->language == 'english') {
                if (!empty($model->name)) {
                    $colorName = $model->name;
                } elseif (empty($model->name) && !empty($model->german_name)) {
                    $colorName = $model->german_name;
                }
            }

            if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'de' || \Yii::$app->language == 'german') {
                if (!empty($model->german_name)) {
                    $colorName = $model->german_name;
                } elseif (empty($model->german_name) && !empty($model->name)) {
                    $colorName = $model->name;
                }
            }
            $model->name = $colorName;
        }
        return $model;
    }

    /**
     * Finds the Color model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Color the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Color::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist', Yii::$app->language));
    }

}
