<?php

namespace TestNamespace;

use ExtendClass;
use NS\IClass;

/**
 * Generated Class
 */
class SNCD extends ExtendClass implements IClass
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
     * @var TestNamespace\ClassDep
     */
    private $dependency;

    /**
     * costruttore
     */
    final public function __construct()
    {
        // autoinizialize
        $this->prova = 0;
        // autoinizialize
        $this->dependency = new ClassDep();
    }

    /**
     * @return int
     */
    final public function getProva()
    {
        return $this->prova;
    }

    /**
     * @var prova int
     */
    final public function setProva(int $prova)
    {
        $this->prova = $prova;
    }

    /**
     * @return TestNamespace\ClassDep
     */
    final public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @var dependency TestNamespace\ClassDep
     */
    final public function setDependency(ClassDep $dependency)
    {
        $this->dependency = $dependency;
    }
}
