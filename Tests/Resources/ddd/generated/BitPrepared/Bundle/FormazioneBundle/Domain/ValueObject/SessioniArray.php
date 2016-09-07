<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject;

/**
 * Sessioni utilizzabili in un campo.
 */
final class SessioniArray
{
    /**
     * array di sessione/sessioneCampo.
     *
     * @var array
     */
    private $sessioni;

    /**
     * costruttore.
     */
    final public function __construct(array $sessioni)
    {
        $this->sessioni = $sessioni;
    }

    /**
     * @return array
     */
    final public function getSessioni()
    {
        return $this->sessioni;
    }
}
