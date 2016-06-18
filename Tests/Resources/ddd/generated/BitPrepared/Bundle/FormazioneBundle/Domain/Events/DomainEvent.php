<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\Events;

/**
 * interface domain events
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
