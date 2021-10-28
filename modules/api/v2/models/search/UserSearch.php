<?php

namespace app\modules\api\v2\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\modules\api\v2\models\User;

/**
 * Class UserSearch
 * @package app\modules\api\v2\models\search
 */
class UserSearch extends User
{
    /**
     * @var $hiddenFields Array of hidden fields which not needed in APIs
     */
    protected $hiddenFields = ['password_hash', 'access_token', 'access_token_expired_at', 'temporary_password'];

    /**
     * @return array[]
     */
    public function rules()
    {
        // return [
        //     [['id', 'mobile', 'shop_phone_number'], 'integer'],
        //     [['profile_picture', 'first_name', 'last_name', 'email', 'password_hash', 'temporary_password', 'access_token', 'access_token_expired_at', 'personal_information', 'user_type', 'is_shop_owner', 'shop_name', 'shop_email', 'created_at', 'updated_at'], 'safe'],
        //     [['weight', 'height'], 'number'],
        // ];

        return [
            [['id'], 'integer'],
            [['profile_picture', 'first_name', 'last_name', 'email', 'password_hash', 'temporary_password', 'access_token', 'access_token_expired_at', 'password_reset_token', 'country_code', 'mobile', 'personal_information', 'user_type', 'is_shop_owner', 'shop_cover_picture', 'shop_name', 'shop_email', 'shop_phone_number', 'shop_logo', 'website', 'shop_address', 'is_newsletter_subscription', 'created_at', 'updated_at'], 'safe'],
            [['weight', 'height', 'top_size', 'pant_size', 'bust_size', 'waist_size', 'hip_size'], 'number'],
        ];
    }

    /**
     * @return array|array[]
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param $requestParams
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
        $query = self::find();

        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            //$select = ['id', 'email', 'first_name', 'last_name', 'mobile', 'user_type', 'is_shop_owner','profile_picture',];
            $select = ['users.*'];

        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        $query->groupBy('users.id');

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
        $userModelData = $activeDataProvider->getModels();

        foreach ($userModelData as $key => $value) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($userModelData[$key]['profile_picture']) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $value->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $value->profile_picture;
            }
            $userModelData[$key]['profile_picture'] = $profilePicture;

            $userModelData[$key]['height'] = (string)$value->height;
            $userModelData[$key]['top_size'] = (string)$value->top_size;
            $userModelData[$key]['pant_size'] = (string)$value->pant_size;
            $userModelData[$key]['bust_size'] = (string)$value->bust_size;
            $userModelData[$key]['waist_size'] = (string)$value->waist_size;
            $userModelData[$key]['hip_size'] = (string)$value->hip_size;

        }
        $activeDataProvider->setModels($userModelData);

        return $activeDataProvider;
    }
}