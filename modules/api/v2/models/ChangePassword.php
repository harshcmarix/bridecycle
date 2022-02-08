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
            [['old_password'], 'required', 'message' => getValidationErrorMsg('old_password_required', Yii::$app->language)],
            [['password'], 'required', 'message' => getValidationErrorMsg('password_required', Yii::$app->language)],
            [['confirm_password'], 'required', 'message' => getValidationErrorMsg('confirm_password_required', Yii::$app->language)],
            //[['password', 'confirm_password'], 'string', 'min' => 6],
            [['password'], 'string', 'min' => 6, 'tooShort' => getValidationErrorMsg('password_min_character_length', Yii::$app->language)],
            [['confirm_password'], 'string', 'min' => 6, 'tooShort' => getValidationErrorMsg('confirm_password_min_character_length', Yii::$app->language)],
            ['old_password', 'validateOldPassword'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => getValidationErrorMsg('password_confirm_password_match_required', Yii::$app->language)]
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
            $this->addError($attribute, getValidationErrorMsg('old_password_wrong', Yii::$app->language));
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
