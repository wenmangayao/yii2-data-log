<?php

namespace wenmangayao\datalog\controllers;

use wenmangayao\datalog\Datalog;
use wenmangayao\datalog\filters\AccessControl;
use Yii;
use wenmangayao\datalog\actions\LogAction;

class LogController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => AccessControl::getAccessControlFilter()
        ];
    }

    public function actions()
    {
        return [
            'index' => [
                'class' => LogAction::className(),
                'view' => Datalog::getInstance()->view,
                'ajaxview' => Datalog::getInstance()->ajaxview,
            ]
        ];
    }
}