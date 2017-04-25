<?php

namespace mgcode\cron\console;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'mgcode\cron\console\controllers';

    /**
     * @var int Maximum concurrent processes (Default: 3).
     */
    public $maxConcurrentProcesses = 3;

    /**
     * @var int Memory limit in megabytes for watcher. After exceeding command will be stopped.
     */
    public $watcherMemoryLimit = 32;

    public function init()
    {
        parent::init();
    }
}