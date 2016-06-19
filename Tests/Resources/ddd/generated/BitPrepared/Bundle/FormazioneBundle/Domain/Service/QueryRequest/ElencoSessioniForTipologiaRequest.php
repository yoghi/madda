<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\Service\QueryRequest;

use BitPrepared\Bundle\FormazioneBundle\Domain\Service\Request;
use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject;

/**
 * Generated Class
 */
final class ElencoSessioniForTipologiaRequest extends Request
{
    /**
     * Tipologia di campo CFM/CFT/CCG/CAM
     * @var BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    private $tipoCampo;


    /**
     * costruttore
     */
    final public function __construct(ValueObject\TipologiaCampo $tipoCampo)
    {
        $this->tipoCampo = $tipoCampo;
    }


    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    final public function getTipoCampo()
    {
        return $this->tipoCampo;
    }
}
