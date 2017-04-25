<?php

namespace mgcode\cron\common\models;

use Yii;

/**
 * This is the model class for table "cron_schedule".
 *
 * @property string $id
 * @property string $key
 * @property string $action
 * @property string $params
 * @property string $trace
 * @property integer $is_important
 * @property integer $process_pid
 * @property integer $process_is_killed
 * @property integer $is_finished
 * @property integer $is_failed
 * @property string $created_by
 * @property string $created_at
 * @property string $started_at
 * @property string $ended_at
 */
abstract class AbstractCronSchedule extends \yii\db\ActiveRecord
{
    /** @inheritdoc */
    public static function tableName()
    {
        return 'cron_schedule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'key', 'action', 'params'], 'required'],
            [['id', 'is_important', 'process_pid', 'process_is_killed', 'is_finished', 'is_failed'], 'integer'],
            [['params', 'trace'], 'string'],
            [['created_at', 'started_at', 'ended_at'], 'safe'],
            [['key'], 'string', 'max' => 32],
            [['action'], 'string', 'max' => 255],
            [['created_by'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'action' => 'Action',
            'params' => 'Params',
            'trace' => 'Trace',
            'is_important' => 'Is Important',
            'process_pid' => 'Process Pid',
            'process_is_killed' => 'Process Is Killed',
            'is_finished' => 'Is Finished',
            'is_failed' => 'Is Failed',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'started_at' => 'Started At',
            'ended_at' => 'Finished At',
        ];
    }

    /**
     * @inheritdoc
     * @return \mgcode\cron\common\models\queries\CronScheduleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \mgcode\cron\common\models\queries\CronScheduleQuery(get_called_class());
    }
}