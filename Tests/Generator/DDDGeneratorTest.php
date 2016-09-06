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

    public function testGenerateDDDOnVfs()
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

        $dddg = new DDDGenerator();
        $dddg->setLogger($this->logger);
        $dddg->analyze($directoryOutput.'/test.yml');
        $dddg->generate(new VfsAdapter($directorySrcGen));

        // $data = file_get_contents("vfs://root/src-gen/BitPrepared/Bundle/FormazioneBundle/Domain/Events/DomainEvent.php");
        // echo $data;

        // $finderV = new Finder();
        // $finderV->search($directorySrcGen, 'php');
        // foreach ($finderV->getFindedFiles() as $file) {
        //     $namespace = str_replace('vfs://root/src-gen/', '', pathinfo($file, PATHINFO_DIRNAME));
        //     $name = str_replace('.php', '', pathinfo($file, PATHINFO_FILENAME));
        //     $this->logger->info('$mappaToCheck[\''.$namespace.'\'] = \''.$name.'\';');
        // }
        // echo $this->readLog();
        // exit;

        $mappaToCheck = [];
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Events'][] = 'DomainEvent';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Events'][] = 'SpiegazioneSessioneCampoAddDocumentEvent';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Events'][] = 'SpiegazioneSessioneCampoCreateEvent';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Events'][] = 'SpiegazioneSessioneCampoDeleteEvent';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CFMLC';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CFMEG';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CFMRS';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CFT';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CAMLC';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CAMEG';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CAMRS';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject/TipologiaCampo'][] = 'CCG';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject'][] = 'TipologiaCampo';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject'][] = 'Sessione';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject'][] = 'SessioniArray';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Entity'][] = 'SessioneCampo';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Aggregate'][] = 'SpiegazioneSessioneCampo';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Service/QueryRequest'][] = 'DettagliSessioneRequest';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Service/QueryRequest'][] = 'ElencoSessioniForTipologiaRequest';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Service/QueryRequest'][] = 'ElencoSessioniRequest';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Service/CommandRequest'][] = 'NewSessioneRequest';
        $mappaToCheck['BitPrepared/Bundle/FormazioneBundle/Domain/Classes'][] = 'SampleClassWithNamespace';
        $mappaToCheck[''][] = 'SampleClass';

        foreach ($mappaToCheck as $namespace => $classList) {
            foreach ($classList as $className) {
                $this->compareClassPhp($resourcesDir.'/ddd/generated/'.$namespace, $namespace, $className, $directorySrcGen);
            }
        }

        // $namespace = 'BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject';
        // $className = 'Sessione';
        // $this->compareClassPhp($resourcesDir.'/ddd/generated/'.$namespace, $namespace, $className, $directorySrcGen);
        //
        // $namespace = 'BitPrepared/Bundle/FormazioneBundle/Domain/ValueObject';
        // $className = 'SessioniArray';
        // $this->compareClassPhp($resourcesDir.'/ddd/generated/'.$namespace, $namespace, $className, $directorySrcGen);

        $errors = $dddg->getErrors();
        $this->assertCount(0, $errors, 'errori durante la generazione');
    }

    public function testGenerateDDDOnVfsBadEventsSpecific()
    {
        $directoryOutput = self::$directoryV->url().'/output';
        $directorySrcGen = self::$directoryV->url().'/src-gen-bad-event';
        $resourcesDir = __DIR__.'/../Resources';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
        }
        if (!file_exists($directorySrcGen)) {
            mkdir($directorySrcGen, 0700, true);
        }
        $data = file_get_contents($resourcesDir.'/ddd/badEvents.yml');
        file_put_contents($directoryOutput.'/test.yml', $data);

        $dddg = new DDDGenerator();
        $dddg->setLogger($this->logger);
        $dddg->analyze($directoryOutput.'/test.yml');
        $dddg->generate(new VfsAdapter($directorySrcGen));

        $errors = $dddg->getErrors();
        $this->assertCount(1, $errors, 'errori non previsti durante la generazione');
    }

    public function testGenerateDDDOnVfsBad()
    {
        $directoryOutput = self::$directoryV->url().'/output';
        $directorySrcGen = self::$directoryV->url().'/src-gen-bad-event';
        $resourcesDir = __DIR__.'/../Resources';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
        }
        if (!file_exists($directorySrcGen)) {
            mkdir($directorySrcGen, 0700, true);
        }
        $data = file_get_contents($resourcesDir.'/ddd/bad.yml');
        file_put_contents($directoryOutput.'/test.yml', $data);

        $dddg = new DDDGenerator();
        $dddg->setLogger($this->logger);
        $dddg->analyze($directoryOutput.'/test.yml');
        $dddg->generate(new VfsAdapter($directorySrcGen));

        $errors = $dddg->getErrors();
        $this->assertCount(1, $errors, 'errori non previsti durante la generazione');
    }

    public function testGenerateDDDOnVfsBadDefinition()
    {
        $directoryOutput = self::$directoryV->url().'/output';
        $directorySrcGen = self::$directoryV->url().'/src-gen-bad-event';
        $resourcesDir = __DIR__.'/../Resources';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
        }
        if (!file_exists($directorySrcGen)) {
            mkdir($directorySrcGen, 0700, true);
        }
        $data = file_get_contents($resourcesDir.'/ddd/badDefinition.yml');
        file_put_contents($directoryOutput.'/test.yml', $data);

        $dddg = new DDDGenerator();
        $dddg->setLogger($this->logger);
        $dddg->analyze($directoryOutput.'/test.yml');
        $dddg->generate(new VfsAdapter($directorySrcGen));

        $errors = $dddg->getErrors();
        $this->assertCount(1, $errors, 'errori non previsti durante la generazione');
    }

    public function testGenerateDDDOnVfsEventsSpecific()
    {
        $directoryOutput = self::$directoryV->url().'/output';
        $directorySrcGen = self::$directoryV->url().'/src-gen-event';
        $resourcesDir = __DIR__.'/../Resources';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
        }
        if (!file_exists($directorySrcGen)) {
            mkdir($directorySrcGen, 0700, true);
        }
        $data = file_get_contents($resourcesDir.'/ddd/realEvents.yml');
        file_put_contents($directoryOutput.'/test.yml', $data);

        $dddg = new DDDGenerator();
        $dddg->setLogger($this->logger);
        $dddg->analyze($directoryOutput.'/test.yml');
        $dddg->generate(new VfsAdapter($directorySrcGen));

        // $finderV = new Finder();
        // $finderV->search($directorySrcGen, 'php');
        // foreach ($finderV->getFindedFiles() as $file) {
        //     $namespace = str_replace('vfs://root/src-gen-event/', '', pathinfo($file, PATHINFO_DIRNAME));
        //     $name = str_replace('.php', '', pathinfo($file, PATHINFO_FILENAME));
        //     $this->logger->info('$mappaToCheck[\''.$namespace.'\'] = \''.$name.'\';');
        // }
        // echo $this->readLog();
        // exit;

        $mappaToCheck = [];
        $mappaToCheck['BitPrepared/Bundle/EventBundle/Domain/Events'][] = 'DomainEvent';
        $mappaToCheck['BitPrepared/Bundle/EventBundle/Domain/Events'][] = 'SpiegazioneSessioneCampoCreateEvent';
        $mappaToCheck['BitPrepared/Bundle/EventBundle/Domain/Events'][] = 'SpiegazioneSessioneCampoDeleteEvent';
        $mappaToCheck['BitPrepared/Bundle/EventBundle/Domain/Events'][] = 'SpiegazioneSessioneCampoAddDocumentEvent';
        $mappaToCheck['BitPrepared/Bundle/EventBundle/Domain/Aggregate'][] = 'SpiegazioneSessioneCampo';

        foreach ($mappaToCheck as $namespace => $classList) {
            foreach ($classList as $className) {
                $this->compareClassPhp($resourcesDir.'/ddd/generated/'.$namespace, $namespace, $className, $directorySrcGen);
            }
        }

        $errors = $dddg->getErrors();
        $this->assertCount(0, $errors, 'errori durante la generazione');
    }
}
