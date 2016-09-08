<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\Classes;

/**
 * No comment found on ddd model
 */
class SampleClassWithNamespace
{
    /**
     * sample string
     *
     * @var string
     */
    private $sasa;

    /**
     * costruttore
     */
    final public function __construct($sasa)
    {
        $this->sasa = $sasa;
    }
}
