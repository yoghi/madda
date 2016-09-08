<?php

namespace TestNamespace;

use ExtendClass;
use NS\IClass;

/**
 * Generated Class
 */
class ImplementsClassWithNamespaceAndField extends ExtendClass implements IClass
{
    /**
     * identificativo univoco della sessione
     *
     * @var int
     */
    private $prova;

    /**
     * costruttore
     */
    final public function __construct($prova)
    {
        $this->prova = $prova;
    }
}
