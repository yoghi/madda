<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * Child of TipologiaCampo CAMEG
 */
final class CAMEG extends TipologiaCampo
{
    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo\CAMEG
     */
    final public static function instance()
    {
        self::$instance = new self();

        return self::$instance;
    }

    /**
     * costruttore
     */
    final private function __construct()
    {
        $this->name = 'CAMEG';
    }
}
