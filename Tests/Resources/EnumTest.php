<?php
namespace TestNamespace;

abstract class EnumTest
{
    /**
     * nome esplicativo della enum
     * @var string
     */
    protected $name;

    /**
     * singleton for enum
     * @var TestNamespace\EnumTest
     */
    protected static $instance;


    /**
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }


    /**
     * @return TestNamespace\EnumTest|null
     */
    final public static function parseString($parseString)
    {
        $class_name = 'TestNamespace\EnumTest'.'\\'.$parseString;
        if (class_exists($class_name)) {
            $x = $class_name::instance();
            return $x;
        }
        return null;
    }
}
