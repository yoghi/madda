<?php

namespace TestNamespace;

use ExtendClass;
use NamespaceDep\ClassDep;
use NS\IClass;

/**
 * Generated Class
 */
class ICWNAFWDAC extends ExtendClass implements IClass
{
    /**
     * session unique identifier
     *
     * @var int
     */
    private $prova;

    /**
     * comment ClassDep
     *
     * @var NamespaceDep\ClassDep
     */
    private $dependency;

    /**
     * costruttore
     */
    final public function __construct($prova)
    {
        $this->prova = $prova;
        // autoinizialize
        $this->dependency = new ClassDep();
    }
}
