<?php

namespace wenmangayao\datalog\controllers;

use wenmangayao\datalog\Datalog;
use wenmangayao\datalog\web\DatalogAsset;
use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    /**
     * access control
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => Datalog::getInstance()->access,
        ];
    }

    /**
     * register view
     * @return boolean
     */
    public function beforeAction($action)
    {
        DatalogAsset::register($this->view);
        return parent::beforeAction($action);
    }
}