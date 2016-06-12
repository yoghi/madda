<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use NamespaceDep\ClassDep;

/**
 *
 */
class ICWNAFWDACGSA extends ExtendClass implements IClass
{
    /**
     * session unique identifier
     * @var int
     */
    private $prova;

    /**
     * comment ClassDep
     * @var NamespaceDep\ClassDep
     */
    private $dependency;


    /**
     * costruttore
     */
    final public function __construct($prova, ClassDep $dependency = null)
    {
        $this->prova = $prova;
        if (is_null($dependency)) {
            $this->dependency = new ClassDep();
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
