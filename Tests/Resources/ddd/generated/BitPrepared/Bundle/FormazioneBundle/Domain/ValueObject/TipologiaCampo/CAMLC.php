<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * Child of TipologiaCampo CAMLC
 */
final class CAMLC extends TipologiaCampo
{

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo\CAMLC
     */
    final public static function instance()
    {
        self::$instance = new CAMLC();
        return self::$instance;
    }


    /**
     * costruttore
     */
    final private function __construct()
    {
        $this->name = 'CAMLC';
    }
}
