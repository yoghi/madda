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

use Yoghi\Bundle\MaddaBundle\Generator\RestGenerator;
use Yoghi\Bundle\MaddaBundleTest\Utils\VfsAdapter;
use Yoghi\Bundle\MaddaBundleTest\Utils\SplFileInfo;
use Yoghi\Bundle\MaddaBundleTest\Utils\AbstractCommonLogTest;
use Yoghi\Bundle\MaddaBundleTest\Utils\FileCompare;
use Yoghi\Bundle\MaddaBundleTest\Utils\PhpunitFatalErrorHandling;
use Yoghi\Bundle\MaddaBundle\Finder\Finder;

/**
 * @author Stefano Tamagnini <>
 */
class RestGeneratorTest extends \PHPUnit_Framework_TestCase
{
    use AbstractCommonLogTest;
    use FileCompare;
    use PhpunitFatalErrorHandling;

    public function testImpresaRestGenerator()
    {
        $directoryOutput = self::$directoryV->url().'/output';

        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
        }

        $resourcesDir = __DIR__.'/../Resources';

        $rgen = new RestGenerator();
        $rgen->setLogger($this->logger);
        $rgen->generateRest($resourcesDir.'/raml/api.raml', new VfsAdapter($directoryOutput));

        $finderV = new Finder();
        $finderV->search($directoryOutput, 'php');
        foreach ($finderV->getFindedFiles() as $file) {
            $namespace = str_replace('vfs://root/output/', '', pathinfo($file, PATHINFO_DIRNAME));
            $name = str_replace('.php', '', pathinfo($file, PATHINFO_FILENAME));
            $this->logger->info('$mappaToCheck[\''.$namespace.'\'] = \''.$name.'\';');
        }
        echo $this->readLog();
        // exit;

        $mappaToCheck = [];
        $mappaToCheck['AppBundle/Controller'][] = 'StatusController';
        $mappaToCheck['AppBundle/Controller'][] = 'BadgeController';
        $mappaToCheck['AppBundle/Controller'][] = 'WorkspaceController';
        $mappaToCheck['AppBundle/Controller'][] = 'StreamController';

        foreach ($mappaToCheck as $namespace => $classList) {
            foreach ($classList as $className) {
                $this->compareClassPhp($resourcesDir.'/raml/generated/'.$namespace, $namespace, $className, $directoryOutput, true);
            }
        }

        $routingFile = 'AppBundle/Resources/config/routing.yml';
        $this->compareFile($resourcesDir, $directoryOutput, $routingFile, true);

        $errors = $rgen->getErrors();
        $this->assertCount(0, $errors, 'errori durante la generazione');
    }
}
