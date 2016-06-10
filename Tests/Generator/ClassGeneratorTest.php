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
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Yoghi\Bundle\MaddaBundle\Generator\ClassGenerator;

require_once __DIR__.'/SplFileInfo.php';
require_once __DIR__.'/VfsAdapter.php';

/**
 * @author Stefano Tamagnini <>
 */
class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{

    private static $directoryV;
    private $logger;

    public static function setUpBeforeClass()
    {
        self::$directoryV = vfsStream::setup();
    }

    public function setUp()
    {
        $this->logger = new Logger('phpunit-logger');
        $directoryLogOutput = self::$directoryV->url().'/log';
        if (!file_exists($directoryLogOutput)) {
            mkdir($directoryLogOutput, 0700, true);
        }
        $output = "%level_name% > %message% %context% %extra%\n";
        $formatter = new LineFormatter($output);
        $handler = new StreamHandler($directoryLogOutput.'/phpunit.log', Logger::DEBUG, true, null, false);
        touch($directoryLogOutput.'/phpunit.log');
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
        $this->logger->info('Avviato test -> '.$this->getName());
    }

    public function tearDown()
    {
        $fileLog = self::$directoryV->url().'/log/phpunit.log';
        if ($this->hasFailed()) {
            echo "\n---- LOG ----\n";
            if (is_readable($fileLog)) {
                echo file_get_contents($fileLog);
            }
            echo "------------\n";
        }
        if (file_exists($fileLog)) {
            unlink($fileLog);
        }
        $this->logger = null;
    }

    public function testEmptyClassGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "emptyClass");
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->is_enum = true;
        $properties = array(); // 'fields', 'extend', 'implements'
        $types_reference = array(); //dipendenza dei field da altre classi
        $types_description = array(); //descrizione delle classi da cui dipendono i field
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $g->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/EmptyClass.php');
        $this->assertSame($actual, $expected, 'Classe EmptyClass invalid');
    }

    public function testFirstClassGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "FirstClass");
        $g->setLogger($this->logger);
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
        $namespace = "TestNamespace";
        $className = "ImplementsClassWithNamespace";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass"
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethod()
    {
        $namespace = "TestNamespace";
        $className = "ImplementsClassWithNamespaceAndField";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
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
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodStatic()
    {
        $namespace = "TestNamespace";
        $className = "ImplementsClassWithNamespaceAndFieldStatic";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "identificativo univoco della sessione",
              "static" => true
            )
          )
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependency()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWD";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier"
            ),
            "dependency" => array(
              "class" => "classDep"
            )
          )
        );
        $types_reference = array(
          "classDep" => "NamespaceDep"
        );
        $types_description = array(
          "classDep" => "comment classDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizialize()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDA";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier",
              "autoinizialize" => true,
              "default" => 0
            ),
            "dependency" => array(
              "class" => "classDep"
            )
          )
        );
        $types_reference = array(
          "classDep" => "NamespaceDep"
        );
        $types_description = array(
          "classDep" => "comment classDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClass()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDAC";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier"
            ),
            "dependency" => array(
              "class" => "classDep",
              "autoinizialize" => true,
              "default" => "new classDep()"
            )
          )
        );
        $types_reference = array(
          "classDep" => "NamespaceDep"
        );
        $types_description = array(
          "classDep" => "comment classDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetter()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACG";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier",
              "getter" => true
            ),
            "dependency" => array(
              "class" => "classDep",
              "autoinizialize" => true,
              "default" => "new classDep()"
            )
          )
        );
        $types_reference = array(
          "classDep" => "NamespaceDep"
        );
        $types_description = array(
          "classDep" => "comment classDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAll()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGA";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $config->create_getter = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier"
            ),
            "dependency" => array(
              "class" => "classDep",
              "autoinizialize" => true,
              "default" => "new classDep()"
            )
          )
        );
        $types_reference = array(
          "classDep" => "NamespaceDep"
        );
        $types_description = array(
          "classDep" => "comment classDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAll()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGSA";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $config->create_getter = true;
        $config->create_setter = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier"
            ),
            "dependency" => array(
              "class" => "classDep",
              "autoinizialize" => true,
              "default" => "new classDep()"
            )
          )
        );
        $types_reference = array(
          "classDep" => "NamespaceDep"
        );
        $types_description = array(
          "classDep" => "comment classDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAllStatic()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGSAS";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $config->create_getter = true;
        $config->create_setter = true;
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier",
              "static" => true
            ),
            "dependency" => array(
              "class" => "classDep",
              "autoinizialize" => true,
              "default" => "new classDep()",
              "static" => true
            )
          )
        );
        $types_reference = array(
          "classDep" => "NamespaceDep"
        );
        $types_description = array(
          "classDep" => "comment classDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testInterface()
    {
        $namespace = "TestNamespace";
        $className = "Itest";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->add_constructor = true;
        $config->is_interface = true;
        $properties = array(
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier"
            )
          )
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testInterfaceWithGetter()
    {
        $namespace = "TestNamespace";
        $className = "ItestWithGetter";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->create_getter = true;
        $config->is_interface = true;
        $properties = array(
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier"
            )
          )
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testInterfaceWithGetterAndSetter()
    {
        $namespace = "TestNamespace";
        $className = "ItestWithGetterSetter";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->create_getter = true;
        $config->create_setter = true;
        $config->is_interface = true;
        $properties = array(
          "fields" => array(
            "prova" => array(
              "primitive" => "int",
              "description" => "session unique identifier"
            )
          )
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    /**
     * [generateDestDir description]
     * @param  string $namespace [description]
     * @return string            [description]
     */
    private function generateDestDir($namespace)
    {
        $directoryOutput = self::$directoryV->url().'/output';
        if (!file_exists($directoryOutput)) {
            mkdir($directoryOutput, 0700, true);
            mkdir($directoryOutput.'/'.$namespace.'/', 0700, true);
        }
        return $directoryOutput;
    }

    /**
     * Compare generated class with expected class into resource dir
     * @param  string         $resourcesDir fullPath resources dir
     * @param  string         $namespace    namespace of class
     * @param  string         $className    class name
     * @param  ClassGenerator $g            class generator object to test
     */
    private function compareFileGenerated($resourcesDir, $namespace, $className, ClassGenerator $g)
    {
        $fileInput = $resourcesDir.'/'.$className.'.php';
        $directoryOutput = $this->generateDestDir($namespace);
        $fileName = $className.'.php';
        $fileOutput = $directoryOutput . '/'.$namespace. '/'. $fileName;

        $g->createFileOnDir(new VfsAdapter($directoryOutput, 0));

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
