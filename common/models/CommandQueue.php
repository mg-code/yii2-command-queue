<?php

namespace mgcode\cq\common\models;

use mgcode\helpers\ActiveRecordHelperTrait;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * This is the model class for table "command_queue".
 */
class CommandQueue extends AbstractCommandQueue
{
    use ActiveRecordHelperTrait;

    /**
     * Checks whether similar command is already running.
     * @return bool
     */
    public function isSimilarCommandRunning()
    {
        $commands = CommandQueue::find()
            ->running()
            ->andWhere(['key' => $this->key])
            ->andWhere(['!=', 'id', $this->id])
            ->all();
        foreach ($commands as $command) {
            if ($command->isRunning()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if command is currently running
     * @return bool
     */
    public function isRunning()
    {
        if (!$this->process_pid) {
            return false;
        }
        if (posix_getpgid($this->process_pid)) {
            return true;
        }
        $this->process_is_killed = 1;
        $this->saveOrFail(false);
        return false;
    }

    /**
     * Set command as running
     */
    public function setIsRunning()
    {
        $this->resetValues();
        $this->process_pid = getmypid();
        $this->started_at = new Expression('NOW()');
        $this->saveOrFail(false);
    }

    /**
     * Set command as finished
     * @param string $trace
     */
    public function setIsFinished($trace)
    {
        $this->resetValues(false);
        $this->is_finished = 1;
        $this->trace = $trace;
        $this->finished_at = new Expression('NOW()');
        $this->saveOrFail(false);
    }

    /**
     * Saves command error
     * @param string $error
     * @param string $trace
     */
    public function setHasError($error, $trace)
    {
        $this->resetValues(false);
        $this->has_error = 1;
        $this->error = $error;
        $this->trace = $trace;
        $this->saveOrFail(false);
    }

    /**
     * Resets runtime values of command
     * @param bool $resetStartedAt
     */
    protected function resetValues($resetStartedAt = true)
    {
        $this->process_pid = null;
        $this->process_is_killed = 0;
        $this->is_finished = 0;
        $this->has_error = 0;
        $this->error = null;
        $this->trace = null;
        $this->finished_at = null;
        if ($resetStartedAt) {
            $this->started_at = null;
        }
    }

    /**
     * Adds command to queue. Command is not added if same command exists and has not been executed.
     * @param string $action
     * @param array $params
     * @see yii\console\Application::runAction for details on how to use action parameters.
     * @return array|CommandQueue|null|static
     */
    public static function create($action, $params = [])
    {
        if (!is_array($params)) {
            throw new InvalidParamException('Command params should be array.');
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
        $model->saveOrFail(false);

        return $model;
    }
}
