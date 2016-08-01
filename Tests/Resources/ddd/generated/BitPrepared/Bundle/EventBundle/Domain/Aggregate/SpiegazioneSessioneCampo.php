<?php
namespace BitPrepared\Bundle\EventBundle\Domain\Aggregate;

/**
 * No comment found on ddd model
 */
final class SpiegazioneSessioneCampo
{
    /**
     * identificativo univoco della sessione come aggregato root
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
