<?php

namespace app\modules\api\v2\models;

use Yii;

/**
 * Class ResetPassword
 * @package app\modules\api\v2\models
 */
class ResetPassword extends User
{
    /**
     * @var string
     */
    public $tmp_password;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $confirm_password;

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
            [['tmp_password'], 'required', 'message' => getValidationErrorMsg('tmp_password_required', Yii::$app->language)],
            [['password'], 'required', 'message' => getValidationErrorMsg('password_required', Yii::$app->language)],
            [['confirm_password'], 'required', 'message' => getValidationErrorMsg('confirm_password_required', Yii::$app->language)],
            ['tmp_password', 'validateTmpPassword'],
            [['password'], 'string', 'min' => 6, 'tooShort' => getValidationErrorMsg('password_min_character_length', Yii::$app->language)],
            [['confirm_password'], 'string', 'min' => 6,'tooShort' => getValidationErrorMsg('confirm_password_min_character_length', Yii::$app->language)],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => getValidationErrorMsg('password_confirm_password_match_required', Yii::$app->language)]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function validateTmpPassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return false;
        }

        $user = $this->getUser();
        // Display message if password is blank in database (This case will happen when user signed up using facebook or google)
        if (!$user instanceof User) {
            $this->addError($attribute, getValidationErrorMsg('tmp_password_not_exist', Yii::$app->language));
        }
    }

    /**
     * Returns the attribute labels.
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'tmp_password' => 'Temporary Password',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
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
        // return $this->_user = User::find()->where(['temporary_password' => $this->tmp_password])->one();
        return $this->_user = User::find()->where(['temporary_password' => $this->tmp_password, 'user_type' => User::USER_TYPE_NORMAL])->one();
        //return $this->_user = User::find()->where(['temporary_password' => $this->tmp_password])->one();
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
