<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\Service\QueryRequest;

use BitPrepared\Bundle\FormazioneBundle\Domain\Service\Request;

/**
 * Generated Class
 */
final class DettagliSessioneRequest extends Request
{
    /**
     * identificativo univoco della sessione richiesta
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
