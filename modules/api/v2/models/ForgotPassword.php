<?php

namespace app\modules\api\v2\models;

use Yii;

/**
 * Class ForgotPassword
 * @package app\modules\api\v2\models
 */
class ForgotPassword extends User
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var bool
     */
    private $_user = false;

    /**
     * Returns the validation rules for attributes.
     * @return array
     */
    public function rules()
    {
        return [
            [['email'], 'required', 'message' => getValidationErrorMsg('email_required', Yii::$app->language)],
            [['email'], 'email', 'message' => getValidationErrorMsg('email_not_valid', Yii::$app->language)],
            [['email'], 'validateUser'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function validateUser($attribute, $params)
    {
        if ($this->hasErrors()) {
            return false;
        }

        $user = $this->getUser();
        // Display message if password is blank in database (This case will happen when user signed up using facebook or google)
        if (!$user instanceof User) {
            //$this->addError($attribute, 'User does not exist');
            $this->addError($attribute, getValidationErrorMsg('email_user_not_exist', Yii::$app->language));
        }
    }

    /**
     * Returns the attribute labels.
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('app', 'Email')
        ];
    }

    /**
     * Helper method responsible for finding user based on the model scenario.
     * In Login With Email 'lwe' scenario we find user by email, otherwise by username
     *
     * @return object The found User object.
     */
    private function findUser()
    {
        // return $this->_user = User::findByEmail($this->email);
        //return $this->_user = User::findOne(['email' => $this->email, 'user_type' => User::USER_TYPE_NORMAL]);
        return $this->_user = User::find()->where(['email' => $this->email, 'user_type' => User::USER_TYPE_NORMAL])->one();
    }

    /**
     * Method that is returning User object.
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = $this->findUser();
        }

        return $this->_user;
    }
}
