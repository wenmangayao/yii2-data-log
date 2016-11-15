<?php

namespace wenmangayao\datalog\actions;

use pttms\models\Pttmslog;
use wenmangayao\datalog\Datalog;
use wenmangayao\datalog\models\Log;
use yii\base\Action;
use yii\data\ActiveDataProvider;
use Yii;
use yii\helpers\Html;
use yii\web\Response;

class LogAction extends Action
{
    /**
     * @var string $view view path.
     */
    public $view;

    /**
     * @var boolean $ajaxview use ajaxview or not.
     */
    public $ajaxview;

    public function run()
    {
        $id = Yii::$app->request->get('id');
        $model = Yii::$app->request->get('model');

        if (empty(Datalog::getInstance()->layout) && !file_exists(Yii::getAlias('@app/views/layouts/main.php'))) {
            $this->controller->layout = false;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $this->getData($id, $model),
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        if ($this->ajaxview) {
            Yii::$app->response->format = Response::FORMAT_JSON;;
            return [
                'title' => $model . ' # ' . $id,
                'content' => $this->controller->renderAjax($this->view, [
                    'dataProvider' => $dataProvider,
                ]),
                'footer' => Html::button('close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"])
            ];
        }
        return $this->controller->render($this->view, [
            'dataProvider' => $dataProvider,
            'title' => $model . ' # ' . $id,
        ]);
    }

    private function getData($id, $model)
    {
        return Log::find()->where([
            'model_id' => $id,
            'model' => $model,
        ]);
    }
}