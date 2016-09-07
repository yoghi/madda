<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo;

/**
 * Child of TipologiaCampo CFMLC.
 */
final class CFMLC extends TipologiaCampo
{
    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo\CFMLC
     */
    final public static function instance()
    {
        self::$instance = new self();

        return self::$instance;
    }

    /**
     * costruttore.
     */
    final private function __construct()
    {
        $this->name = 'CFMLC';
    }
}
