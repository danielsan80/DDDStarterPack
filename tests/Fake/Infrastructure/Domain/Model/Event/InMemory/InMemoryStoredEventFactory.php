<?php

namespace Tests\DddStarterPack\Fake\Infrastructure\Domain\Model\Event\InMemory;

use DddStarterPack\Domain\Model\Event\EventStore;
use DddStarterPack\Domain\Model\Event\StoredEvent;
use DddStarterPack\Domain\Model\Event\StoredEventFactory;

class InMemoryStoredEventFactory implements StoredEventFactory
{
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function build(string $eventType, \DateTimeImmutable $occuredOn, string $serializedEvent): StoredEvent
    {
        $storedEvent = new StoredEvent($this->eventStore->nextId(), $eventType, $occuredOn, $serializedEvent);

        return $storedEvent;
    }
}