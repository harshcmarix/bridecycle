<?php

/**
 * Developer Debugging function
 * @param $value
 * @param int $exit
 */
function p($value, $exit = 1)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    if ($exit == 1) {
        die;
    }
}

/**
 * @param $attribute
 * @param string $language
 * @return mixed|string
 */
function getValidationErrorMsg($attribute, $language = 'english')
{
    if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'english') {
        $language = 'english';
    }

    if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'german') {
        $language = 'german';
    }
    //$messageLanguage = (!empty(\Yii::$app->language)) ? \Yii::$app->language : $language;
    $messageLanguage = $language;

    $messageLanguageString = $messageLanguage . '_message';
    $responseResult = "";
    if (!empty($messageLanguageString)) {
        $result = \app\models\ErrorMessages::find()->where(['error_key' => $attribute])->all();
        if (!empty($result[0]) && !empty($result[0][$messageLanguageString])) {
            $responseResult = $result[0][$messageLanguageString];
        }
    }
    return $responseResult;

}

//$configForGoogle = require __DIR__ . '/../web/uploads/google-app-credentials.json';
//defined('GOOGLE_APPLICATION_CREDENTIALS') or define('GOOGLE_APPLICATION_CREDENTIALS', $configForGoogle);
defined('GOOGLE_APPLICATION_CREDENTIALS') or define('GOOGLE_APPLICATION_CREDENTIALS', '/../web/uploads/google-app-credentials.json');

defined('YII_ENV') or define('YII_ENV', getenv('ENVIRONMENT'));

//if (YII_ENV == 'dev') {
//    defined('YII_DEBUG') or define('YII_DEBUG', true);
//}
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
?>