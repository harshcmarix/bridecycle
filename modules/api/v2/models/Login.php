<?php

namespace app\modules\api\v2\models;

use app\models\UserDevice;
use Yii;
use yii\base\Model;

/**
 * Class Login
 * @package app\modules\api\v2\models
 */
class Login extends Model
{
    public $email;
    public $password;
    public $access_token;
    public $access_token_expired_at;
    public $token_type;
    private $_user = false;

    const SCENARIO_LOGIN_FROM_APP = 'login_from_app';


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['email'], 'required', 'on' => self::SCENARIO_LOGIN_FROM_APP, 'message' => getValidationErrorMsg('email_required', Yii::$app->language)],
            [['password'], 'required', 'on' => self::SCENARIO_LOGIN_FROM_APP, 'message' => getValidationErrorMsg('password_required', Yii::$app->language)],
            [['access_token_expired_at', 'token_type'], 'safe'],
            [['access_token'], 'string', 'max' => 255],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['email'], $fields['password']);

        return $fields;
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param $attribute
     * @param $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (empty($user) || !$user->validatePassword($this->password)) {

                $this->addError($attribute, getValidationErrorMsg('password_valid', Yii::$app->language));

            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return false
     */
    public function login()
    {

        if (!empty(Yii::$app->request->post('is_login_from'))) {

            $loginFrom = Yii::$app->request->post('is_login_from');
            if ($this->getUser($loginFrom)) {

                $modelDevice = UserDevice::find()->where(['notification_token' => Yii::$app->request->post('notification_token'), 'device_platform' => Yii::$app->request->post('device_platform'), 'user_id' => $this->_user->id])->one();
                if (empty($modelDevice)) {
                    $accessToken = $this->_user->generateAccessToken();
                    //$accessTokenExpiredAt = date('Y-m-d h:i:s', time() + Yii::$app->params['token_expire_time']);
                    $accessTokenExpiredAt = date('Y-m-d h:i:s', time() + (3600 * 24 * 365));
                    $this->_user->access_token_expired_at = $accessTokenExpiredAt;
                } else {
                    $accessToken = $this->_user->access_token;
                    //$accessTokenExpiredAt = $this->_user->access_token_expired_at;
                    $accessTokenExpiredAt = date('Y-m-d h:i:s', time() + (3600 * 24 * 365));
                    $this->_user->access_token_expired_at = $accessTokenExpiredAt;
                }

//                $accessToken = $this->_user->generateAccessToken();
//                $accessTokenExpiredAt = date('Y-m-d h:i:s', time() + Yii::$app->params['token_expire_time']);
//                $this->_user->access_token_expired_at = $accessTokenExpiredAt;

                $this->_user->save(false);
                Yii::$app->user->login($this->_user, 3600 * 24 * 365);
                //Yii::$app->user->login($this->_user, 60 * 60 * 24 * 365);

                //Yii::$app->user->login($this->_user, time() + Yii::$app->params['token_expire_time']);

                $this->access_token = $accessToken;
                $this->access_token_expired_at = $accessTokenExpiredAt;
                $this->token_type = ucfirst(Yii::$app->params['token_type']);

                return true;
            }

        } else if ($this->validate()) {
            if ($this->getUser()) {

                Yii::$app->request->post('notification_token');
                Yii::$app->request->post('device_platform');

                $modelDevice = UserDevice::find()->where(['notification_token' => Yii::$app->request->post('notification_token'), 'device_platform' => Yii::$app->request->post('device_platform'), 'user_id' => $this->_user->id])->one();
                if (empty($modelDevice)) {
                    $accessToken = $this->_user->generateAccessToken();
                    //$accessTokenExpiredAt = date('Y-m-d h:i:s', time() + Yii::$app->params['token_expire_time']);
                    $accessTokenExpiredAt = date('Y-m-d h:i:s', time() + (3600 * 24 * 365));
                    $this->_user->access_token_expired_at = $accessTokenExpiredAt;
                } else {
                    $accessToken = $this->_user->access_token;
                    if (empty($this->_user->access_token) || is_null($this->_user->access_token)) {
                        $accessToken = $this->_user->generateAccessToken();
                    }
                    //$accessTokenExpiredAt = $this->_user->access_token_expired_at;
                    $accessTokenExpiredAt = date('Y-m-d h:i:s', time() + (3600 * 24 * 365));
                    $this->_user->access_token_expired_at = $accessTokenExpiredAt;
                }

                $this->_user->save(false);
                //Yii::$app->user->login($this->_user, time() + Yii::$app->params['token_expire_time']);
                Yii::$app->user->login($this->_user, 3600 * 24 * 365);

                $this->access_token = $accessToken;
                $this->access_token_expired_at = $accessTokenExpiredAt;
                $this->token_type = ucfirst(Yii::$app->params['token_type']);

                return true;
            }
            //} else if (!empty(Yii::$app->request->post('is_login_from')) && (!empty(Yii::$app->request->post('facebook_id')) || !empty(Yii::$app->request->post('apple_id')))) {
        }
        return false;
    }

    /**
     * Finds user by [[email]]
     * @return User|null
     */
    public function getUser($loginFrom = null)
    {
        if ($this->_user === false && empty($loginFrom)) {
            //$this->_user = User::findByEmail($this->email);
            $this->_user = User::find()->where(['email' => $this->email, 'user_type' => User::USER_TYPE_NORMAL])->one();
        } elseif ($this->_user === false && !empty($loginFrom) && $loginFrom == 'facebook') {
            $this->_user = User::find()->where(['facebook_id' => Yii::$app->request->post('facebook_id'), 'user_type' => User::USER_TYPE_NORMAL])->one();
        } elseif ($this->_user === false && !empty($loginFrom) && $loginFrom == 'apple') {
            $this->_user = User::find()->where(['apple_id' => Yii::$app->request->post('apple_id'), 'user_type' => User::USER_TYPE_NORMAL])->one();
        } elseif ($this->_user === false && !empty($loginFrom) && $loginFrom == 'google') {
            $this->_user = User::find()->where(['google_id' => Yii::$app->request->post('google_id'), 'user_type' => User::USER_TYPE_NORMAL])->one();
        }
        return $this->_user;
    }
}
