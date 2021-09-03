<?php

namespace app\modules\api\v2\models;

use Yii;

/**
 * Class ChangePassword
 * @package app\modules\api\v2\models
 */
class ChangePassword extends User
{
    /**
     * @var string
     */
    public $old_password;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $confirm_password;

    /**
     * Returns the validation rules for attributes.
     * @return array
     */
    public function rules()
    {
        return [
            [['old_password', 'password', 'confirm_password'], 'required'],
            [['password', 'confirm_password'], 'string','min'=> 6],
            ['old_password', 'validateOldPassword'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Confirm Password don't match"]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function validateOldPassword($attribute, $params)
    {
        $userModel = Yii::$app->user->identity;
        if (!$userModel->validatePassword($this->old_password)) {
            $this->addError($attribute, 'Old Password has been wrong!');
        }
    }

    /**
     * Returns the attribute labels.
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'old_password' => 'Old Password',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
        ];
    }
}
