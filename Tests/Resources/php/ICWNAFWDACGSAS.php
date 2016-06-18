<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use NamespaceDep\ClassDep;

/**
 *
 */
class ICWNAFWDACGSAS extends ExtendClass implements IClass
{
    /**
     * session unique identifier
     * @var int
     */
    private static $prova;

    /**
     * comment ClassDep
     * @var NamespaceDep\ClassDep
     */
    private static $dependency;


    /**
     * costruttore
     */
    final public function __construct()
    {
    }


    /**
     * @return int
     */
    final public static function getProva()
    {
        return self::$prova;
    }


    /**
     * @var prova int
     */
    final public static function setProva(int $prova)
    {
        self::$prova = $prova;
    }


    /**
     * @return NamespaceDep\ClassDep
     */
    final public static function getDependency()
    {
        return self::$dependency;
    }


    /**
     * @var dependency NamespaceDep\ClassDep
     */
    final public static function setDependency(ClassDep $dependency)
    {
        self::$dependency = $dependency;
    }
}
