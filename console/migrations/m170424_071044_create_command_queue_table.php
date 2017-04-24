<?php

use yii\db\Migration;

class m170424_071044_create_command_queue_table extends Migration
{
    public function up()
    {
        $strOptions = null;
        if ($this->db->driverName === 'mysql') {
            $strOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%command_queue}}', [
            'id' => $this->primaryKey(11)->unsigned()->notNull(),
            'key' => $this->char(32)->notNull(),
            'action' => $this->string(255)->notNull(),
            'params' => $this->text()->notNull(),
            'process_pid' => $this->integer(11)->unsigned(),
            'is_finished' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
            'has_error' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
            'error' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'started_at' => $this->dateTime(),
            'finished_at' => $this->dateTime(),
        ], $strOptions);
        $this->createIndex('I_key', '{{%command_queue}}', ['key']);
    }

    public function down()
    {
        $this->dropTable('{{%command_queue}}');
        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
