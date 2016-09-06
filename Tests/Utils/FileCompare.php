<?php

namespace Yoghi\Bundle\MaddaBundleTest\Utils;

use Symfony\CS\Fixer;
use Symfony\CS\ConfigurationResolver;
use Symfony\CS\FileCacheManager;

trait FileCompare
{
    /**
   * Compare generated class with expected class into resource dir
   * @param  string         $resourcesDir fullPath resources dir
   * @param  string         $namespace    namespace of class
   * @param  string         $className    class name
   * @param  ClassGenerator $g            class generator object to test
   */
   private function compareClassPhp($resourcesDir, $namespace, $className, $directoryOutput, $createIfNotExist = false)
   {
       $fileInput = $resourcesDir.'/'.$className.'.php';
       $fileName = $className.'.php';
       $fileOutput = $directoryOutput . '/'.$namespace. '/'. $fileName;

       $iFile = new SplFileInfo($fileOutput, $directoryOutput.'/'.$namespace, $fileName);
       $fixer = new Fixer();
       $fixer->registerBuiltInFixers();
       $fixer->registerBuiltInConfigs();

       $cresolver = new ConfigurationResolver();
       $cresolver->setAllFixers($fixer->getFixers());
       $cresolver->setOption('level', 'psr2');
       $cresolver->setOption('fixers', 'eof_ending,strict_param,short_array_syntax,trailing_spaces,indentation,line_after_namespace,php_closing_tag');
       $cresolver->resolve();

       $fileCacheManager = new FileCacheManager(false, $directoryOutput, $cresolver->getFixers());
       $fixer->fixFile($iFile, $cresolver->getFixers(), false, false, $fileCacheManager);

       $fileOutput2 = $iFile->getPathname();
       $actual = file_get_contents($fileOutput2);

       if (!file_exists($fileInput) && $createIfNotExist) {
           file_put_contents($fileInput, $actual);
       }

       $expected = file_get_contents($fileInput);

       $this->assertSame($expected, $actual, 'Classe '.$className.' invalid');
   }

    private function compareFile($resourcesDir, $directoryOutput, $pathFie, $createIfNotExist = false)
    {
        $fileInput = $resourcesDir.'/'.$pathFie;
        $actual = file_get_contents($directoryOutput.'/'.$pathFie);

        if (!file_exists($fileInput) && $createIfNotExist) {
            file_put_contents($fileInput, $actual);
        }

        $expected = file_get_contents($fileInput);

        $this->assertSame($expected, $actual, 'File '.$pathFie.' invalid');
    }
}
