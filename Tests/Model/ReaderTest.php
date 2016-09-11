<?php

namespace Yoghi\Bundle\MaddaBundle\Model;

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
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Yoghi\Bundle\MaddaBundle\Exception\MaddaException
     */
    public function testReadNotExistFile()
    {
        $baseDirectory = __DIR__.'/';
        $fileName = 'nonEsite.yml';
        $rym = new Reader();
        $rym->readYaml($baseDirectory.'/'.$fileName);
    }

    public function testEmptyReadFile()
    {
        $baseDirectory = __DIR__.'/../Resources/finder';
        $fileName = 'emptyModel.yml';
        $rym = new Reader();
        $rym->readYaml($baseDirectory.'/'.$fileName);
        $prop = $rym->getProperties();
        $propExpected = [
            'ddd' => [],
            'classes' => [],
        ];
        $this->assertEquals($propExpected, $prop, 'corretta lettura yml');
    }

    /**
     * @expectedException Yoghi\Bundle\MaddaBundle\Exception\MaddaException
     */
    public function testReadInvalidFile()
    {
        $baseDirectory = __DIR__.'/../Resources/';
        $fileName = 'invalidModel.yml';
        $rym = new Reader();
        $rym->readYaml($baseDirectory.'/'.$fileName);
    }

    /**
     * @slowThreshold 10
     */
    public function testReadFile()
    {
        $baseDirectory = __DIR__.'/../Resources/finder/basemodel';
        $fileName = 'model.yml';
        $rym = new Reader();
        $rym->readYaml($baseDirectory.'/'.$fileName);
        $prop = $rym->getProperties();
        $propExpected = [
            'ddd' => [
            'vo' => [
                'package' => "Yoghi\Bundle\Madda\Domain\ValueObject",
                'getter' => 1,
            ],
            ],
            'classes' => [
            'TestEnum' => [
                'ddd' => ['type' => 'vo'],
                'name' => 'TestEnum',
                'description' => 'Test Enum',
                'namespace' => 'Yoghi\Bundle\Madda\Domain\ValueObject',
                'enum' => [
                'TEST',
                ],
            ],
            ],
        ];
        $this->assertEquals($propExpected, $prop, 'corretta lettura yml');
    }

    public function testReadDomainDefinition()
    {
        $baseDirectory = __DIR__.'/../Resources/finder/basemodel';
        $fileName = 'model.yml';
        $rym = new Reader();
        $rym->readYaml($baseDirectory.'/'.$fileName);
        $testVoProperties = $rym->getDomainDefinitionAttributes('vo');
        $this->assertEquals('Yoghi\Bundle\Madda\Domain\ValueObject', $testVoProperties['package'], 'package non letto corretamente');
    }

    public function testReadAllClassDefinition()
    {
        $baseDirectory = __DIR__.'/../Resources/finder/basemodel';
        $fileName = 'model.yml';
        $rym = new Reader();
        $rym->readYaml($baseDirectory.'/'.$fileName);
        $prop = $rym->getClassesDefinition();
        $propExpected = [
        'TestEnum' => [
            'ddd' => ['type' => 'vo'],
            'name' => 'TestEnum',
            'description' => 'Test Enum',
            'namespace' => 'Yoghi\Bundle\Madda\Domain\ValueObject',
            'enum' => ['TEST'],
            ],
        ];
        $this->assertEquals($propExpected, $prop, 'corretta lettura yml');
    }

    public function testReadClassDefinition()
    {
        $baseDirectory = __DIR__.'/../Resources/finder/basemodel';
        $fileName = 'model.yml';
        $rym = new Reader();
        $rym->readYaml($baseDirectory.'/'.$fileName);
        $testEnumProperties = $rym->getClassDefinitionAttributes('TestEnum');
        $this->assertEquals('Yoghi\Bundle\Madda\Domain\ValueObject', $testEnumProperties['namespace'], 'namespace non letto corretamente');
    }
}
