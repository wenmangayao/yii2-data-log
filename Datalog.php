<?php
namespace wenmangayao\datalog;

use yii\base\Module;
use yii\web\ForbiddenHttpException;
use Yii;


class Datalog extends Module
{
    /**
     * @var array access rules
     */
    public $access = [
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'allow' => true,
                'roles' => ['admin'],
            ],
        ],
    ];


    /**
     * @var array Forbidden app
     */
    public $Forbidden = [];

    /**
     * @var string layout path.
     */
    public $layout;

    /**
     * @var boolean Use ajaxview or not, default false.
     */
    public $ajaxview = false;

    /**
     * @var string view path.
     */
    public $view = 'index';

    /**
     * @var string database name
     */
    public $db = 'db';

    /**
     * @var string defaultRoute
     */
    public $defaultRoute = 'log/index';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (in_array(Yii::$app->id, $this->Forbidden)) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }
        return true;
    }

    /**
     * get the database connection.
     * @return \yii\db\Connection
     */
    public function getDb()
    {
        return Yii::$app->{$this->db};
    }

    /**
     * @return int|null|string
     */
    public static function findModuleIdentifier()
    {
        foreach (Yii::$app->modules as $name => $module) {
            /** @var Module $module */
            $class = null;
            if (is_string($module))
                $class = $module;
            elseif (is_array($module)) {
                if (isset($module['class']))
                    $class = $module['class'];
            } else
                $class = $module::className();

            $parts = explode('\\', $class);
            if ($class && strtolower(end($parts)) == 'datalog')
                return $name;
        }
        return null;
    }
}
