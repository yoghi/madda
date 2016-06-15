<?php

namespace Yoghi\Bundle\MaddaBundle\Finder;

/*
 * This file is part of the MADDA project.
 *
 * (c) Stefano Tamagnini <>
 *
 * This source file is subject to the GPLv3 license that is bundled
 * with this source code in the file LICENSE.
 */

use Monolog\Logger;
use Yoghi\Bundle\MaddaBundleTest\Utils\AbstractCommonLogTest;
use Yoghi\Bundle\MaddaBundle\Finder\Finder;

/**
 * @author Stefano Tamagnini <>
 */
class DDDGeneratorTest extends \PHPUnit_Framework_TestCase
{
    use AbstractCommonLogTest;

    public function testFindYmlOnVfs()
    {
        // $directoryOutput = self::$directoryV->url().'/output';
        // if (!file_exists($directoryOutput)) {
        //     mkdir($directoryOutput, 0700, true);
        // }
        //
        // $data = file_get_contents(__DIR__.'/../Resources/basemodel/model.yml');
        // file_put_contents($directoryOutput.'/test.yml', $data);
        //
        // $finder = new Finder();
        // $finder->setLogger($this->logger);
        // $finder->search($directoryOutput, 'yml');
        // $actual = $finder->getYmlFiles();
        // $this->assertCount(1, $actual, 'yml file not found');
    }
}
