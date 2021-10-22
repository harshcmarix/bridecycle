<?php

namespace app\modules\api\v2\models\search;

use app\models\Setting;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\data\{
    ActiveDataFilter,
    ActiveDataProvider
};
use app\models\Tailor;

/**
 * TailorSearch represents the model behind the search form of `app\models\Tailor`.
 */
class TailorSearch extends Tailor
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'shop_name', 'shop_image', 'address', 'mobile', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @var $hiddenFields Array of hidden fields which not needed in APIs
     */
    protected $hiddenFields = [];

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($requestParams)
    {

        /* ########## Prepare Request Filter Start ######### */
        if (!empty($requestParams['filter'])) {
            foreach ($requestParams['filter'] as $key => $val) {
                if ($key === 'dropdown') {
                    if (is_array($val) && !empty($val)) {
                        foreach ($val as $k => $v) {
                            if (!empty($v['like'])) {
                                $requestParams['filter'][$key][$k]['like'] = trim(urldecode($v['like']));
                            } else {
                                if (isset($v) && !is_array($v) && $v != '') {
                                    $requestParams['filter'][$key][$k] = trim(urldecode($v));
                                }
                            }
                        }
                    }
                } else {
                    if (!empty($val['like'])) {
                        $requestParams['filter'][$key]['like'] = trim(urldecode($val['like']));
                    } else {
                        if (isset($val) && !is_array($val) && $val != '') {
                            $requestParams['filter'][$key] = trim(urldecode($val));
                        }
                    }
                }
            }
        }
        /* ########## Prepare Request Filter End ######### */

        /* ########## Active Data Filter Start ######### */
        $activeDataFilter = new ActiveDataFilter();
        $activeDataFilter->setSearchModel($this);
        $filter = null;
        if (isset($requestParams['filter']['dropdown'])) {
            unset($requestParams['filter']['dropdown']);
        }

        if ($activeDataFilter !== null) {
            if ($activeDataFilter->load($requestParams)) {
                $filter = $activeDataFilter->build();

                if ($filter === false) {
                    return $activeDataFilter;
                }
            }
        }
        /* ########## Active Data Filter End ######### */

        /* ########## Prepare Query With Default Filter Start ######### */

        $distance = Setting::find()->where(['option_key' => 'km_range'])->one();
        $distanceKm = $distance->option_value;

        $query = self::find();
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            if (!empty($requestParams['latitude']) && !empty($requestParams['longitude'])) {
                $lat = $requestParams['latitude'];
                $long = $requestParams['longitude'];
            } else if (!empty($requestParams['address'])) {
                $modelTailor = Tailor::find()->where('address LIKE "%' . $requestParams['address'] . '%"')->one();
                $lat = !empty($modelTailor->latitude) ? $modelTailor->latitude : Yii::$app->user->identity->latitude;
                $long = !empty($modelTailor->longitude) ? $modelTailor->longitude : Yii::$app->user->identity->longitude;
            } else if (!empty($requestParams['zip_code'])) {
                $modelTailor = Tailor::find()->where(['zip_code' => $requestParams['zip_code']])->one();
                $lat = !empty($modelTailor->latitude) ? $modelTailor->latitude : Yii::$app->user->identity->latitude;
                $long = !empty($modelTailor->longitude) ? $modelTailor->longitude : Yii::$app->user->identity->longitude;
            } else {
                $lat = (!empty(Yii::$app->user->identity->latitude)) ? Yii::$app->user->identity->latitude : "";
                $long = (!empty(Yii::$app->user->identity->longitude)) ? Yii::$app->user->identity->longitude : '';
            }
//p($lat,0);
//p($long);
            if (!empty($lat) && !empty($long)) {
                $SelectString = "(3956 * 2 * ASIN(SQRT( POWER(SIN(( $lat - latitude) *  pi()/180 / 2), 2) +COS( $lat * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( $long - longitude) * pi()/180 / 2), 2) ))) as distance";
                $select = ['tailors.*', $SelectString];
            } else {
                $select = ['tailors.*'];
            }
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */
        if (!empty($lat) && !empty($long) && !empty($distanceKm)) {
            $query->having(['<=', 'distance', $distanceKm]);
        }

        $query->groupBy('tailors.id');

        $activeDataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
                'pageSize' => isset($requestParams['pageSize']) ? $requestParams['pageSize'] : Yii::$app->params['default_page_size'], //set page size here
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);

        $tailorModels = $activeDataProvider->getModels();

        foreach ($tailorModels as $key => $value) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';

            //$voucherPicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            $voucherPicture = null;

            if (!empty($tailorModels[$key]['shop_image']) && file_exists(Yii::getAlias('@tailorShopImageRelativePath') . '/' . $value->shop_image)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@tailorShopImageAbsolutePath') . '/' . $value->shop_image;
            }

            if (!empty($tailorModels[$key]['voucher']) && file_exists(Yii::getAlias('@tailorVoucherImageRelativePath') . '/' . $value->voucher)) {
                $voucherPicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@tailorVoucherImageAbsolutePath') . '/' . $value->voucher;
            }

            $tailorModels[$key]['zip_code'] = (string)$value->zip_code;
            $tailorModels[$key]['shop_image'] = $profilePicture;
            $tailorModels[$key]['voucher'] = $voucherPicture;
        }

        $activeDataProvider->setModels($tailorModels);

        return $activeDataProvider;
    }
}