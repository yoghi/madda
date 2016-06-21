<?php

namespace Yoghi\Bundle\MaddaBundleTest\Utils;

trait PhpunitFatalErrorHandling
{

    public static function generateCallTrace($e = null)
    {
        if (is_null($e)) {
            $e = new Exception();
        }
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        // // $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return "\t" . implode("\n\t", $result);
    }

    /**
     * @beforeClass
     */
    public static function setupErrorHandling()
    {
        set_exception_handler(function ($exception) {
          self::generateCallTrace($exception);
        });
    }
}
