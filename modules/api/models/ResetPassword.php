<?php

namespace app\modules\api\models;

use Yii;

/**
 * Class ResetPassword
 * @package app\modules\api\models
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
            [['tmp_password', 'password', 'confirm_password'], 'required'],
            ['tmp_password', 'validateTmpPassword'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Confirm Password don't match"]
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
            $this->addError($attribute, 'Temporary password does not exist');
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
        return $this->_user = User::find()->where(['temporary_password' => $this->tmp_password])->one();
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
