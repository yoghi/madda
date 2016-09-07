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

use Yoghi\Bundle\MaddaBundleTest\Utils\AbstractCommonLogTest;
use Yoghi\Bundle\MaddaBundleTest\Utils\FileCompare;
use Yoghi\Bundle\MaddaBundleTest\Utils\PhpunitFatalErrorHandling;
use Yoghi\Bundle\MaddaBundleTest\Utils\VfsAdapter;

/**
 * @author Stefano Tamagnini <>
 */
class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    use AbstractCommonLogTest;
    use FileCompare;
    use PhpunitFatalErrorHandling;

    public function testEmptyClassGenerator()
    {
        $gClassgenClassgen = new ClassGenerator('TestNamespace', 'emptyClass');
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = []; // 'fields', 'extend', 'implements'
        $typesReferenceArray = []; //dipendenza dei field da altre classi
        $typesDescArray = []; //descrizione delle classi da cui dipendono i field
        $gClassgenClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $actual = $gClassgenClassgen->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/php/EmptyClass.php');
        $this->assertSame($actual, $expected, 'Classe EmptyClass invalid');
    }

    public function testFirstClassGenerator()
    {
        $gClassgenClassgen = new ClassGenerator('TestNamespace', 'FirstClass');
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => 'IClass',
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgenClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $actual = $gClassgenClassgen->toString();
        $expected = file_get_contents(__DIR__.'/../Resources/php/FirstClass.php');
        $this->assertSame($expected, $actual, 'Classe FirstClass invalid');
    }

    public function testTraitsGenerator()
    {
        $namespace = 'TestNamespace';
        $className = 'TraitsTestClass';
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'traits'     => 'TraitsClass',
        ];
        $typesReferenceArray = [
          'TraitsClass' => 'TraitNamespace',
        ];
        $typesDescArray = [];
        $gClassgenClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }

    public function testMultiTraitsGenerator()
    {
        $namespace = 'TestNamespace';
        $className = 'MultiTraitsTestClass';
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'traits'     => ['TraitsClass', 'TraitsClass2'],
        ];
        $typesReferenceArray = [
          'TraitsClass'  => 'TraitNamespace',
          'TraitsClass2' => 'TestNamespace',
        ];
        $typesDescArray = [];
        $gClassgenClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }

    public function testImplementsClassWithNamespaceGenerator()
    {
        $namespace = 'TestNamespace';
        $className = 'ImplementsClassWithNamespace';
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgenClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }

    public function testMultiImplementsClassWithNamespaceGenerator()
    {
        $namespace = 'TestNamespace';
        $className = 'MultiImplementsClassWithNamespace';
        $gClassgenClassgen = new ClassGenerator($namespace, $className);
        $gClassgenClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => [
            "NS\IClass",
            "NS\IClass2",
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgenClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgenClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethod()
    {
        $namespace = 'TestNamespace';
        $className = 'ImplementsClassWithNamespaceAndField';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'identificativo univoco della sessione',
            ],
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodStatic()
    {
        $namespace = 'TestNamespace';
        $className = 'ImplementsClassWithNamespaceAndFieldStatic';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'identificativo univoco della sessione',
              'static'      => true,
            ],
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependency()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWD';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
            ],
            'dependency' => [
              'class' => 'ClassDep',
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'NamespaceDep',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizialize()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWDA';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'      => 'int',
              'description'    => 'session unique identifier',
              'autoinizialize' => true,
              'default'        => 0,
            ],
            'dependency' => [
              'class' => 'ClassDep',
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'NamespaceDep',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClass()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWDAC';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
            ],
            'dependency' => [
              'class'          => 'ClassDep',
              'autoinizialize' => true,
              'default'        => 'new ClassDep();',
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'NamespaceDep',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetter()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWDACG';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
              'getter'      => true,
            ],
            'dependency' => [
              'class'          => 'ClassDep',
              'autoinizialize' => true,
              'default'        => 'new ClassDep()',
            ],
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAll()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWDACGA';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $config->haveGetter = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
            ],
            'dependency' => [
              'class'          => 'ClassDep',
              'autoinizialize' => true,
              'default'        => 'new ClassDep()',
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'NamespaceDep',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAll()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWDACGSA';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $config->haveGetter = true;
        $config->haveSetter = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
            ],
            'dependency' => [
              'class'          => 'ClassDep',
              'autoinizialize' => false,
              'default'        => 'new ClassDep()',
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'NamespaceDep',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAllStaticExplicit()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWDACGSAS';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
              'static'      => true,
              'getter'      => true,
              'setter'      => true,
            ],
            'dependency' => [
              'class'          => 'ClassDep',
              'autoinizialize' => true,
              'default'        => 'new ClassDep()',
              'static'         => true,
              'getter'         => true,
              'setter'         => true,
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'NamespaceDep',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testImplementsClassWithNamespaceAndFieldGeneratorMethodWithDependencyAutoInizializeClassWithGetterAndSetterAllStatic()
    {
        $namespace = 'TestNamespace';
        $className = 'ICWNAFWDACGSAS';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $config->haveGetter = true;
        $config->haveSetter = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
              'static'      => true,
            ],
            'dependency' => [
              'class'          => 'ClassDep',
              'autoinizialize' => true,
              'default'        => 'new ClassDep()',
              'static'         => true,
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'NamespaceDep',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testSameNamespaceClassDependency()
    {
        $namespace = 'TestNamespace';
        $className = 'SNCD';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $config->haveGetter = true;
        $config->haveSetter = true;
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => "NS\IClass",
          'fields'     => [
            'prova' => [
              'primitive'      => 'int',
              'autoinizialize' => true,
              'default'        => 0,
              'description'    => 'session unique identifier',
            ],
            'dependency' => [
              'class'          => 'ClassDep',
              'autoinizialize' => true,
              'default'        => 'new ClassDep()',
            ],
          ],
        ];
        $typesReferenceArray = [
          'ClassDep' => 'TestNamespace',
        ];
        $typesDescArray = [
          'ClassDep' => 'comment ClassDep',
        ];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testEnum()
    {
        $namespace = 'TestNamespace';
        $className = 'EnumTest';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->isEnum = true;
        $properties = [
          'fields' => [
            'name' => [
              'primitive'   => 'string',
              'description' => 'nome esplicativo della enum',
              'getter'      => true,
            ],
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testSingleton()
    {
        $namespace = 'TestNamespace';
        $className = 'SingletonClass';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->isSingleton = true;
        $properties = [];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testInterface()
    {
        $namespace = 'TestNamespace';
        $className = 'Itest';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveConstructor = true;
        $config->isInterface = true;
        $properties = [
          'fields' => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
            ],
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';

        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testInterfaceWithGetter()
    {
        $namespace = 'TestNamespace';
        $className = 'ItestWithGetter';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $config->haveGetter = true;
        $config->isInterface = true;
        $properties = [
          'fields' => [
            'prova' => [
              'primitive'   => 'int',
              'description' => 'session unique identifier',
            ],
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
        $resourcesDir = __DIR__.'/../Resources/php';
        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    public function testFirstClassMethodGenerator()
    {
        $namespace = 'TestNamespace';
        $className = 'FirstMethodClass';
        $gClassgen = new ClassGenerator($namespace, $className);
        $gClassgen->setLogger($this->logger);
        $config = new ClassConfig();
        $properties = [
          'extend'     => 'ExtendClass',
          'implements' => 'IClass',
          'methods'    => [
            'methodName' => [
              'params' => [
                'prova' => [
                  'primitive'   => 'int',
                  'description' => 'session unique identifier',
                ],
                'prova2' => [
                  'primitive'   => 'string',
                  'description' => 'campo generico',
                ],
              ],
            ],
          ],
        ];
        $typesReferenceArray = [];
        $typesDescArray = [];
        $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);

        $resourcesDir = __DIR__.'/../Resources/php';
        $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    }

    // public function testInterfaceWithGetterAndSetter()
    // {
    //     $namespace = "TestNamespace";
    //     $className = "ItestWithGetterSetter";
    //     $gClassgen = new ClassGenerator($namespace, $className);
    //     $gClassgen->setLogger($this->logger);
    //     $config = new ClassConfig();
    //     $config->haveGetter = true;
    //     $config->haveSetter = true;
    //     $config->isInterface = true;
    //     $properties = array(
    //       "fields" => array(
    //         "prova" => array(
    //           "primitive" => "int",
    //           "description" => "session unique identifier"
    //         )
    //       )
    //     );
    //     $typesReferenceArray = array();
    //     $typesDescArray = array();
    //     $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
    //     $resourcesDir = __DIR__.'/../Resources/php';
    //
    //     $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    // }
    //
    //
    // public function testStrange()
    // {
    //     $namespace = "BitPrepared\\Bundle\\FormazioneBundle\\Domain\\Events";
    //     $className = "SpiegazioneSessioneCampoDeleteEvent";
    //     $gClassgen = new ClassGenerator($namespace, $className, 'Event delete for Aggregate Root SpiegazioneSessioneCampo');
    //     $gClassgen->setLogger($this->logger);
    //     $config = new ClassConfig();
    //     $config->haveGetter = true;
    //     $config->haveSetter = false;
    //     $config->isInterface = false;
    //     $config->isFinalClass = true;
    //     $config->haveConstructor = true;
    //     $properties = array(
    //       "implements" => "BitPrepared\\Bundle\\FormazioneBundle\\Domain\\Events\\DomainEvent",
    //       "fields" => array(
    //         "occurredOn" => array(
    //           "class" => "\\DateTime",
    //           "description" => "quando accade l'evento",
    //           "default" => "new \\DateTime()",
    //           "autoinizialize" => true
    //         ),
    //         "aggregateId" => array(
    //           "primitive" => "int",
    //           "description" => "id dell'aggregato root relativo all'evento",
    //           "autoinizialize" => false
    //         ),
    //         "properties" => array(
    //           "primitive" => "array",
    //           "description" => "proprietà dell'evento",
    //           "autoinizialize" => false
    //         ),
    //       )
    //     );
    //     $typesReferenceArray = array();
    //     $typesDescArray = array();
    //     $gClassgen->generateClassType($properties, $typesReferenceArray, $typesDescArray, $config);
    //     $resourcesDir = __DIR__.'/../Resources/php';
    //
    //     $this->compareFileGenerated($resourcesDir, $namespace, $className, $gClassgen);
    // }

    /**
     * [generateDestDir description].
     *
     * @param string $namespace [description]
     *
     * @return string [description]
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
        $this->compareClassPhp($resourcesDir, $namespace, $className, $directoryOutput);
    }
}
