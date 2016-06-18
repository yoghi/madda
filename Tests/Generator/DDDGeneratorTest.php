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
use Yoghi\Bundle\MaddaBundleTest\Utils\VfsAdapter;
use Yoghi\Bundle\MaddaBundleTest\Utils\AbstractCommonLogTest;
use Yoghi\Bundle\MaddaBundle\Finder\Finder;
use Yoghi\Bundle\MaddaBundle\Generator\DDDGenerator;
use Yoghi\Bundle\MaddaBundleTest\Utils\FileCompare;

/**
 * @author Stefano Tamagnini <>
 */
class DDDGeneratorTest extends \PHPUnit_Framework_TestCase
{
    use AbstractCommonLogTest;
    use FileCompare;

    public function testFindYmlOnVfs()
    {
        $directoryOutput = self::$directoryV->url().'/output';
        $directorySrcGen = self::$directoryV->url().'/src-gen';
        $resourcesDir = __DIR__.'/../Resources';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
        }
        if (!file_exists($directorySrcGen)) {
            mkdir($directorySrcGen, 0700, true);
        }
        $data = file_get_contents($resourcesDir.'/ddd/real.yml');
        file_put_contents($directoryOutput.'/test.yml', $data);

        $dg = new DDDGenerator();
        $dg->setLogger($this->logger);
        $dg->analyze($directoryOutput.'/test.yml');
        $dg->generate(new VfsAdapter($directorySrcGen));

        // $finder = new Finder();
        // $finder->setLogger($this->logger);
        // $finder->search($directoryOutput, 'yml');
        // $actual = $finder->getYmlFiles();
        // $this->assertCount(1, $actual, 'yml file not found');
        // echo $this->readLog();
        // $data = file_get_contents("vfs://root/src-gen/BitPrepared/Bundle/FormazioneBundle/Domain/Events/DomainEvent.php");
        // echo $data;

        $namespace = 'BitPrepared/Bundle/FormazioneBundle/Domain/Events';
        $className = 'DomainEvent';
        $this->compareFilePhp($resourcesDir.'/ddd/generated/'.$namespace, $namespace, $className, $directorySrcGen);

        // $errors = $dg->getErrors();
        // $this->assertCount(0, $errors, 'errori durante la generazione');
    }
}
