<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use TraitNamespace\TraitsClass;
use TraitNamespace\TraitsClass2;

class MultiTraitsTestClass extends ExtendClass implements IClass
{
    use TraitsClass;
    use TraitsClass2;
}
