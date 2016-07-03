<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;

/**
 * Generated Class
 */
class ICWNAFWDACG extends ExtendClass implements IClass
{
    /**
     * session unique identifier
     * @var int
     */
    private $prova;

    /**
     * comment ClassDep
     * @var TestNamespace\ClassDep
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
}
