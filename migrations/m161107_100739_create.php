<?php

use yii\db\Migration;
use yii\db\Schema;

class m161107_100739_create extends Migration
{
    const TABLE = '{{%datalog}}';

    public function up()
    {
        $this->createTable(self::TABLE, [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_BIGINT . " unsigned DEFAULT 0 COMMENT 'operation_user_id'",
            'action' => Schema::TYPE_STRING . "(255) COMMENT 'operation_type'",
            'model' => Schema::TYPE_STRING . "(255) COMMENT 'operation_model'",
            'model_id' => Schema::TYPE_STRING . "(255) COMMENT 'model_pk'",
            'old_value' => Schema::TYPE_TEXT,
            'new_value' => Schema::TYPE_TEXT,
            'appname' => Schema::TYPE_STRING . "(255) COMMENT 'operation_app_name'",
            'addtime' => Schema::TYPE_DATETIME . " NOT NULL COMMENT 'addtime'",
        ], ($this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB' : null));

        $this->createIndex('idx_userid', self::TABLE, 'user_id');
        $this->createIndex('idx_model_modelid', self::TABLE, ['model', 'model_id']);
        $this->createIndex('idx_action', self::TABLE, 'action');
    }

    public function down()
    {
        $this->dropTable(self::TABLE);
    }
}
