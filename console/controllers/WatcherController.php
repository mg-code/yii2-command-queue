<?php
namespace mgcode\cq\console\controllers;

use mgcode\commandLogger\LoggingTrait;
use mgcode\cq\common\models\CommandQueue;
use mgcode\cq\console\Module;
use mgcode\helpers\SystemHelper;
use yii\console\Controller;
use yii\helpers\Json;

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
        $this->msg('Watching for command queue.');

        while (memory_get_usage() / 1024 / 1024 < 32) {
            $this->sendCommand();
            $this->sleep(5);
        }

        $this->msg('Process stopped.');
    }

    /**
     * Run command by its unique ID.
     * @param $id
     * @throws \Exception
     */
    public function actionRun($id)
    {
        $command = CommandQueue::find()->where(['id' => $id])->one();
        if (!$command) {
            throw new \Exception('Command not found');
        }

        if ($command->isRunning()) {
            $this->msg('Command is already running.');
            return;
        }

        $this->msg('Running command #{id}', ['id' => $command->id]);
        $command->setIsRunning();
        try {
            $params = Json::decode($command->params);
            \Yii::$app->runAction($command->action, $params);
            $command->setIsFinished();
        } catch (\Exception $e) {
            $this->logException($e);
            $error = $this->getMsgFromException($e);
            $command->setHasError($error);
        }

        $this->msg('Finished');
    }

    /**
     * Finds next command and executes it.
     */
    protected function sendCommand()
    {
        /** @var Module $module */
        $module = \Yii::$app->getModule('command-queue');

        $runningCount = $this->getRunningCommandCount();
        if ($runningCount >= $module->maxConcurrentProcesses) {
            $this->msg('Max concurrent running commands reached.');
            return;
        }

        /** @var CommandQueue[] $commands */
        $commands = CommandQueue::find()->notStarted()->orderBy(['id' => SORT_ASC])->each();
        foreach ($commands as $command) {
            if ($command->isSimilarCommandRunning()) {
                continue;
            }

            $this->runInBackground($command);
            return;
        }
    }

    /**
     * Returns count of running commands
     * @return int
     */
    protected function getRunningCommandCount()
    {
        $commands = CommandQueue::find()->running()->all();
        $count = 0;
        foreach ($commands as $command) {
            if ($command->isRunning()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Runs command in background
     * @param CommandQueue $command
     */
    protected function runInBackground(CommandQueue $command)
    {
        $this->msg('Executing command #{id} in background.', ['id' => $command->id]);

        $scriptFile = \Yii::$app->request->getScriptFile();
        $cliCommand = "php {$scriptFile} command-queue/watcher/run {$command->id}";
        SystemHelper::runBackgroundCommand($cliCommand);
    }
}