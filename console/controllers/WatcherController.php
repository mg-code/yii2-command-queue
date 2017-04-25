<?php
namespace mgcode\cron\console\controllers;

use mgcode\commandLogger\LoggingTrait;
use mgcode\cron\common\models\CronSchedule;
use mgcode\cron\console\Module;
use mgcode\helpers\SystemHelper;
use yii\console\Controller;

/**
 * Class WatcherController
 * @property Module $module
 */
class WatcherController extends Controller
{
    use LoggingTrait;

    /** @inheritdoc */
    public $defaultAction = 'watch';

    /**
     * Watch for commands that needs to be executed.
     */
    public function actionWatch()
    {
        /** @var Module $module */
        $module = $this->module;

        $this->msg('Watching for cron queue.');
        // Sends one command per loop to prevent running similar commands
        while (memory_get_usage() / 1024 / 1024 < $module->watcherMemoryLimit) {
            if ($schedule = $this->findNextSchedule()) {
                $this->runInBackground($schedule);
            }
            $this->sleep(5);
        }
        $this->msg('Process stopped.');
    }

    /**
     * Finds next schedule to send to background processing.
     * @return bool|CronSchedule False will be returned if no schedule found
     */
    protected function findNextSchedule()
    {
        /** @var Module $module */
        $module = $this->module;

        // Search for important command
        $schedules = CronSchedule::find()->notStarted()->important()->all();
        foreach ($schedules as $schedule) {
            if (!$schedule->isSimilarCommandRunning()) {
                return $schedule;
            }
        }

        // Check concurrent command limit
        $runningCount = $this->getRunningCommandsCount();
        if ($runningCount >= $module->maxConcurrentProcesses) {
            $this->msg('Max concurrent running commands reached.');
            return false;
        }

        // Find next command
        $schedules = CronSchedule::find()->notStarted()->notImportant()->orderBy(['id' => SORT_ASC])->each();
        foreach ($schedules as $schedule) {
            if (!$schedule->isSimilarCommandRunning()) {
                return $schedule;
            }
        }

        // No schedule found
        return false;
    }

    /**
     * Returns count of running commands
     * @return int
     */
    protected function getRunningCommandsCount()
    {
        $commands = CronSchedule::find()->running()->all();
        $count = 0;
        foreach ($commands as $command) {
            if ($command->getIsRunning()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Runs schedule in background
     * @param CronSchedule $schedule
     */
    protected function runInBackground(CronSchedule $schedule)
    {
        $this->msg('Sending schedule #{id} to background processing.', ['id' => $schedule->id]);

        $scriptFile = \Yii::$app->request->getScriptFile();
        $cliCommand = "php {$scriptFile} cron/runner/run {$schedule->id}";
        SystemHelper::runBackgroundCommand($cliCommand);
    }
}