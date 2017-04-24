<?php

namespace mgcode\cq\common\models\queries;

/**
 * This is the ActiveQuery class for [[\mgcode\cq\common\models\CommandQueue]].
 * @see \mgcode\cq\common\models\CommandQueue
 */
class CommandQueueQuery extends \yii\db\ActiveQuery
{
    /**
     * Scope for currently running commands.
     * @return $this
     */
    public function running()
    {
        $this
            ->andWhere([
                'process_is_killed' => 0,
            ])
            ->andWhere(['is not', 'process_pid', null]);

        return $this;
    }

    /**
     * Scope for commands that has not been started
     * @return $this
     */
    public function notStarted()
    {
        $this->andWhere(['is', 'started_at', null]);
        return $this;
    }

    /**
     * @inheritdoc
     * @return \mgcode\cq\common\models\CommandQueue[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \mgcode\cq\common\models\CommandQueue|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
