<?php

namespace BitPrepared\Bundle\EventBundle\Domain\Events;

/**
 * interface domain events.
 */
interface DomainEvent
{
    /**
     * @return \DateTime
     */
    public function getOccurredOn();

    /**
     * @return int
     */
    public function getAggregateId();

    /**
     * @return array
     */
    public function getProperties();
}
