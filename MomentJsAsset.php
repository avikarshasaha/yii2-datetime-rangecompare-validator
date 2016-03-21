<?php
namespace avikarsha\validators;

use yii\web\AssetBundle;

class MomentJsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/moment/min';
    
    /**
     * @inheritdoc
     */
    public $js = [
        'moment.min.js',
    ];
}
