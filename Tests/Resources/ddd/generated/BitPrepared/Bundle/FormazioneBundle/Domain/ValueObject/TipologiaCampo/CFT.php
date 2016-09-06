<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * Child of TipologiaCampo CFT
 */
final class CFT extends TipologiaCampo
{

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo\CFT
     */
    final public static function instance()
    {
        self::$instance = new CFT();
        return self::$instance;
    }


    /**
     * costruttore
     */
    final private function __construct()
    {
        $this->name = 'CFT';
    }
}
