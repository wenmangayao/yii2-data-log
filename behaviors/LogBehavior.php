<?php
namespace wenmangayao\datalog\behaviors;

use pttms\models\Pttmslog;
use wenmangayao\datalog\Datalog;
use wenmangayao\datalog\models\Log;
use Yii;
use yii\db\ActiveRecord;
use bedezign\yii2\audit\Audit;
use bedezign\yii2\audit\models\AuditTrail;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * Class LogBehavior
 * @package common\behaviors
 *
 * @property \yii\db\ActiveRecord $owner
 */
class LogBehavior extends \yii\base\Behavior
{
    /**
     * Array with fields to save
     * You don't need to configure both `allowed` and `ignored`
     * @var array
     */
    public $allowed = [];

    /**
     * Array with fields to ignore
     * You don't need to configure both `allowed` and `ignored`
     * @var array
     */
    public $ignored = [];

    /**
     * Array with app_id to ignore
     * example:
     * [
     *      'class' => LogBehavior::className(),
     *      'ignoredApp' => ['app-console']
     * ]
     * @var array
     */
    public $ignoredApp = [];

    /**
     * Array with classes to ignore
     * @var array
     */
    public $ignoredClasses = [];

    /**
     * Is the behavior is active or not
     * @var boolean
     */
    public $active = true;

    /**
     * Date format to use in stamp - set to "Y-m-d H:i:s" for datetime or "U" for timestamp
     * @var string
     */
    public $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var array
     */
    private $_oldAttributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    public function afterFind()
    {
        $this->setOldAttributes($this->owner->getAttributes());
    }

    public function afterInsert()
    {
        $this->audit('CREATE');
        $this->setOldAttributes($this->owner->getAttributes());
    }

    public function afterUpdate()
    {
        $this->audit('UPDATE');
        $this->setOldAttributes($this->owner->getAttributes());
    }

    public function afterDelete()
    {
        $this->audit('DELETE');
        $this->setOldAttributes([]);
    }

    /**
     * @param $action
     * @throws \yii\db\Exception
     */
    protected function audit($action)
    {
        if ($this->isIgnore()) {
            return;
        }
        // Not active? get out of here
        if (!$this->active) {
            return;
        }
        // Lets check if the whole class should be ignored
        if (sizeof($this->ignoredClasses) > 0 && array_search(get_class($this->owner), $this->ignoredClasses) !== false) {
            return;
        }
        // If this is a delete then just write one row and get out of here
        if ($action == 'DELETE') {
            $this->saveAuditTrailDelete();
            return;
        }
        // Now lets actually write the attributes
        $this->auditAttributes($action);
    }

    /**
     * Clean attributes of fields that are not allowed or ignored.
     *
     * @param $attributes
     * @return mixed
     */
    protected function cleanAttributes($attributes)
    {
        $attributes = $this->cleanAttributesAllowed($attributes);
        $attributes = $this->cleanAttributesIgnored($attributes);
        return $attributes;
    }

    /**
     * Unset attributes which are not allowed
     *
     * @param $attributes
     * @return mixed
     */
    protected function cleanAttributesAllowed($attributes)
    {
        if (sizeof($this->allowed) > 0) {
            foreach ($attributes as $f => $v) {
                if (array_search($f, $this->allowed) === false) {
                    unset($attributes[$f]);
                }
            }
        }
        return $attributes;
    }

    /**
     * Unset attributes which are ignored
     *
     * @param $attributes
     * @return mixed
     */
    protected function cleanAttributesIgnored($attributes)
    {
        if (sizeof($this->ignored) > 0) {
            foreach ($attributes as $f => $v) {
                if (array_search($f, $this->ignored) !== false) {
                    unset($attributes[$f]);
                }
            }
        }
        return $attributes;
    }

    /**
     * @param string $action
     * @throws \yii\db\Exception
     */
    protected function auditAttributes($action)
    {
        // Get the new and old attributes
        $newAttributes = $this->cleanAttributes($this->owner->getAttributes());
        $oldAttributes = $this->cleanAttributes($this->getOldAttributes());
        // If no difference then get out of here
        if (count(array_diff_assoc($newAttributes, $oldAttributes)) <= 0) {
            return;
        }
        // Get the trail data
        $user_id = $this->getUserId();
        $model = $this->owner->className();
        $model_id = $this->getNormalizedPk();
        $addtime = date($this->dateFormat);
        $appname = $this->getAppname();
        $this->saveAuditTrail($action, $newAttributes, $oldAttributes, $user_id, $model, $model_id, $appname, $addtime);
    }

    /**
     * Save the audit trails for a create or update action
     *
     * @param $action
     * @param $newAttributes
     * @param $oldAttributes
     * @param $user_id
     * @param $model
     * @param $model_id
     * @param $appname
     * @param $addtime
     * @throws \yii\db\Exception
     */
    protected function saveAuditTrail($action, $newAttributes, $oldAttributes, $user_id, $model, $model_id, $appname, $addtime)
    {
        // Build a list of fields to log
        $oldArr = $newArr = array();
        foreach ($newAttributes as $field => $new) {
            $old = isset($oldAttributes[$field]) ? $oldAttributes[$field] : '';
            // If they are not the same lets write an audit log
            if ($new != $old) {
                $oldArr[$field] = $old;
                $newArr[$field] = $new;
            }
        }
        // Record the field changes
        if (!empty($oldArr) || !empty($newArr)) {
            Datalog::getInstance()->getDb()->createCommand()->insert(Log::tableName(), [
                'action' => $action,
                'user_id' => $user_id,
                'model' => $model,
                'model_id' => $model_id,
                'old_value' => json_encode($oldArr),
                'new_value' => json_encode($newArr),
                'appname' => $appname,
                'addtime' => $addtime,
            ])->execute();
        }
    }

    /**
     * Save the audit trails for a delete action
     */
    protected function saveAuditTrailDelete()
    {
        Datalog::getInstance()->getDb()->createCommand()->insert(Log::tableName(), [
            'action' => 'DELETE',
            'user_id' => $this->getUserId(),
            'model' => $this->owner->className(),
            'model_id' => $this->getNormalizedPk(),
            'appname' => $this->getAppname(),
            'addtime' => date($this->dateFormat),
        ])->execute();
    }

    /**
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldAttributes;
    }

    /**
     * @param $value
     */
    public function setOldAttributes($value)
    {
        $this->_oldAttributes = $value;
    }

    /**
     * @return string
     */
    protected function getNormalizedPk()
    {
        $pk = $this->owner->getPrimaryKey();
        return is_array($pk) ? json_encode($pk) : $pk;
    }

    /**
     * @return int|null
     */
    protected function getUserId()
    {
        return (Yii::$app instanceof Application && Yii::$app->user) ? Yii::$app->user->id : null;
    }

    /**
     * @return int|null|string
     */
    protected function getAppname()
    {
        return (Yii::$app instanceof \yii\base\Application && Yii::$app->id) ? Yii::$app->id : null;
    }

    /**
     * @return array
     */
    protected function isIgnore()
    {
        return in_array($this->getAppname(), $this->ignoredApp);
    }
}
