<?php

namespace app\modules\api\v1\models;

use Yii;
use yii\base\Model;

/**
 * Class Login
 * @package app\modules\api\v1\models
 */
class Login extends Model
{
    public $email;
    public $password;
    public $access_token;
    public $access_token_expired_at;
    public $token_type;
    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
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

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return false
     */
    public function login()
    {
        if ($this->validate()) {
            if ($this->getUser()) {
                $accessToken = $this->_user->generateAccessToken();
                $accessTokenExpiredAt = date('Y-m-d h:i:s', time() + Yii::$app->params['token_expire_time']);
                $this->_user->access_token_expired_at = $accessTokenExpiredAt;
                $this->_user->save();
                Yii::$app->user->login($this->_user, time() + Yii::$app->params['token_expire_time']);

                $this->access_token = $accessToken;
                $this->access_token_expired_at = $accessTokenExpiredAt;
                $this->token_type = Yii::$app->params['token_type'];
                return true;
            }
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            // $this->_user = User::findByEmail($this->email);
            $this->_user = User::findOne(['email' => $this->email, 'user_type' => User::USER_TYPE_NORMAL]);
        }
        return $this->_user;
    }
}