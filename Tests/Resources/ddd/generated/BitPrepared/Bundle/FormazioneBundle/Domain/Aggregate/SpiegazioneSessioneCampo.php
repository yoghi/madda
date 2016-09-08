<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\Aggregate;

use BitPrepared\Bundle\FormazioneBundle\Domain\Entity\SessioneCampo;

/**
 * No comment found on ddd model
 */
final class SpiegazioneSessioneCampo
{
    /**
     * identificativo univoco della sessione come aggregato root
     *
     * @var int
     */
    private $id;

    /**
     * No comment found on ddd model
     *
     * @var BitPrepared\Bundle\FormazioneBundle\Domain\Entity\SessioneCampo
     */
    private $sessione;

    /**
     * costruttore
     */
    final public function __construct($id, SessioneCampo $sessione = null)
    {
        $this->id = $id;
        $this->sessione = $sessione;
    }

    /**
     * @return int
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\Entity\SessioneCampo
     */
    final public function getSessione()
    {
        return $this->sessione;
    }
}
