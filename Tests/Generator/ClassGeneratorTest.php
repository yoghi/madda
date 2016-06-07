<?php

namespace Yoghi\Bundle\MaddaBundle\Generator;

/*
 * This file is part of the MADDA project.
 *
 * (c) Stefano Tamagnini <>
 *
 * This source file is subject to the GPLv3 license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\CS\Fixer;
use Symfony\CS\ConfigurationResolver;
use Symfony\CS\FileCacheManager;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

require_once __DIR__.'/SplFileInfo.php';
require_once __DIR__.'/VfsAdapter.php';

/**
 * @author Stefano Tamagnini <>
 */
class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyClassGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "emptyClass");
        $config = new ClassConfig();
        $config->is_enum = true;
        $properties = array();
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $g->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/EmptyClass.php');
        $this->assertSame($actual, $expected, 'Classe EmptyClass invalid');
    }

    public function testFirstClassGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "FirstClass");
        $config = new ClassConfig();
        $config->is_enum = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "IClass"
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $g->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/FirstClass.php');
        $this->assertSame($expected, $actual, 'Classe FirstClass invalid');
    }

    public function testImplementsClassWithNamespaceGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "ImplementsClassWithNamespace");
        $config = new ClassConfig();
        $config->is_enum = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass"
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $g->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/ImplementsClassWithNamespace.php');
        $this->assertSame($expected, $actual, 'Classe FirstClass invalid');
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethod()
    {
        $namespace = "TestNamespace";
        $g = new ClassGenerator($namespace, "ImplementsClassWithNamespaceAndField");
        $config = new ClassConfig();
        $config->is_enum = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "identificativo univoco della sessione"
            )
          )
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $expected = file_get_contents(__DIR__.'/../Resources/ImplementsClassWithNamespaceAndField.php');

        $directoryV = vfsStream::setup();
        $directoryOutput = $directoryV->url().'/output';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
            mkdir($directoryOutput.'/'.$namespace.'/', 0700, true);
        }

        $fileOutput = $directoryOutput . '/ImplementsClassWithNamespaceAndField.php';
        $g->createFileOnDir(new VfsAdapter($directoryOutput, 0));

        $fileOutput = $directoryOutput . '/'.$namespace.'/ImplementsClassWithNamespaceAndField.php';
        $iFile = new SplFileInfo($fileOutput, $directoryOutput, '/'.$namespace.'/ImplementsClassWithNamespaceAndField.php');

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

        $this->assertSame($expected, $actual, 'Classe FirstClass invalid');
    }
}
