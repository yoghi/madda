<?php
namespace TestNamespace;

/**
 *
 */
class SingletonClass
{
    /**
     * Singleton instance
     * @var TestNamespace\SingletonClass
     */
    protected static $instance;


    /**
     * Singleton NO THREAD SAFE!
     * @return TestNamespace\SingletonClass|null
     */
    final public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new SingletonClass();
        }
        return self::$instance;
    }
}
