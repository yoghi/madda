<?php

namespace TestNamespace;

use ExtendClass;
use NamespaceDep\ClassDep;
use NS\IClass;

/**
 * Generated Class
 */
class ICWNAFWD extends ExtendClass implements IClass
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
    final public function __construct($prova, ClassDep $dependency = null)
    {
        $this->prova = $prova;
        $this->dependency = $dependency;
    }
}
