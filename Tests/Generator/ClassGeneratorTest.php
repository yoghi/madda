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


/**
 * @author Stefano Tamagnini <>
 */
class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyClassGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "emptyClass");
        $config = new Config();
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
        $config = new Config();
        $config->is_enum = true;
        $properties = array();
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $g->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/FirstClass.php');
        $this->assertSame($actual, $expected, 'Classe FirstClass invalid');
    }
}
