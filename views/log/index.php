<?php

use yii\grid\GridView;

$this->title = isset($title) ? $title : '';
?>
<h2><?= \yii\helpers\Html::encode($this->title)?></h2>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'action',
        'model',
        'appname',
        'user_id',
        [
            'label' => 'Edit',
            'format' => 'raw',
            'value' => function ($model) {
                return $model->getDiffHtml();
            }
        ],
        'addtime',
    ]])
?>