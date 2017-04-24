<?php

namespace mgcode\cq\console;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'mgcode\cq\console\controllers';

    /**
     * @var int Maximum concurrent processes (Default: 3).
     */
    public $maxConcurrentProcesses = 3;

    public function init()
    {
        parent::init();
    }
}