<?php

namespace TestNamespace;

/**
 * Generated Class
 */
abstract class EnumTest
{
    /**
     * nome esplicativo della enum
     *
     * @var string
     */
    protected $name;

    /**
     * Singleton instance for enum
     *
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
        $className = 'TestNamespace\EnumTest'.'\\'.$parseString;
        if (class_exists($className)) {
            $enumClass = $className::instance();

            return $enumClass;
        }
    }
}
