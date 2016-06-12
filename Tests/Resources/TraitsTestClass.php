<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use TraitNamespace\TraitsClass;

/**
 *
 */
class TraitsTestClass extends ExtendClass implements IClass
{
    use TraitsClass;
}
