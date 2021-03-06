<?php

namespace DDDStarterPack\Domain\Event\Publisher;

use DDDStarterPack\Domain\Event\Subscriber\EventSubscriber;
use DDDStarterPack\Domain\Model\Event\DomainEvent;

class DomainEventPublisher
{
    /**
     * @var EventSubscriber[]
     */
    private $subscribers;

    private static $instance = null;

    public static function instance(bool $reset = false): DomainEventPublisher
    {
        if (null === static::$instance or $reset) {

            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct()
    {
        $this->subscribers = [];
    }

    public function __clone()
    {
        throw new \BadMethodCallException('Clone is not supported');
    }

    public function subscribe(EventSubscriber $eventSubscriber)
    {
        $eventSubscribeClass = get_class($eventSubscriber);

        $found = (bool)array_filter($this->subscribers, function ($v) use ($eventSubscribeClass) {

            return get_class($v) === $eventSubscribeClass;
        });

        if (!$found) {

            $this->subscribers[] = $eventSubscriber;
        }

    }

    public function publish(DomainEvent $anEvent)
    {
        foreach ($this->subscribers as $aSubscriber) {

            if ($aSubscriber->isSubscribedTo($anEvent)) {

                $aSubscriber->handle($anEvent);
            }
        }
    }
}
