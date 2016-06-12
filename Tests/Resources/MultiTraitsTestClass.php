<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use TraitNamespace\TraitsClass;

/**
 *
 */
class MultiTraitsTestClass extends ExtendClass implements IClass
{
    use TraitsClass;
    use TraitsClass2;
}
