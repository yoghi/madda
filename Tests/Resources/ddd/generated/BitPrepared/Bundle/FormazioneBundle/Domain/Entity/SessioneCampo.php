<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\Entity;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\Sessione;
use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * No comment found on ddd model.
 */
final class SessioneCampo
{
    use SessioneCampoTrait;

    /**
     * identificativo univoco della sessione.
     *
     * @var int
     */
    private $id;

    /**
     * Sessione generica utilizzabile in un campo.
     *
     * @var BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\Sessione
     */
    private $sessione;

    /**
     * Tipologia di campo CFM/CFT/CCG/CAM.
     *
     * @var BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    private $tipologiaCampo;

    /**
     * Documenti correlati alla sessione al campo.
     *
     * @var array
     */
    private $documentiCorrelati;

    /**
     * costruttore.
     */
    final public function __construct($id, Sessione $sessione = null, TipologiaCampo $tipologiaCampo = null, array $documentiCorrelati = null)
    {
        $this->id = $id;
        $this->sessione = $sessione;
        $this->tipologiaCampo = $tipologiaCampo;
        $this->documentiCorrelati = $documentiCorrelati;
    }

    /**
     * @return int
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\Sessione
     */
    final public function getSessione()
    {
        return $this->sessione;
    }

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    final public function getTipologiaCampo()
    {
        return $this->tipologiaCampo;
    }

    /**
     * @return array
     */
    final public function getDocumentiCorrelati()
    {
        return $this->documentiCorrelati;
    }
}
