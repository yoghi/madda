<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\Events;

/**
 * Event create for Aggregate Root SpiegazioneSessioneCampo
 */
final class SpiegazioneSessioneCampoCreateEvent implements DomainEvent
{
    /**
     * quando accade l'evento
     * @var \DateTime
     */
    private $occurredOn;

    /**
     * id dell'aggregato root relativo all'evento
     * @var int
     */
    private $aggregateId;

    /**
     * proprietà dell'evento
     * @var array
     */
    private $properties;


    /**
     * costruttore
     */
    final public function __construct($aggregateId, array $properties = null)
    {
        // autoinizialize
        $this->occurredOn = new \DateTime();
        $this->aggregateId = $aggregateId;
        $this->properties = $properties;
    }


    /**
     * @return \DateTime
     */
    final public function getOccurredOn()
    {
        return $this->occurredOn;
    }


    /**
     * @return int
     */
    final public function getAggregateId()
    {
        return $this->aggregateId;
    }


    /**
     * @return array
     */
    final public function getProperties()
    {
        return $this->properties;
    }
}
