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
    public function testReadInvalidFile()
    {
        $baseDirectory = __DIR__.'/';
        $fileName = "nonEsite.yml";
        $rym = new Reader();
        $rym->readYaml($baseDirectory, $fileName);
    }

    public function testEmptyReadFile()
    {
        $baseDirectory = __DIR__.'/../Resources/';
        $fileName = "emptyModel.yml";
        $rym = new Reader();
        $rym->readYaml($baseDirectory, $fileName);
        $prop = $rym->getProperties();
        $propExpected = array(
          "ddd" => array(),
          "classes" => array()
        );
        $this->assertEquals($propExpected, $prop, "corretta lettura yml");
    }

    /**
     * @slowThreshold 10
     */
    public function testReadFile()
    {
        $baseDirectory = __DIR__.'/../Resources/basemodel/';
        $fileName = "model.yml";
        $rym = new Reader();
        $rym->readYaml($baseDirectory, $fileName);
        $prop = $rym->getProperties();
        $propExpected = array(
          "ddd" => array(
            "vo" => array(
              "package" => "Yoghi\Bundle\Madda\Domain\ValueObject",
              "getter" => 1
            )
          ),
          "classes" => array(
            "TestEnum" => array(
              "ddd" => array( "type" => "vo"),
              "name" => "TestEnum",
              "description" => "Test Enum",
              "namespace" => 'Yoghi\Bundle\Madda\Domain\ValueObject',
              'enum' => array(
                'TEST'
              )
            )
          )
        );
        $this->assertEquals($propExpected, $prop, "corretta lettura yml");
    }
}
