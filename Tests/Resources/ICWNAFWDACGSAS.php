<?php
namespace TestNamespace;

use ExtendClass;
use NS\IClass;
use NamespaceDep\classDep;

class ICWNAFWDACGSAS extends ExtendClass implements IClass
{
    /**
     * session unique identifier
     * @var int
     */
    private static $prova;

    /**
     * comment classDep
     * @var NamespaceDep\classDep
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
     * @return NamespaceDep\classDep
     */
    final public static function getDependency()
    {
        return self::$dependency;
    }


    /**
     * @var dependency NamespaceDep\classDep
     */
    final public static function setDependency(classDep $dependency)
    {
        self::$dependency = $dependency;
    }
}
