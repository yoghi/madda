<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\Events;

/**
 * interface domain events
 */
interface DomainEvent
{
    /**
     * @return array
     */
    public function getProperties();

    /**
     * @return int
     */
    public function getAggregateId();

    /**
     * @return \DateTime
     */
    public function getOccurredOn();
}
