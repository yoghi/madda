<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;

class ImplementsClassWithNamespaceAndFieldStatic extends ExtendClass implements IClass
{
    /**
     * identificativo univoco della sessione
     * @var int
     */
    private static $prova;


    /**
     * costruttore
     */
    final public function __construct()
    {
    }
}
