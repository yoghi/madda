<?php

namespace TestNamespace;

use ExtendClass;
use NamespaceDep\ClassDep;
use NS\IClass;

/**
 * Generated Class
 */
class ICWNAFWDA extends ExtendClass implements IClass
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
    final public function __construct(ClassDep $dependency)
    {
        // autoinizialize
        $this->prova = 0;
        $this->dependency = $dependency;
    }
}
