<?php

namespace TestNamespace;

use ExtendClass;
use NamespaceDep\classDep;
use NS\IClass;

/**
 * Generated Class.
 */
class ImplementsClassWithNamespaceAndFieldWithDependency extends ExtendClass implements IClass
{
    /**
     * session unique identifier.
     *
     * @var int
     */
    private $prova;

    /**
     * comment classDep.
     *
     * @var NamespaceDep\classDep
     */
    private $dependency;

    /**
     * costruttore.
     */
    final public function __construct($prova, classDep $dependency)
    {
        $this->prova = $prova;
        $this->dependency = $dependency;
    }
}
