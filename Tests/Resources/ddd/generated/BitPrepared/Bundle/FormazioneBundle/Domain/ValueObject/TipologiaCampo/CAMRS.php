<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * Child of TipologiaCampo CAMRS
 */
final class CAMRS extends TipologiaCampo
{

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo\CAMRS
     */
    final public static function instance()
    {
        self::$instance = new CAMRS();
        return self::$instance;
    }


    /**
     * costruttore
     */
    final private function __construct()
    {
        $this->name = 'CAMRS';
    }
}
