<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * Generated Class
 */
final class CFMEG extends TipologiaCampo
{

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo\CFMEG
     */
    final public static function instance()
    {
        self::$instance = new CFMEG();
        return self::$instance;
    }


    /**
     * costruttore
     */
    final private function __construct()
    {
        $this->name = 'CFMEG';
    }
}
