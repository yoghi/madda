<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\Service\CommandRequest;

use BitPrepared\Bundle\FormazioneBundle\Domain\Service\Request;
use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * No comment found on ddd model
 */
final class NewSessioneRequest extends Request
{
    /**
     * titolo della sessione
     *
     * @var string
     */
    private $title;

    /**
     * descrizione della sessione
     *
     * @var string
     */
    private $description;

    /**
     * Tipologia di campo CFM/CFT/CCG/CAM
     *
     * @var BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    private $tipoCampo;

    /**
     * costruttore
     */
    final public function __construct($title, $description = null, TipologiaCampo $tipoCampo = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->tipoCampo = $tipoCampo;
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

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    final public function getTipoCampo()
    {
        return $this->tipoCampo;
    }
}
