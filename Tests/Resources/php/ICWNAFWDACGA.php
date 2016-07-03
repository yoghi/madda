<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use NamespaceDep\ClassDep;

/**
 * Generated Class
 */
class ICWNAFWDACGA extends ExtendClass implements IClass
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
    final public function __construct($prova)
    {
        $this->prova = $prova;
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
     * @return NamespaceDep\ClassDep
     */
    final public function getDependency()
    {
        return $this->dependency;
    }
}
