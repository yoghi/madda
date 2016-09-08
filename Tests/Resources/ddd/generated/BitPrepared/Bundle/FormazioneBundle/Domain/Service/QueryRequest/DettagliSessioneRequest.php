<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\Service\QueryRequest;

use BitPrepared\Bundle\FormazioneBundle\Domain\Service\Request;

/**
 * No comment found on ddd model
 */
final class DettagliSessioneRequest extends Request
{
    /**
     * identificativo univoco della sessione richiesta
     *
     * @var int
     */
    private $id;

    /**
     * costruttore
     */
    final public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    final public function getId()
    {
        return $this->id;
    }
}
