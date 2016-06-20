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


use Yoghi\Bundle\MaddaBundle\Model\Reader;
use Psr\Log\LoggerInterface;
use League\Flysystem\Adapter\Local;

/**
 * @author Stefano Tamagnini <>
 */
class DDDGenerator
{

    /**
     * [$logger description]
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Reader model file
     * @var Yoghi\Bundle\MaddaBundle\Model\Reader
     */
    private $rym;

    /**
     * Model classes: class -> namespace
     * @var array
     */
    private $modelClass;

    /**
     * Comments of model classes
     * @var array
     */
    private $modelComments;

    /**
     * Array process errors
     * @var array
     */
    private $errors;

    public function __construct()
    {
        $this->rym = new Reader();
        $this->errors = array();
        $this->modelClass = array();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * [analyze description]
     * @param  [type] $fullPathFile [description]
     * @return [type]               [description]
     */
    public function analyze($fullPathFile)
    {
        $this->rym->readYaml($fullPathFile);
    }

    private function info($message, $context = array())
    {
        if (!is_null($this->logger)) {
            $this->logger->info($message, $context);
        }
    }

    private function error($message, $context = array())
    {
        if (!is_null($this->logger)) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * errori durante la generazione
     * @return array of string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * [generate description]
     * @param  String $directoryOutput directory where write generated class
     */
    public function generate(Local $directoryOutput)
    {
        //$is_constructor_enable = true;
        //$ddd_is_root_aggregate = false;


        $specListClasses = $this->rym->getClassesDefinition();
        foreach ($specListClasses as $className => $properties) {
            $this->info('Generate class', array('class' => $className)); //, 'properties' => $properties
            if (!array_key_exists('ddd', $properties)) {
                $this->error('missing ddd section into yml for class', array( 'class' => $className ));
                $this->errors[] = 'missing ddd section into yml for class '.$className;
                $this->info('force '.$className.' to type class');
                $properties['ddd'] = array();
                $properties['ddd']['type'] = 'class';
            }

            $namespace = '';
            if (array_key_exists('namespace', $properties)) {
                $namespace = $properties['namespace'];
            }

            $classComments = 'No comment found on ddd model';
            if (array_key_exists('description', $properties)) {
                $classComments = $properties['description'];
                $this->info('Found description :'.$classComments);
            }

            $generated = false;
            $dddType = $properties['ddd']['type'];
            //FIXME: , 'class' gestito diversamente
            if (in_array($dddType, array('interface'))) {
                $g = new ClassGenerator($namespace, $className, $classComments);
                $g->setLogger($this->logger);
                $config = new ClassConfig();
                $config->isInterface = true;
                $g->generateClassType($properties, $this->modelClass, $this->modelComments, $config);
                $g->createFileOnDir($directoryOutput);
                $generated = true; //FIXME: use $g for determinate! -> take error from generator
                // DOMANDA: perche' non passarle tutte??
                // if (array_key_exists('fields', $properties)) {
                //     $types_field[$className] = $properties['fields'];
                // }
                // $this->generateClassType($fileInterface, $interface, $properties, $types_reference, $types_description, false, true, true, false, false, $filesystem, $io);
            }

            if (in_array($dddType, array('class'))) {
                $g = new ClassGenerator($namespace, $className, $classComments);
                $g->setLogger($this->logger);
                $config = new ClassConfig();
                $config->isInterface = false;
                $config->haveConstructor = true;
                $g->generateClassType($properties, $this->modelClass, $this->modelComments, $config);
                $g->createFileOnDir($directoryOutput);
                $generated = true; //FIXME: use $g for determinate! -> take error from generator
                // DOMANDA: perche' non passarle tutte??
                // if (array_key_exists('fields', $properties)) {
                //     $types_field[$className] = $properties['fields'];
                // }
                // $this->generateClassType($fileInterface, $interface, $properties, $types_reference, $types_description, false, true, true, false, false, $filesystem, $io);
            }

            if (!$generated) {
                $dddDefinition = $this->rym->getDomainDefinitionAttributes($dddType);

                if (is_null($dddDefinition)) {
                    $this->error('Missing ddd reference for : '.$dddType.' into '.$className, array( 'class' => $className ));
                    $this->errors[] = 'Missing ddd reference for : '.$dddType.' into '.$className;
                }

                if (array_key_exists('package', $dddDefinition)) {
                    $namespace = $dddDefinition['package'];
                }

                if (empty($namespace)) {
                    $this->error('Missing namespace', array( 'class' => $className ));
                    $this->errors[] = 'Missing namespace for '.$className;
                }

                $createGetter = false;
                $createSetter = false;
                if (array_key_exists('getter', $dddDefinition)) {
                    $createGetter = $dddDefinition['getter'];
                }
                if (array_key_exists('setter', $dddDefinition)) {
                    $createSetter = $dddDefinition['setter'];
                }

                $isRootAggregate = $dddType == 'aggregate' && isset($properties['ddd']['root']) && boolval($properties['ddd']['root']) ? true : false;

                $this->info('Method required', array( 'class' => $className, 'getter' => $createGetter, 'setter' => $createSetter, 'aggregateRoot' => $isRootAggregate ));

                if (array_key_exists('extend', $dddDefinition)) {
                    $ddd_extend = $dddDefinition['extend'];
                    if (!array_key_exists('extend', $properties)) {
                        $properties['extend'] = $ddd_extend; //No multi-inheritance
                    }
                }

                $dddReferenceFields = array();
                if (array_key_exists('fields', $dddDefinition)) {
                    foreach ($dddDefinition['fields'] as $key => $value) {
                        $dddReferenceFields[$key] = $value;
                    }
                }

                //TODO: gestire gli [] dentro la definizione del modello se serve...

                //TODO: aggiungere le validazioni
                // validationRule:
                //   events:
                //     create:
                //       fields: [ id, sessione, tipologiaCampo]
                //     delete:
                //       fields: [ id ]
                //     addDocument:
                //       fields: [ id, documentoCorrelato ]

                $g = new ClassGenerator($namespace, $className, $classComments);
                $g->setLogger($this->logger);
                $config = new ClassConfig();
                $config->isInterface = false;
                $config->haveConstructor = true;
                $config->isFinalClass = true; //don't wnat cycle dependency
                $config->haveGetter = $createGetter;
                $config->haveSetter = $createSetter;
                $g->generateClassType($properties, $this->modelClass, $this->modelComments, $config);
                $g->createFileOnDir($directoryOutput);
            }

            $this->modelClass[$className] = $namespace;
            $this->modelComments[$className] = $classComments;
        } //end class generation
    }
}
