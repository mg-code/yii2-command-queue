<?php

namespace mgcode\cron\common\models\queries;

/**
 * This is the ActiveQuery class for [[\mgcode\cron\common\models\CronSchedule]].
 * @see \mgcode\cron\common\models\CronSchedule
 */
class CronScheduleQuery extends \yii\db\ActiveQuery
{
    /**
     * Scope for currently running commands.
     * @return $this
     */
    public function running()
    {
        return $this
            ->andWhere([
                'process_is_killed' => 0,
            ])
            ->andWhere(['is not', 'process_pid', null]);
    }

    /**
     * Scope for commands that has not been started
     * @return $this
     */
    public function notStarted()
    {
        return $this->andWhere(['is', 'started_at', null]);
    }

    /**
     * @return $this
     */
    public function important()
    {
        return $this->andWhere(['is_important' => 1]);
    }

    /**
     * @return $this
     */
    public function notImportant()
    {
        return $this->andWhere(['is_important' => 0]);
    }

    /**
     * @inheritdoc
     * @return \mgcode\cron\common\models\CronSchedule[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \mgcode\cron\common\models\CronSchedule|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
