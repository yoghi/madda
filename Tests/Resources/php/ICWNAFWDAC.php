<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use NamespaceDep\ClassDep;

/**
 *
 */
class ICWNAFWDAC extends ExtendClass implements IClass
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
}
