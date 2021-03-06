<?php

namespace DDDStarterPack\Application\Notification;

use DDDStarterPack\Domain\Model\Event\EventStore;
use DDDStarterPack\Domain\Model\Event\StoredDomainEventInterface;
use DDDStarterPack\Domain\Model\Message\PublishedMessageTracker;
use JMS\Serializer\SerializerBuilder;

/**
 * This class takes care of sending messages to a queue
 *
 * Class NotificationService
 * @package DDDStarterPack\Application\Notification
 */
class NotificationService
{
    private $eventStore;
    private $publishedMessageTracker;
    private $messageProducer;
    private $serializer;
    private $notificationMessageFactory;

    public function __construct
    (
        EventStore $eventStore,
        PublishedMessageTracker $publishedMessageTracker,
        MessageProducer $messageProducer,
        NotificationMessageFactory $notificationMessageFactory
    )
    {
        $this->eventStore = $eventStore;
        $this->publishedMessageTracker = $publishedMessageTracker;
        $this->messageProducer = $messageProducer;
        $this->notificationMessageFactory = $notificationMessageFactory;
    }

    public function publishNotifications($exchangeName)
    {
        /**
         * $notifications contains all the events that have not yet been published,
         * starting from the id of the last published event (the most recent one)
         */
        $notifications = $this->listUnpublishedNotifications(
            $this->publishedMessageTracker->mostRecentPublishedMessageId($exchangeName)
        );

        if (!$notifications) {

            return 0;
        }

        $this->messageProducer->open($exchangeName);

        try {

            $publishedMessages = 0;
            $lastPublishedNotification = null;

            foreach ($notifications as $notification) {

                $lastPublishedNotification = $this->publish(
                    $exchangeName,
                    $notification,
                    $this->messageProducer
                );

                $publishedMessages++;
            }

        } catch (\Exception $e) {

            throw $e;
        }

        /**
         * Salvo l'ultimo evento pubblicato
         */
        $this->publishedMessageTracker->trackMostRecentPublishedMessage($exchangeName, $lastPublishedNotification);

        $this->messageProducer->close($exchangeName);

        /**
         * Ritorno il numero di messaggi pubblicati
         */
        return $publishedMessages;
    }

    private function listUnpublishedNotifications($mostRecentPublishedMessageId)
    {
        return $this->eventStore->allStoredEventsSince($mostRecentPublishedMessageId);
    }

    private function publish($exchangeName, StoredDomainEventInterface $notification, MessageProducer $messageProducer)
    {
        $serialized = $this->serializer()->serialize($notification, 'json');

        $messageProducer->send(
            $this->notificationMessageFactory->build(
                $exchangeName,
                $notification->eventId(),
                $serialized,
                $notification->typeName(),
                $notification->occurredOn())
        );

        return $notification;
    }

    private function serializer()
    {
        if (null === $this->serializer) {

            $this->serializer = SerializerBuilder::create()->build();
        }

        return $this->serializer;
    }
}
