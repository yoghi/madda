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
  private function compareFilePhp($resourcesDir, $namespace, $className, $directoryOutput)
  {
      $fileInput = $resourcesDir.'/'.$className.'.php';
      $fileName = $className.'.php';
      $fileOutput = $directoryOutput . '/'.$namespace. '/'. $fileName;

      $expected = file_get_contents($fileInput);
      $iFile = new SplFileInfo($fileOutput, $directoryOutput.'/'.$namespace, $fileName);
      $f = new Fixer();
      $f->registerBuiltInFixers();
      $f->registerBuiltInConfigs();

      $cr = new ConfigurationResolver();
      $cr->setAllFixers($f->getFixers());
      $cr->setOption('level', 'psr2');
      $cr->setOption('fixers', 'eof_ending,strict_param,short_array_syntax,trailing_spaces,indentation,line_after_namespace,php_closing_tag');
      $cr->resolve();

      $fileCacheManager = new FileCacheManager(false, $directoryOutput, $cr->getFixers());
      $f->fixFile($iFile, $cr->getFixers(), false, false, $fileCacheManager);

      $fileOutput2 = $iFile->getPathname();
      $actual = file_get_contents($fileOutput2);

      $this->assertSame($expected, $actual, 'Classe '.$className.' invalid');
  }
}
