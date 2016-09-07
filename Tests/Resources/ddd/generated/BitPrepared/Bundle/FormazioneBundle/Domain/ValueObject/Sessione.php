<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject;

/**
 * Sessione generica utilizzabile in un campo.
 */
final class Sessione
{
    use SessioneTrait;

    /**
     * titolo della sessione.
     *
     * @var string
     */
    private $title;

    /**
     * Descrizione completa della sessione.
     *
     * @var string
     */
    private $description;

    /**
     * costruttore.
     */
    final public function __construct($title, $description = null)
    {
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @return string
     */
    final public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    final public function getDescription()
    {
        return $this->description;
    }
}
