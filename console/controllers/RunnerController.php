<?php
namespace mgcode\cron\console\controllers;

use mgcode\commandLogger\LoggingTrait;
use mgcode\cron\common\models\CronSchedule;
use yii\console\Controller;
use yii\helpers\Json;

class RunnerController extends Controller
{
    use LoggingTrait;

    /** @inheritdoc */
    public $defaultAction = 'run';

    /**
     * Run cron schedule by its unique ID.
     * @param $id
     * @throws \Exception
     */
    public function actionRun($id)
    {
        $schedule = CronSchedule::find()->where(['id' => $id])->one();
        if (!$schedule) {
            throw new \Exception('Cron schedule not found');
        }

        if ($schedule->getIsRunning()) {
            $this->msg('Command is already running.');
            return;
        }

        $this->msg('Running schedule #{id}', ['id' => $schedule->id]);
        $schedule->setIsRunning();

        // Save trace
        $callback = function ($buffer) use ($schedule) {
            $trace = $schedule->trace."\r\n".$buffer;
            $schedule->updateTrace($trace);
            return $buffer;
        };
        ob_start($callback, 1);

        // Execute command
        try {
            $params = Json::decode($schedule->params);
            \Yii::$app->runAction($schedule->action, $params);
            $schedule->setIsFinished();
        } catch (\Exception $e) {
            $this->logException($e);
            $schedule->setIsFailed();
        }

        ob_end_flush();
        $this->msg('Ended');
    }
}