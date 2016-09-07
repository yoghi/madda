<?php

namespace TestNamespace;

use ExtendClass;
use NamespaceDep\ClassDep;
use NS\IClass;

/**
 * Generated Class.
 */
class ICWNAFWDACGSA extends ExtendClass implements IClass
{
    /**
     * session unique identifier.
     *
     * @var int
     */
    private $prova;

    /**
     * comment ClassDep.
     *
     * @var NamespaceDep\ClassDep
     */
    private $dependency;

    /**
     * costruttore.
     */
    final public function __construct($prova, ClassDep $dependency = null)
    {
        $this->prova = $prova;
        if (empty($dependency)) {
            $this->dependency = new ClassDep();
        } else {
            $this->dependency = $dependency;
        }
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
     * @return NamespaceDep\ClassDep
     */
    final public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @var dependency NamespaceDep\ClassDep
     */
    final public function setDependency(ClassDep $dependency)
    {
        $this->dependency = $dependency;
    }
}
