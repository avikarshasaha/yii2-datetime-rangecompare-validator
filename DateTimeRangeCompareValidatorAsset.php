<?php
namespace avikarsha\validators;

use yii\web\AssetBundle;

class DateTimeRangeCompareValidatorAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/avikarsha/yii2-datetime-rangecompare-validator/assets';
    
    /**
     * @inheritdoc
     */
    public $publishOptions = ['forceCopy' => YII_DEBUG];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/datetime-rangecompare-validator.js',
    ];
    
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\validators\ValidationAsset',
        'avikarsha\validators\MomentJsAsset',
    ];
}
