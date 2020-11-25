<?php

namespace chemexsol\common\components;

use Yii;
use yii\base\Application;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * Class TransactionChecker
 *
 * @package chemexsol\common\components
 *
 * @property string[]       $connections
 * The names of the database components where you want to track the completion of the transaction.
 * 'connections' => [
 *    'db', 'db2'
 * ],
 * Default: ['db']
 *
 * @property string[string] $extendedEvents
 * List of components and their events for which you want to track the completion of transactions.
 *
 * 'extendedEvents' => [
 *    'myComponent'  => 'final-work-event',
 *    'myComponent2' => ['final-work-event-1', 'final-work-event-2'],
 * ],
 * Default: []
 */
class TransactionChecker extends Component
{
    public $connections    = ['db'];
    public $extendedEvents = [];

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        Yii::$app->on(Application::EVENT_AFTER_REQUEST, [$this, 'checkTransaction'], 'Application::EVENT_AFTER_REQUEST');

        register_shutdown_function([$this, 'checkTransaction'], 'register_shutdown_function');

        foreach ($this->extendedEvents as $componentId => $eventNames) {
            /** @var Component $component */
            $component = Yii::$app->get($componentId);

            $eventNames = is_array($eventNames) ? $eventNames : [$eventNames];

            foreach ($eventNames as $eventName) {
                $component->on($eventName, [$this, 'checkTransaction'], $componentId . '::' . $eventName);
            }
        }
    }

    /**
     * @param string $eventName
     *
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function checkTransaction($eventName)
    {
        $connectionErrorIds = [];

        foreach ($this->connections as $connectionId) {
            if (!Yii::$app->has($connectionId, true)) {
                continue;
            }

            /** @var Connection $db */
            $db = Yii::$app->get($connectionId);

            $transaction = $db->getTransaction();

            if (!$db || !$transaction) {
                continue;
            }

            $transaction->rollBack();

            $connectionErrorIds[] = $connectionId;
        }

        if (!empty($connectionErrorIds)) {
            $connectionErrorIds = implode(', ', $connectionErrorIds);
            throw new ErrorException("Trigger \"$eventName\": Transaction in \"$connectionErrorIds\" does\'t closed!");
        }
    }
}