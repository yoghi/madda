<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use NamespaceDep\classDep;

class ICWNAFWDACGA extends ExtendClass implements IClass
{
    /**
     * session unique identifier
     * @var int
     */
    private $prova;

    /**
     * comment classDep
     * @var NamespaceDep\classDep
     */
    private $dependency;


    /**
     * costruttore
     */
    final public function __construct($prova, classDep $dependency)
    {
        $this->prova = $prova;
        $this->dependency = new classDep();
    }


    /**
     * @return int
     */
    final public function getProva()
    {
        return $this->prova;
    }


    /**
     * @return NamespaceDep\classDep
     */
    final public function getDependency()
    {
        return $this->dependency;
    }
}
