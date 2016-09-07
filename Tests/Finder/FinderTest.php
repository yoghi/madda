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

use Yoghi\Bundle\MaddaBundleTest\Utils\AbstractCommonLogTest;


/**
 * @author Stefano Tamagnini <>
 */
class FinderTest extends \PHPUnit_Framework_TestCase
{
    use AbstractCommonLogTest;

    public function testFindYml()
    {
        $finder = new Finder();
        $finder->setLogger($this->logger);
        $finder->search(__DIR__.'/../Resources/finder', 'yml');
        $actual = $finder->getFindedFiles();
        $this->logger->info('File trovati '.count($actual));
        $this->assertCount(3, $actual, 'yml file not found');

        $names = [];
        foreach ($actual as $file) {
            $names[] = pathinfo($file, PATHINFO_FILENAME);
        }

        $expected = ['model', 'emptyModel', 'invalidModel'];
        $diff = array_diff($names, $expected);

        $this->assertEmpty($diff, 'yml not found');
    }

    public function testFindYmlOnVfs()
    {
        $directoryOutput = self::$directoryV->url().'/output';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
        }

        $data = file_get_contents(__DIR__.'/../Resources/finder/basemodel/model.yml');
        file_put_contents($directoryOutput.'/test.yml', $data);

        $finder = new Finder();
        $finder->setLogger($this->logger);
        $finder->search($directoryOutput, 'yml');
        $actual = $finder->getFindedFiles();
        $this->assertCount(1, $actual, 'yml file not found');
    }
}
