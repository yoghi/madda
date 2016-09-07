<?php

namespace BitPrepared\Bundle\EventBundle\Domain\Events;

/**
 * Event delete for Aggregate Root SpiegazioneSessioneCampo.
 */
final class SpiegazioneSessioneCampoDeleteEvent implements DomainEvent
{
    /**
     * special value.
     *
     * @var int
     */
    private $special;

    /**
     * quando accade l'evento.
     *
     * @var \DateTime
     */
    private $occurredOn;

    /**
     * id dell'aggregato root relativo all'evento.
     *
     * @var int
     */
    private $aggregateId;

    /**
     * proprietà dell'evento.
     *
     * @var array
     */
    private $properties;

    /**
     * costruttore.
     */
    final public function __construct($aggregateId, array $properties = null)
    {
        // autoinizialize
        $this->special = 10;
        // autoinizialize
        $this->occurredOn = new \DateTime();
        $this->aggregateId = $aggregateId;
        $this->properties = $properties;
    }

    /**
     * @return int
     */
    final public function getSpecial()
    {
        return $this->special;
    }

    /**
     * @var special int
     */
    final public function setSpecial(int $special)
    {
        $this->special = $special;
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
