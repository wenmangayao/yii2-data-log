<?php
namespace wenmangayao\datalog\models;

use wenmangayao\datalog\Datalog;
use yii\db\ActiveRecord;
use Yii;

/**
 * @property integer          $id
 * @property integer          $user_id      operation_user_id
 * @property string           $action       operation_type
 * @property string           $model        operation_model
 * @property string           $model_id     model_pk
 * @property string           $old_value
 * @property string           $new_value
 * @property string           $appname      operation_app_name
 * @property string           $addtime      addtime
 */
class Log extends ActiveRecord
{

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Datalog::getInstance()->getDb();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%datalog}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['old_value', 'new_value'], 'string'],
            [['addtime'], 'required'],
            [['addtime'], 'safe'],
            [['action', 'model', 'model_id', 'appname'], 'string', 'max' => 255]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'Id',
            'user_id'    => 'User Id',
            'action'     => 'Action',
            'model'      => 'Model',
            'model_id'   => 'Model Id',
            'old_value'  => 'Old Value',
            'new_value'  => 'New Value',
            'appname'    => 'Appname',
            'addtime'    => 'Addtime',
        ];
    }

    /**
     * @return mixed
     */
    public function getDiffHtml()
    {
        $oldValue = json_decode($this->old_value);
        $newValue = json_decode($this->new_value);

        $html = '';

        if (empty($oldValue) && empty($newValue)) {
            return $html;
        }

        $html .= '<table class="Diff">';
        $html .= '<tr>';
        $html .= '<th>Colunm</th>';
        $html .= '<th>Nld Value</th>';
        $html .= '<th>New Value</th>';
        $html .= '</tr>';

        foreach ($newValue as $column => $new) {
            $old = isset($oldValue->$column) ? $oldValue->$column : '';
            $html .= '<tr class="DataRow">';
            $html .= "<td class='Column'>$column</td>";
            $html .= "<td class='OldValue'>$old</td>";
            $html .= "<td class='NewValue'>$new</td>";
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }
}