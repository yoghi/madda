<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;

class ImplementsClassWithNamespaceAndField extends ExtendClass implements IClass
{
    /**
     * identificativo univoco della sessione
     * @var int
     */
    protected $prova;
}
