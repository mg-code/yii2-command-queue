<?php

namespace mgcode\cron\common\models;

use mgcode\helpers\ActiveRecordHelperTrait;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * This is the model class for table "cron_schedule".
 */
class CronSchedule extends AbstractCronSchedule
{
    use ActiveRecordHelperTrait;

    /**
     * Adds command to queue. Command is not added if same command exists and has not been executed.
     * @param string $action
     * @param array $params
     * @see yii\console\Application::runAction for details on how to use action parameters.
     * @param int $isImportant Whether current command is important and should be run primarily.
     * @return CronSchedule
     */
    public static function create($action, $params = [], $isImportant = 0)
    {
        if (!is_array($params)) {
            throw new InvalidParamException('Parameters must be array.');
        }

        // Check for existence
        $key = md5($action.print_r($params, true));
        $model = static::find()->notStarted()->andWhere(['key' => $key])->one();
        if ($model) {
            return $model;
        }

        $model = new static();
        $model->loadDefaultValues();
        $model->key = $key;
        $model->action = $action;
        $model->params = Json::encode($params);
        $model->is_important = (int) $isImportant;
        if (\Yii::$app->has('user') && ($userId = \Yii::$app->user->id)) {
            $model->created_by = $userId;
        }

        $model->saveOrFail(false);

        return $model;
    }

    /**
     * Checks whether similar command is already running.
     * @return bool
     */
    public function isSimilarCommandRunning()
    {
        $commands = static::find()
            ->running()
            ->andWhere(['key' => $this->key])
            ->andWhere(['!=', 'id', $this->id])
            ->all();
        foreach ($commands as $command) {
            if ($command->getIsRunning()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if command is currently running
     * @return bool
     */
    public function getIsRunning()
    {
        if (!$this->process_pid) {
            return false;
        }
        if (posix_getpgid($this->process_pid)) {
            return true;
        }
        $this->process_is_killed = 1;
        $this->ended_at = new Expression('NOW()');
        $this->saveOrFail(false);
        return false;
    }

    /**
     * Set command as running
     */
    public function setIsRunning()
    {
        $this->resetValues();
        $this->trace = '';
        $this->process_pid = getmypid();
        $this->started_at = new Expression('NOW()');
        $this->saveOrFail(false);
    }

    /**
     * Set command as finished
     */
    public function setIsFinished()
    {
        $this->resetValues();
        $this->is_finished = 1;
        $this->ended_at = new Expression('NOW()');
        $this->saveOrFail(false);
    }

    /**
     * Saves command error
     */
    public function setIsFailed()
    {
        $this->resetValues();
        $this->is_failed = 1;
        $this->ended_at = new Expression('NOW()');
        $this->saveOrFail(false);
    }

    /**
     * Updates schedule trace.
     * @param $trace
     */
    public function updateTrace($trace)
    {
        $this->trace = trim($trace);
        $this->saveOrFail(false, ['trace']);
    }

    /**
     * Resets runtime values of command
     */
    protected function resetValues()
    {
        $this->process_pid = null;
        $this->process_is_killed = 0;
        $this->is_finished = 0;
        $this->is_failed = 0;
        $this->ended_at = null;
    }
}
