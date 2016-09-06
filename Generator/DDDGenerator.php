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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Reader model file
     * @var \Yoghi\Bundle\MaddaBundle\Model\Reader
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
     * Fields of model classes
     * @var array
     */
    private $fieldsClass;

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

            //FIXME: switch with $dddType as key

            $generated = false;
            $dddType = $properties['ddd']['type'];
            if (in_array($dddType, array('interface'))) {
                $g = new ClassGenerator($namespace, $className, $classComments);
                $g->setLogger($this->logger);
                $config = new ClassConfig();
                $config->isInterface = true;
                $g->generateClassType($properties, $this->modelClass, $this->modelComments, $config);
                $g->createFileOnDir($directoryOutput);
                $generated = true; //FIXME: use $g for determinate! -> take error from generator
                if ($generated) {
                    $this->fieldsClass[$namespace.'\\'.$className] = $properties['fields']; //ONLY IF VALID!!!
                }
                // DOMANDA: perche' non passarle tutte??
                // if (array_key_exists('fields', $properties)) {
                //     $types_field[$className] = $properties['fields'];
                // }
                // $this->generateClassType($fileInterface, $interface, $properties, $types_reference, $types_description, false, true, true, false, false, $filesystem, $io);
            }

            // NOTE: class aren't ddd type, we haven't section on ddd definition
            if (in_array($dddType, array('class'))) {
                $g = new ClassGenerator($namespace, $className, $classComments);
                $g->setLogger($this->logger);
                $config = new ClassConfig();
                $config->isInterface = false;
                $config->haveConstructor = true;
                $g->generateClassType($properties, $this->modelClass, $this->modelComments, $config);
                $g->createFileOnDir($directoryOutput);
                $generated = true; //FIXME: use $g for determinate! -> take error from generator
                if ($generated) {
                    $this->fieldsClass[$namespace.'\\'.$className] = $properties['fields']; //ONLY IF VALID!!!
                }
                // DOMANDA: perche' non passarle tutte??
                // if (array_key_exists('fields', $properties)) {
                //     $types_field[$className] = $properties['fields'];
                // }
                // $this->generateClassType($fileInterface, $interface, $properties, $types_reference, $types_description, false, true, true, false, false, $filesystem, $io);
            }

            if (in_array($dddType, array('events'))) {
                //FIXME: impossible! events exist in relation on aggregateRoot
                $this->error('events exist in relation on aggregateRoot', array( 'class' => $className ));
                $this->errors[] = 'events exist in relation on aggregateRoot, event class '.$className.' cannot exist!';
            }

            if (!$generated) {
                $dddDefinition = $this->rym->getDomainDefinitionAttributes($dddType);

                if (is_null($dddDefinition)) {
                    $this->error('Missing ddd reference for : '.$dddType.' into '.$className, array( 'class' => $className ));
                    $this->errors[] = 'Missing ddd reference for : '.$dddType.' into '.$className;
                } else {
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
                        $dddExtendDefinition = $dddDefinition['extend'];
                        if (!array_key_exists('extend', $properties)) {
                            $properties['extend'] = $dddExtendDefinition; //No multi-inheritance
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

                    if (array_key_exists('events', $properties)) {
                        //genero altre classi per ogni evento!
                        $eventsProperties = $this->rym->getDomainDefinitionAttributes('events');
                        $eventsNamespace = $eventsProperties['package'];
                        $eventsImplement = '';
                        if (array_key_exists('implement', $eventsProperties)) {
                            $eventsImplement = $eventsProperties['implement'];
                        }

                        $eventsExtend = '';
                        if (array_key_exists('extend', $eventsProperties)) {
                            $eventsExtend = $eventsProperties['extend'];
                        }

                        if (!array_key_exists($eventsImplement, $this->modelClass)) {
                            $this->error('Missing implement class '.$eventsImplement, array( 'class' => $className ));
                            $this->errors[] = 'Missing implement '.$eventsImplement.' for '.$className;
                            continue;
                        }
                        $namespaceImplementClass = $this->modelClass[$eventsImplement];
                        $eventsImplementFull = $namespaceImplementClass.'\\'.$eventsImplement;

                        $eventsField = array();
                        if (array_key_exists('fields', $eventsProperties)) {
                            foreach ($eventsProperties['fields'] as $key => $value) {
                                $eventsField[$key] = $value;
                            }
                        }

                        //field's inheritance
                        if (array_key_exists($eventsImplementFull, $this->fieldsClass)) {
                            $fieldsImplementClass = $this->fieldsClass[$eventsImplementFull];
                            foreach ($fieldsImplementClass as $key => $value) {
                                $eventsField[$key] = $value;
                            }
                        }

                        $eventsToCreate = array();
                        if (array_key_exists('events', $properties)) {
                            $eventsToCreate = $properties['events'];
                        }

                        if (array_key_exists('events', $dddDefinition)) {
                            $eventsToCreate = array_merge($dddDefinition['events'], $eventsToCreate);
                        }

                        foreach ($eventsToCreate as $event) {
                            $eventClassName = $className . str_replace('_', '', ucwords($event, '_')).'Event';
                            $eventClassComments = 'Event '.$event.' for Aggregate Root '.$className;

                            $propertiesEventClass = array();
                            if (!empty($eventsExtend)) {
                                $propertiesEventClass['extend'] = $eventsExtend;
                            }
                            if (!empty($eventsImplement)) {
                                $propertiesEventClass['implements'] = $eventsImplementFull;
                            }

                            $propertiesEventClass['fields'] = $eventsField;

                            $this->info('Create Event', array('event' => $event, 'class' => $className, 'extend' =>$eventsExtend, 'implement' => $eventsImplementFull, 'fields' => $eventsField));

                            $g = new ClassGenerator($eventsNamespace, $eventClassName, $eventClassComments);
                            $g->setLogger($this->logger);
                            $config = new ClassConfig();
                            $config->isInterface = false;
                            $config->haveConstructor = true;
                            $config->isFinalClass = true; //don't wnat cycle dependency
                            $config->haveGetter = true;
                            $config->haveSetter = false;
                            $g->generateClassType($propertiesEventClass, $this->modelClass, $this->modelComments, $config);
                            $g->createFileOnDir($directoryOutput);
                            $generated = true;
                        }

                        if ($generated) {
                            $this->fieldsClass[$namespace.'\\'.$className] = $eventsField; //ONLY IF VALID!!!
                        }
                    }

                    if (array_key_exists('enum', $properties)) {
                        $enumClassList = $properties['enum'];
                        foreach ($enumClassList as $enumClassName) {
                            $enumNamespace = $namespace.'\\'.$className;
                            $propertiesEnumClass = array(
                              'extend' => $namespace.'\\'.$className
                            );
                            $actionName = 'instance';
                            $propertiesEnumClass['methods'] = array();
                            $propertiesEnumClass['methods'][$actionName] = array();
                            $propertiesEnumClass['methods'][$actionName]['params'] = array();
                            $propertiesEnumClass['methods'][$actionName]['static'] = true;
                            $propertiesEnumClass['methods'][$actionName]['@return'] = $enumNamespace.'\\'.$enumClassName;
                            $body = 'self::$instance = new '.$enumClassName.'();';
                            $body .= 'return self::$instance;';
                            $propertiesEnumClass['methods'][$actionName]['body'] = $body;

                            //TODO: pensare se qui va bene cosi... potrebbe il ClassGenerator sapere come fare questo costruttore?
                            $actionName = '__construct';
                            $propertiesEnumClass['methods'][$actionName] = array();
                            $propertiesEnumClass['methods'][$actionName]['visibility'] = 'private';
                            $propertiesEnumClass['methods'][$actionName]['params'] = array();
                            $propertiesEnumClass['methods'][$actionName]['static'] = false;
                            $propertiesEnumClass['methods'][$actionName]['description'] = 'costruttore';
                            $body = '$this->name = \''.$enumClassName.'\';';
                            $propertiesEnumClass['methods'][$actionName]['body'] = $body;

                            $enumClassComments = 'Child of '.$className.' '.$enumClassName;
                            $g = new ClassGenerator($enumNamespace, $enumClassName, $enumClassComments);
                            $g->setLogger($this->logger);
                            $configEnum = new ClassConfig();
                            $configEnum->isInterface = false;
                            $configEnum->haveConstructor = true;
                            $configEnum->isFinalClass = true; //don't wnat cycle dependency
                            $configEnum->haveGetter = true;
                            $configEnum->haveSetter = false;
                            $g->generateClassType($propertiesEnumClass, $this->modelClass, $this->modelComments, $configEnum);
                            $g->createFileOnDir($directoryOutput);
                            $generated = true;
                        }

                        $properties['fields']['name'] = array(
                          "primitive" => "string",
                          "description" => "nome esplicativo della enum",
                          "getter" => true
                        );

                        $config = new ClassConfig();
                        $config->isInterface = false;
                        $config->haveConstructor = false;
                        $config->isFinalClass = false;
                        $config->isEnum = true;
                        $config->haveGetter = $createGetter;
                        $config->haveSetter = $createSetter;
                    } else {
                        $config = new ClassConfig();
                        $config->isInterface = false;
                        $config->haveConstructor = true;
                        $config->isFinalClass = true; //don't wnat cycle dependency
                        $config->haveGetter = $createGetter;
                        $config->haveSetter = $createSetter;
                    }

                    //NORMAL GENERATION
                    $g = new ClassGenerator($namespace, $className, $classComments);
                    $g->setLogger($this->logger);
                    $g->generateClassType($properties, $this->modelClass, $this->modelComments, $config);
                    $g->createFileOnDir($directoryOutput);
                    $generated = true;
                }
            }

            if ($generated) {
                $this->modelClass[$className] = $namespace;
                $this->modelComments[$className] = $classComments;
            }
        } //end class generation
    }
}
