<?php

use yii\db\Migration;

class m170424_071047_create_cron_schedule_table extends Migration
{
    public function up()
    {
        $strOptions = null;
        if ($this->db->driverName === 'mysql') {
            $strOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM';
        }
        $this->createTable('{{%cron_schedule}}', [
            'id' => $this->bigInteger(22)->unsigned()->notNull(),
            'key' => $this->char(32)->notNull()->comment('Hash generated from action and params'),
            'action' => $this->string(255)->notNull(),
            'params' => $this->text()->notNull(),
            'trace' => $this->text(),
            'is_important' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
            'process_pid' => $this->integer(11)->unsigned(),
            'process_is_killed' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
            'is_finished' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
            'is_failed' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
            'created_by' => $this->string(64),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'killed_at' => $this->dateTime(),
            'started_at' => $this->dateTime(),
            'finished_at' => $this->dateTime(),
        ], $strOptions);

        $this->addPrimaryKey('pk', '{{%cron_schedule}}', ['id']);
        $this->createIndex('I_key', '{{%cron_schedule}}', ['key']);
    }

    public function down()
    {
        $this->dropTable('{{%cron_schedule}}');
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
