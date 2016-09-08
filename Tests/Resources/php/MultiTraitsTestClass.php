<?php

namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use TraitNamespace\TraitsClass;

/**
 * Generated Class
 */
class MultiTraitsTestClass extends ExtendClass implements IClass
{
    use TraitsClass;
    use TraitsClass2;
}
