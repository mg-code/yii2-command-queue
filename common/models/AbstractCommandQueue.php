<?php

namespace mgcode\cq\common\models;

use Yii;

/**
 * This is the model class for table "command_queue".
 *
 * @property integer $id
 * @property string $key
 * @property string $action
 * @property string $params
 * @property integer $process_pid
 * @property integer $process_is_killed
 * @property integer $is_finished
 * @property integer $has_error
 * @property string $error
 * @property string $trace
 * @property string $created_at
 * @property string $started_at
 * @property string $finished_at
 */
abstract class AbstractCommandQueue extends \yii\db\ActiveRecord
{
    /** @inheritdoc */
    public static function tableName()
    {
        return 'command_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'action', 'params'], 'required'],
            [['params', 'error', 'trace'], 'string'],
            [['process_pid', 'process_is_killed', 'is_finished', 'has_error'], 'integer'],
            [['created_at', 'started_at', 'finished_at'], 'safe'],
            [['key'], 'string', 'max' => 32],
            [['action'], 'string', 'max' => 255],
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
            'process_pid' => 'Process Pid',
            'process_is_killed' => 'Process Is Killed',
            'is_finished' => 'Is Finished',
            'has_error' => 'Has Error',
            'error' => 'Error',
            'trace' => 'Trace',
            'created_at' => 'Created At',
            'started_at' => 'Started At',
            'finished_at' => 'Finished At',
        ];
    }

    /**
     * @inheritdoc
     * @return \mgcode\cq\common\models\queries\CommandQueueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \mgcode\cq\common\models\queries\CommandQueueQuery(get_called_class());
    }
}
