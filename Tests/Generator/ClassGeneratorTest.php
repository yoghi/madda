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
        $gClassgenClassgen = new ClassGenerator("TestNamespace", "emptyClass");
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(); // 'fields', 'extend', 'implements'
        $types_reference = array(); //dipendenza dei field da altre classi
        $types_description = array(); //descrizione delle classi da cui dipendono i field
        $gClassgenClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $gClassgenClassgen->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/php/EmptyClass.php');
        $this->assertSame($actual, $expected, 'Classe EmptyClass invalid');
    }

    public function testFirstClassGenerator()
    {
        $gClassgenClassgen = new ClassGenerator("TestNamespace", "FirstClass");
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "IClass"
        );
        $types_reference = array();
        $types_description = array();
        $gClassgenClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $actual = $gClassgenClassgen->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/php/FirstClass.php');
        $this->assertSame($expected, $actual, 'Classe FirstClass invalid');
    }

    public function testTraitsGenerator()
    {
        $namespace = "TestNamespace";
        $className = "TraitsTestClass";
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
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
        $gClassgenClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }

    public function testMultiTraitsGenerator()
    {
        $namespace = "TestNamespace";
        $className = "MultiTraitsTestClass";
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
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
        $gClassgenClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }



    public function testImplementsClassWithNamespaceGenerator()
    {
        $namespace = "TestNamespace";
        $className = "ImplementsClassWithNamespace";
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = array(
          "extend" => "ExtendClass",
          "implements" => "NS\IClass"
        );
        $types_reference = array();
        $types_description = array();
        $gClassgenClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }

    public function testMultiImplementsClassWithNamespaceGenerator()
    {
        $namespace = "TestNamespace";
        $className = "MultiImplementsClassWithNamespace";
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
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
        $gClassgenClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethod()
    {
        $namespace = "TestNamespace";
        $className = "ImplementsClassWithNamespaceAndField";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodStatic()
    {
        $namespace = "TestNamespace";
        $className = "ImplementsClassWithNamespaceAndFieldStatic";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependency()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWD";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizialize()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDA";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClass()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDAC";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetter()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACG";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAll()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGA";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAll()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGSA";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAllStaticExplicit()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGSAS";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAllStatic()
    {
        $namespace = "TestNamespace";
        $className = "ICWNAFWDACGSAS";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testSameNamespaceClassDependency()
    {
        $namespace = "TestNamespace";
        $className = "SNCD";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testEnum()
    {
        $namespace = "TestNamespace";
        $className = "EnumTest";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testSingleton()
    {
        $namespace = "TestNamespace";
        $className = "SingletonClass";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->is_singleton = true;
        $properties = array();
        $types_reference = array();
        $types_description = array();
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testInterface()
    {
        $namespace = "TestNamespace";
        $className = "Itest";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testInterfaceWithGetter()
    {
        $namespace = "TestNamespace";
        $className = "ItestWithGetter";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testInterfaceWithGetterAndSetter()
    {
        $namespace = "TestNamespace";
        $className = "ItestWithGetterSetter";
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
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
        $gClassgen->generateClassType($properties, $types_reference, $types_description, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
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

    private function compareFileGenerated($resourcesDir, $namespace, $className, ClassGenerator $gClassgen)
    {
        $directoryOutput = $this->generateDestDir($namespace);
        $gClassgen->createFileOnDir(new VfsAdapter($directoryOutput, 0));
        $this->compareFilePhp($resourcesDir, $namespace, $className, $directoryOutput);
    }
}
