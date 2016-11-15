<?php

namespace wenmangayao\datalog\web;

use yii\web\AssetBundle;


class DatalogAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@wenmangayao/datalog/web/assets';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/datalog.css',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}