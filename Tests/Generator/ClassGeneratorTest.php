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

use Yoghi\Bundle\MaddaBundle\Generator\ClassGenerator;
use Yoghi\Bundle\MaddaBundleTest\Utils\VfsAdapter;
use Yoghi\Bundle\MaddaBundleTest\Utils\SplFileInfo;
use Yoghi\Bundle\MaddaBundleTest\Utils\AbstractCommonLogTest;
use Yoghi\Bundle\MaddaBundleTest\Utils\FileCompare;

/**
 * @author Stefano Tamagnini <>
 */
class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    use AbstractCommonLogTest;
    use FileCompare;

    public function testEmptyClassGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "emptyClass");
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(); // 'fields', 'extend', 'implements'
        $types_reference = array(); //dipendenza dei field da altre classi
        $types_description = array(); //descrizione delle classi da cui dipendono i field
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $g->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/php/EmptyClass.php');
        $this->assertSame($actual, $expected, 'Classe EmptyClass invalid');
    }

    public function testFirstClassGenerator()
    {
        $g = new ClassGenerator("TestNamespace", "FirstClass");
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "IClass"
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $g->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/php/FirstClass.php');
        $this->assertSame($expected, $actual, 'Classe FirstClass invalid');
    }

    public function testTraitsGenerator()
    {
        $namespace = "TestNamespace";
        $className = "TraitsTestClass";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "traits" => "TraitsClass"
        );
        $types_reference = array(
          'TraitsClass' => 'TraitNamespace'
        );
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testMultiTraitsGenerator()
    {
        $namespace = "TestNamespace";
        $className = "MultiTraitsTestClass";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass",
          "traits" => array("TraitsClass","TraitsClass2")
        );
        $types_reference = array(
          'TraitsClass' => 'TraitNamespace',
          'TraitsClass2' => 'TestNamespace'
        );
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
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
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testMultiImplementsClassWithNamespaceGenerator()
    {
        $namespace = "TestNamespace";
        $className = "MultiImplementsClassWithNamespace";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => array(
            "NS\IClass",
            "NS\IClass2"
          )
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
        $resourcesDir = __DIR__.'/../Resources/php';

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
        $resourcesDir = __DIR__.'/../Resources/php';

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
              "class" => "ClassDep"
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "NamespaceDep"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
              "class" => "ClassDep"
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "NamespaceDep"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
              "class" => "ClassDep",
              "autoinizialize" => true,
              "default" => "new ClassDep();"
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "NamespaceDep"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
              "class" => "ClassDep",
              "autoinizialize" => true,
              "default" => "new ClassDep()"
            )
          )
        );
        $types_reference = array();
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
              "class" => "ClassDep",
              "autoinizialize" => true,
              "default" => "new ClassDep()"
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "NamespaceDep"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
              "class" => "ClassDep",
              "autoinizialize" => true,
              "default" => "new ClassDep()"
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "NamespaceDep"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAllStaticExplicit()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGSAS";
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
              "static" => true,
              "getter" => true,
              "setter" => true
            ),
            "dependency" => array(
              "class" => "ClassDep",
              "autoinizialize" => true,
              "default" => "new ClassDep()",
              "static" => true,
              "getter" => true,
              "setter" => true
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "NamespaceDep"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
              "class" => "ClassDep",
              "autoinizialize" => true,
              "default" => "new ClassDep()",
              "static" => true
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "NamespaceDep"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testSameNamespaceClassDependency()
    {
        $namespace = "TestNamespace";
        $className = "SNCD";
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
              "autoinizialize" => true,
              "default" => 0,
              "description" => "session unique identifier"
            ),
            "dependency" => array(
              "class" => "ClassDep",
              "autoinizialize" => true,
              "default" => "new ClassDep()"
            )
          )
        );
        $types_reference = array(
          "ClassDep" => "TestNamespace"
        );
        $types_description = array(
          "ClassDep" => "comment ClassDep"
        );
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testEnum()
    {
        $namespace = "TestNamespace";
        $className = "EnumTest";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->is_enum = true;
        $properties = array(
          "fields" => array(
            "name" => array(
              "primitive" => "string",
              "description" => "nome esplicativo della enum",
              "getter" => true
            )
          )
        );
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $g);
    }

    public function testSingleton()
    {
        $namespace = "TestNamespace";
        $className = "SingletonClass";
        $g = new ClassGenerator($namespace, $className);
        $g->setLogger($this->logger);
        $config = new ClassConfig();
        $config->is_singleton = true;
        $properties = array();
        $types_reference = array();
        $types_description = array();
        $g->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

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
        $resourcesDir = __DIR__.'/../Resources/php';

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
        $resourcesDir = __DIR__.'/../Resources/php';

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
        $resourcesDir = __DIR__.'/../Resources/php';

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

    private function compareFileGenerated($resourcesDir, $namespace, $className, ClassGenerator $g)
    {
        $directoryOutput = $this->generateDestDir($namespace);
        $g->createFileOnDir(new VfsAdapter($directoryOutput, 0));
        $this->compareFilePhp($resourcesDir, $namespace, $className, $directoryOutput);
    }
}
