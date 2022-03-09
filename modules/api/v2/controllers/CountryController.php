<?php

namespace app\modules\api\v2\controllers;

use app\models\Country;
use app\models\Product;
use app\models\ShippingCost;
use app\models\ShippingPrice;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class CountryController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Country';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\CountrySearch';

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
            'check-feasibility' => ['POST', 'OPTIONS'],
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
     * Displays a single Country model.
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
     * Creates a new Country model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Country();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Country model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Country model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Country model based on its primary key  value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Country the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Country::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCheckFeasibility()
    {
        $post = \Yii::$app->request->post();
        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('product_id_required', Yii::$app->language));
        }
        if (empty($post) || empty($post['zip_code'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('post_code_required', Yii::$app->language));
        }

        $postcode = $post['zip_code'];
        $modelProduct = Product::findOne($post['product_id']);
        if (!$modelProduct instanceof Product) {
            throw new NotFoundHttpException(getValidationErrorMsg('product_not_exist', Yii::$app->language));
        }

        $modelsShippingCountry = [];
        if (!empty($modelProduct) && $modelProduct instanceof Product) {
            $modelsShippingCountry = ShippingPrice::find()->where(['product_id' => $modelProduct->id])->all();
        }

        $result = $this->getCountryAndGoogleCodeFromZipCode($postcode);

        // Canada = north america
        // usa = south america
        // europe = europe
        // asia = asia
        // other = all remaining are consider as other

        //$continent = 'Europe';
        $continent = '';

        if (!empty($result) && !empty($result['continent'])) {

            if ($result['continent'] == 'North America') {
                $continent = ShippingCost::CONTINENT_CANADA;
            } elseif ($result['continent'] == 'South America') {
                $continent = ShippingCost::CONTINENT_USA;
            } elseif ($result['continent'] == 'Europe') {
                $continent = ShippingCost::CONTINENT_EUROPE;
            } elseif ($result['continent'] == 'Asia') {
                $continent = ShippingCost::CONTINENT_ASIA;
            } else {
                $continent = ShippingCost::CONTINENT_OTHER;
            }
        }
        if ($result['continent'] == 'Europe') {
            $continent = 'Europe';
        } else {
            $continent = ShippingCost::CONTINENT_OTHER;
        }


        $data['is_feasible'] = 0;
        $data['shipping_cost'] = 0.0;
        $data['shipping_country'] = "";
        if (!empty($result) && !empty($result['country_name']) && !empty($result['country_google_code']) && !empty($modelsShippingCountry)) {
            foreach ($modelsShippingCountry as $key => $modelShippingCountryRow) {
                if (!empty($modelShippingCountryRow) && $modelShippingCountryRow instanceof ShippingPrice) {
                    if (!empty($modelShippingCountryRow) && !empty($modelShippingCountryRow->shippingCost) && $modelShippingCountryRow->shippingCost instanceof ShippingCost) {
                        if (strtolower($modelShippingCountryRow->shippingCost->name) == strtolower($continent)) {
                            $data['is_feasible'] = 1;
                            $data['shipping_cost'] = $modelShippingCountryRow->price;
                            $data['shipping_country'] = "(" . $continent . ")";
                        }
                    }
                }
            }
        }
        $data['shipping_cost_symbol'] = mb_substr(Yii::$app->formatter->asCurrency($data['shipping_cost']), 0, 1);
        $output[] = $data;
        return $output;
    }

    /**
     * @param $zipcode
     * @return array
     */
    public function getCountryAndGoogleCodeFromZipCode($zipcode)
    {
        $data['country_name'] = $data['country_google_code'] = $data['continent'] = "";
        if (!empty($zipcode)) {
            $address = urlencode($zipcode);
            $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?key=' . Yii::$app->params['google_map_api_key'] . '&address=' . $address . '&sensor=false');
            $obj = json_decode($geocode);

            if (!empty($obj) && !empty($obj->results) && !empty($obj->results[0]) && !empty($obj->results[0]->address_components)) {
                foreach ($obj->results[0]->address_components as $addressComponentRow) {
                    if (!empty($addressComponentRow) && !empty($addressComponentRow->types) && is_array($addressComponentRow->types)) {
                        if (!empty($addressComponentRow->types[0]) && $addressComponentRow->types[0] == 'country') {
                            $data['country_name'] = $addressComponentRow->long_name;
                            $data['country_google_code'] = $addressComponentRow->short_name;
                            $data['continent'] = $this->CountryToContinent($addressComponentRow->short_name);
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param $country
     * @return string
     */
    public function CountryToContinent($country)
    {
        $continent = '';
        if ($country == 'AF') $continent = 'Asia';
        if ($country == 'AX') $continent = 'Europe';
        if ($country == 'AL') $continent = 'Europe';
        if ($country == 'DZ') $continent = 'Africa';
        if ($country == 'AS') $continent = 'Oceania';
        if ($country == 'AD') $continent = 'Europe';
        if ($country == 'AO') $continent = 'Africa';
        if ($country == 'AI') $continent = 'North America';
        if ($country == 'AQ') $continent = 'Antarctica';
        if ($country == 'AG') $continent = 'North America';
        if ($country == 'AR') $continent = 'South America';
        if ($country == 'AM') $continent = 'Asia';
        if ($country == 'AW') $continent = 'North America';
        if ($country == 'AU') $continent = 'Oceania';
        if ($country == 'AT') $continent = 'Europe';
        if ($country == 'AZ') $continent = 'Asia';
        if ($country == 'BS') $continent = 'North America';
        if ($country == 'BH') $continent = 'Asia';
        if ($country == 'BD') $continent = 'Asia';
        if ($country == 'BB') $continent = 'North America';
        if ($country == 'BY') $continent = 'Europe';
        if ($country == 'BE') $continent = 'Europe';
        if ($country == 'BZ') $continent = 'North America';
        if ($country == 'BJ') $continent = 'Africa';
        if ($country == 'BM') $continent = 'North America';
        if ($country == 'BT') $continent = 'Asia';
        if ($country == 'BO') $continent = 'South America';
        if ($country == 'BA') $continent = 'Europe';
        if ($country == 'BW') $continent = 'Africa';
        if ($country == 'BV') $continent = 'Antarctica';
        if ($country == 'BR') $continent = 'South America';
        if ($country == 'IO') $continent = 'Asia';
        if ($country == 'VG') $continent = 'North America';
        if ($country == 'BN') $continent = 'Asia';
        if ($country == 'BG') $continent = 'Europe';
        if ($country == 'BF') $continent = 'Africa';
        if ($country == 'BI') $continent = 'Africa';
        if ($country == 'KH') $continent = 'Asia';
        if ($country == 'CM') $continent = 'Africa';
        if ($country == 'CA') $continent = 'North America';
        if ($country == 'CV') $continent = 'Africa';
        if ($country == 'KY') $continent = 'North America';
        if ($country == 'CF') $continent = 'Africa';
        if ($country == 'TD') $continent = 'Africa';
        if ($country == 'CL') $continent = 'South America';
        if ($country == 'CN') $continent = 'Asia';
        if ($country == 'CX') $continent = 'Asia';
        if ($country == 'CC') $continent = 'Asia';
        if ($country == 'CO') $continent = 'South America';
        if ($country == 'KM') $continent = 'Africa';
        if ($country == 'CD') $continent = 'Africa';
        if ($country == 'CG') $continent = 'Africa';
        if ($country == 'CK') $continent = 'Oceania';
        if ($country == 'CR') $continent = 'North America';
        if ($country == 'CI') $continent = 'Africa';
        if ($country == 'HR') $continent = 'Europe';
        if ($country == 'CU') $continent = 'North America';
        if ($country == 'CY') $continent = 'Asia';
        if ($country == 'CZ') $continent = 'Europe';
        if ($country == 'DK') $continent = 'Europe';
        if ($country == 'DJ') $continent = 'Africa';
        if ($country == 'DM') $continent = 'North America';
        if ($country == 'DO') $continent = 'North America';
        if ($country == 'EC') $continent = 'South America';
        if ($country == 'EG') $continent = 'Africa';
        if ($country == 'SV') $continent = 'North America';
        if ($country == 'GQ') $continent = 'Africa';
        if ($country == 'ER') $continent = 'Africa';
        if ($country == 'EE') $continent = 'Europe';
        if ($country == 'ET') $continent = 'Africa';
        if ($country == 'FO') $continent = 'Europe';
        if ($country == 'FK') $continent = 'South America';
        if ($country == 'FJ') $continent = 'Oceania';
        if ($country == 'FI') $continent = 'Europe';
        if ($country == 'FR') $continent = 'Europe';
        if ($country == 'GF') $continent = 'South America';
        if ($country == 'PF') $continent = 'Oceania';
        if ($country == 'TF') $continent = 'Antarctica';
        if ($country == 'GA') $continent = 'Africa';
        if ($country == 'GM') $continent = 'Africa';
        if ($country == 'GE') $continent = 'Asia';
        if ($country == 'DE') $continent = 'Europe';
        if ($country == 'GH') $continent = 'Africa';
        if ($country == 'GI') $continent = 'Europe';
        if ($country == 'GR') $continent = 'Europe';
        if ($country == 'GL') $continent = 'North America';
        if ($country == 'GD') $continent = 'North America';
        if ($country == 'GP') $continent = 'North America';
        if ($country == 'GU') $continent = 'Oceania';
        if ($country == 'GT') $continent = 'North America';
        if ($country == 'GG') $continent = 'Europe';
        if ($country == 'GN') $continent = 'Africa';
        if ($country == 'GW') $continent = 'Africa';
        if ($country == 'GY') $continent = 'South America';
        if ($country == 'HT') $continent = 'North America';
        if ($country == 'HM') $continent = 'Antarctica';
        if ($country == 'VA') $continent = 'Europe';
        if ($country == 'HN') $continent = 'North America';
        if ($country == 'HK') $continent = 'Asia';
        if ($country == 'HU') $continent = 'Europe';
        if ($country == 'IS') $continent = 'Europe';
        if ($country == 'IN') $continent = 'Asia';
        if ($country == 'ID') $continent = 'Asia';
        if ($country == 'IR') $continent = 'Asia';
        if ($country == 'IQ') $continent = 'Asia';
        if ($country == 'IE') $continent = 'Europe';
        if ($country == 'IM') $continent = 'Europe';
        if ($country == 'IL') $continent = 'Asia';
        if ($country == 'IT') $continent = 'Europe';
        if ($country == 'JM') $continent = 'North America';
        if ($country == 'JP') $continent = 'Asia';
        if ($country == 'JE') $continent = 'Europe';
        if ($country == 'JO') $continent = 'Asia';
        if ($country == 'KZ') $continent = 'Asia';
        if ($country == 'KE') $continent = 'Africa';
        if ($country == 'KI') $continent = 'Oceania';
        if ($country == 'KP') $continent = 'Asia';
        if ($country == 'KR') $continent = 'Asia';
        if ($country == 'KW') $continent = 'Asia';
        if ($country == 'KG') $continent = 'Asia';
        if ($country == 'LA') $continent = 'Asia';
        if ($country == 'LV') $continent = 'Europe';
        if ($country == 'LB') $continent = 'Asia';
        if ($country == 'LS') $continent = 'Africa';
        if ($country == 'LR') $continent = 'Africa';
        if ($country == 'LY') $continent = 'Africa';
        if ($country == 'LI') $continent = 'Europe';
        if ($country == 'LT') $continent = 'Europe';
        if ($country == 'LU') $continent = 'Europe';
        if ($country == 'MO') $continent = 'Asia';
        if ($country == 'MK') $continent = 'Europe';
        if ($country == 'MG') $continent = 'Africa';
        if ($country == 'MW') $continent = 'Africa';
        if ($country == 'MY') $continent = 'Asia';
        if ($country == 'MV') $continent = 'Asia';
        if ($country == 'ML') $continent = 'Africa';
        if ($country == 'MT') $continent = 'Europe';
        if ($country == 'MH') $continent = 'Oceania';
        if ($country == 'MQ') $continent = 'North America';
        if ($country == 'MR') $continent = 'Africa';
        if ($country == 'MU') $continent = 'Africa';
        if ($country == 'YT') $continent = 'Africa';
        if ($country == 'MX') $continent = 'North America';
        if ($country == 'FM') $continent = 'Oceania';
        if ($country == 'MD') $continent = 'Europe';
        if ($country == 'MC') $continent = 'Europe';
        if ($country == 'MN') $continent = 'Asia';
        if ($country == 'ME') $continent = 'Europe';
        if ($country == 'MS') $continent = 'North America';
        if ($country == 'MA') $continent = 'Africa';
        if ($country == 'MZ') $continent = 'Africa';
        if ($country == 'MM') $continent = 'Asia';
        if ($country == 'NA') $continent = 'Africa';
        if ($country == 'NR') $continent = 'Oceania';
        if ($country == 'NP') $continent = 'Asia';
        if ($country == 'AN') $continent = 'North America';
        if ($country == 'NL') $continent = 'Europe';
        if ($country == 'NC') $continent = 'Oceania';
        if ($country == 'NZ') $continent = 'Oceania';
        if ($country == 'NI') $continent = 'North America';
        if ($country == 'NE') $continent = 'Africa';
        if ($country == 'NG') $continent = 'Africa';
        if ($country == 'NU') $continent = 'Oceania';
        if ($country == 'NF') $continent = 'Oceania';
        if ($country == 'MP') $continent = 'Oceania';
        if ($country == 'NO') $continent = 'Europe';
        if ($country == 'OM') $continent = 'Asia';
        if ($country == 'PK') $continent = 'Asia';
        if ($country == 'PW') $continent = 'Oceania';
        if ($country == 'PS') $continent = 'Asia';
        if ($country == 'PA') $continent = 'North America';
        if ($country == 'PG') $continent = 'Oceania';
        if ($country == 'PY') $continent = 'South America';
        if ($country == 'PE') $continent = 'South America';
        if ($country == 'PH') $continent = 'Asia';
        if ($country == 'PN') $continent = 'Oceania';
        if ($country == 'PL') $continent = 'Europe';
        if ($country == 'PT') $continent = 'Europe';
        if ($country == 'PR') $continent = 'North America';
        if ($country == 'QA') $continent = 'Asia';
        if ($country == 'RE') $continent = 'Africa';
        if ($country == 'RO') $continent = 'Europe';
        if ($country == 'RU') $continent = 'Europe';
        if ($country == 'RW') $continent = 'Africa';
        if ($country == 'BL') $continent = 'North America';
        if ($country == 'SH') $continent = 'Africa';
        if ($country == 'KN') $continent = 'North America';
        if ($country == 'LC') $continent = 'North America';
        if ($country == 'MF') $continent = 'North America';
        if ($country == 'PM') $continent = 'North America';
        if ($country == 'VC') $continent = 'North America';
        if ($country == 'WS') $continent = 'Oceania';
        if ($country == 'SM') $continent = 'Europe';
        if ($country == 'ST') $continent = 'Africa';
        if ($country == 'SA') $continent = 'Asia';
        if ($country == 'SN') $continent = 'Africa';
        if ($country == 'RS') $continent = 'Europe';
        if ($country == 'SC') $continent = 'Africa';
        if ($country == 'SL') $continent = 'Africa';
        if ($country == 'SG') $continent = 'Asia';
        if ($country == 'SK') $continent = 'Europe';
        if ($country == 'SI') $continent = 'Europe';
        if ($country == 'SB') $continent = 'Oceania';
        if ($country == 'SO') $continent = 'Africa';
        if ($country == 'ZA') $continent = 'Africa';
        if ($country == 'GS') $continent = 'Antarctica';
        if ($country == 'ES') $continent = 'Europe';
        if ($country == 'LK') $continent = 'Asia';
        if ($country == 'SD') $continent = 'Africa';
        if ($country == 'SR') $continent = 'South America';
        if ($country == 'SJ') $continent = 'Europe';
        if ($country == 'SZ') $continent = 'Africa';
        if ($country == 'SE') $continent = 'Europe';
        if ($country == 'CH') $continent = 'Europe';
        if ($country == 'SY') $continent = 'Asia';
        if ($country == 'TW') $continent = 'Asia';
        if ($country == 'TJ') $continent = 'Asia';
        if ($country == 'TZ') $continent = 'Africa';
        if ($country == 'TH') $continent = 'Asia';
        if ($country == 'TL') $continent = 'Asia';
        if ($country == 'TG') $continent = 'Africa';
        if ($country == 'TK') $continent = 'Oceania';
        if ($country == 'TO') $continent = 'Oceania';
        if ($country == 'TT') $continent = 'North America';
        if ($country == 'TN') $continent = 'Africa';
        if ($country == 'TR') $continent = 'Asia';
        if ($country == 'TM') $continent = 'Asia';
        if ($country == 'TC') $continent = 'North America';
        if ($country == 'TV') $continent = 'Oceania';
        if ($country == 'UG') $continent = 'Africa';
        if ($country == 'UA') $continent = 'Europe';
        if ($country == 'AE') $continent = 'Asia';
        if ($country == 'GB') $continent = 'Europe';
        if ($country == 'US') $continent = 'North America';
        if ($country == 'UM') $continent = 'Oceania';
        if ($country == 'VI') $continent = 'North America';
        if ($country == 'UY') $continent = 'South America';
        if ($country == 'UZ') $continent = 'Asia';
        if ($country == 'VU') $continent = 'Oceania';
        if ($country == 'VE') $continent = 'South America';
        if ($country == 'VN') $continent = 'Asia';
        if ($country == 'WF') $continent = 'Oceania';
        if ($country == 'EH') $continent = 'Africa';
        if ($country == 'YE') $continent = 'Asia';
        if ($country == 'ZM') $continent = 'Africa';
        if ($country == 'ZW') $continent = 'Africa';
        return $continent;
    }

}
