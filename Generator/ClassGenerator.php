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

 use Nette\PhpGenerator\ClassType;
 use Nette\PhpGenerator\PhpLiteral;
 use Nette\PhpGenerator\Method;
 use Nette\PhpGenerator\PhpFile;
 use League\Flysystem\Filesystem;
 use League\Flysystem\Adapter\Local;
 use Psr\Log\LoggerInterface;

 /**
 * @author Stefano Tamagnini <>
 */
class ClassGenerator
{
    /**
     * [$currentClass description]
     * @var Nette\PhpGenerator\ClassType
     */
    private $currentClass;
    private $currentFile;
    private $errors;

    /**
     * [$logger description]
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct($namespace, $class_name)
    {
        $this->currentFile = new PhpFile;
        $this->currentClass = $this->currentFile->addClass($namespace.'\\'.ucfirst($class_name));
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->info('Creazione classe', array( $this->currentClass->getName()));
    }

    /**
     * Aggiungere il costruttore
     */
    private function addConstructor()
    {
        if (isset($this->logger)) {
            $this->logger->info('Aggiungo costruttore', array( 'class' => $this->currentClass->getName() ));
        }
        $mc = $this->currentClass->addMethod('__construct');
        $mc->setStatic(false);
        $mc->setVisibility('public');
        $mc->addDocument('costruttore');
        $mc->setFinal(true);
        return $mc;
    }

    private function addGetter($field_name, $field_class_full, $is_static, $is_concrete)
    {
        if (isset($this->logger)) {
            $this->logger->info('Aggiungo getter', array(
              'class' => $this->currentClass->getName(),
              'field' => $field_name,
              'type' => $field_class_full,
              'static' => $is_static,
              'concrete' => $is_concrete
          ));
        }

        /** $m @var \Nette\PhpGenerator\Method */
        $m = $this->currentClass->addMethod('get'.ucfirst($field_name));
        $m->setStatic($is_static);
        $m->addDocument('@return '.$field_class_full);
        if ($is_concrete) {
            $m->setFinal(true);
            $m->setBody('return $this->?;', [$field_name]);
        }
    }

    private function addSetter($field_name, $field_class_full, $is_static, $is_concrete)
    {
        if (isset($this->logger)) {
            $this->logger->info('Aggiungo setter', array(
              'class' => $this->currentClass->getName(),
              'field' => $field_name,
              'type' => $field_class_full,
              'static' => $is_static,
              'concrete' => $is_concrete
          ));
        }

        /** $m @var \Nette\PhpGenerator\Method */
        $m = $this->currentClass->addMethod('set'.ucfirst($field_name));
        $m->setStatic($is_static);
        $m->addDocument('@var '.$name.' '.$field_class_full);
        $m->addParameter($name)->setTypeHint($field_class_name);

        if ($is_concrete) {
            $m->setFinal(true);
            $m->setBody('$this->? = $?;', [$name, $name]);
            $m->addParameter($name)->setTypeHint($field_class_name);
        }
    }

    /**
     * [generateClassType description]
     * @param  string      $properties        elementi possibili 'fields', 'extend', 'implements'
     * @param  array       $types_reference   [description]
     * @param  array       $types_description [description]
     * @param  ClassConfig $config            [description]
     */
    public function generateClassType($properties, $types_reference, $types_description, ClassConfig $config)
    {
        // extend class
        if (array_key_exists('extend', $properties)) {
            $extendClassName = $properties['extend'];
            if (isset($this->logger)) {
                $this->logger->info('Aggiungo setter', array(
                'class' => $this->currentClass->getName(),
                'extend' => $extendClassName
              ));
            }
            $this->currentClass->setExtends($extendClassName);
            $this->currentClass->getNamespace()->addUse($extendClassName);
        }

        // implements class
        if (array_key_exists('implements', $properties)) {
            $implementsList = array();
            if (!is_array($properties['implements'])) {
                $implementsList[] = $properties['implements'];
            } else {
                $implementsList = array_merge($implementsList, $properties['implements']);
            }
            $this->currentClass->setImplements($implementsList);
            foreach ($implementsList as $implement_use) {
                if (isset($this->logger)) {
                    $this->logger->info('Aggiungo setter', array(
                    'class' => $this->currentClass->getName(),
                    'implements' => $implement_use
                  ));
                }
                $this->currentClass->getNamespace()->addUse($implement_use);
            }
        }

        if (array_key_exists('fields', $properties)) {
            $mc_constructor = null;
            if ($config->add_constructor) {
                $mc_constructor = $this->addConstructor();
            }

            $body = '';

            foreach ($properties['fields'] as $name => $field_properties) {
                $is_static = false;
                $is_autoinizialize = false;
                $default_value = null;
                if (array_key_exists('static', $field_properties)) {
                    $is_static = $field_properties['static'];
                }

                if (array_key_exists('autoinizialize', $field_properties)) {
                    $is_autoinizialize = boolval($field_properties['autoinizialize']);
                }

                if (array_key_exists('default', $field_properties)) {
                    $default_value = $field_properties['default'];
                }

                $field_class_full = '';
                if (array_key_exists('class', $field_properties)) {
                    $field_class_name = $field_properties['class'];

                    if (strpos($field_class_name, '\\') !== 0) {
                        if (array_key_exists($field_class_name, $types_reference)) {
                            $field_namespace = $types_reference[$field_class_name];
                            $field_class_full = $field_namespace.'\\'.$field_class_name;
                            // $this->currentClass->getNamespace()->addUse($field_namespace);
                            if (isset($this->logger)) {
                                $this->logger->info('Add field type class with namespace', array(
                                'class' => $this->currentClass->getName(),
                                'field' => $name,
                                'className' => $field_class_full
                              ));
                            }
                            if ($config->add_constructor) {
                                $mc_constructor->addParameter($name)->setTypeHint($field_class_full);
                                $this->currentClass->getNamespace()->addUse($field_class_full);
                            }
                        } else {
                            $this->errors[] = ' Missing class '.$field_class_name.' on '.$this->currentClass->getName();
                        }
                    } else {
                        if (isset($this->logger)) {
                            $this->logger->info('Add field type class without namespace', array(
                            'class' => $this->currentClass->getName(),
                            'field' => $name,
                            'className' => $field_class_name
                          ));
                        }
                        $field_class_full = $field_class_name;
                        if ($config->add_constructor && !$is_autoinizialize) {
                            $mc_constructor->addParameter($name)->setTypeHint($field_class_full);
                            $this->currentClass->getNamespace()->addUse($field_class_full);
                        }
                    }
                } else {
                    $field_class_name = $field_properties['primitive'];
                    $field_namespace = null;
                    $field_class_full = $field_properties['primitive'];
                    if ($config->add_constructor) {
                        //FIXME: se sono in php7 ho anche gli altri elementi primitivi
                        //@see: http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
                        if ($field_class_full == 'array') {
                            $mc_constructor->addParameter($name)->setTypeHint('array');
                        } else {
                            $mc_constructor->addParameter($name);
                        }
                    }
                }

                if (isset($this->logger)) {
                    $this->logger->info('Check autoinizialize field', array(
                    'class' => $this->currentClass->getName(),
                    'field' => $name,
                    'autoinizialize' => $is_autoinizialize,
                    'default' => $default_value
                  ));
                }

                if (!$is_autoinizialize) {
                    if (null !=  $default_value) {
                        //TODO: usare "primitive type per determinare il corretto IF"
                        //FARE UN TEST PER I BOOLEAN
                        //@see https://www.virendrachandak.com/techtalk/php-isset-vs-empty-vs-is_null/
                        $body .= 'if ( empty($'.$name.') ) { ';
                        $body .= ' $this->'.$name.' = '.$default_value.';';
                        $body .= '} else {';
                        $body .= ' $this->'.$name.' = $'.$name.';';
                        $body .= '}';
                    } else {
                        $body .= ' $this->'.$name.' = $'.$name.';';
                    }
                } else {
                    if (!empty($default_value) || is_int($default_value)) {
                        if (substr(rtrim($default_value), -1) != ";") {
                            $this->logger->error('autoinizialize for '.$field_class_full.' on class '.$this->currentClass->getName().' have default with ";" please remove!');
                            $default_value = substr($default_value, 0, strlen($default_value));
                        }
                        $body .= ' $this->'.$name.' = '.$default_value.';';
                    } else {
                        if (isset($this->logger)) {
                            $this->logger->error('autoinizialize for '.$field_class_full.' not defined on element '.$this->currentClass->getName());
                        }
                        $this->errors[] = 'autoinizialize for '.$field_class_full.' not defined on element '.$this->currentClass->getName();
                    }
                }

                $comment = 'no description available';
                if (array_key_exists('description', $field_properties)) {
                    $comment = $field_properties['description'];
                } else {
                    if (array_key_exists($field_class_name, $types_description)) {
                        $comment = $types_description[$field_class_name];
                    }
                }

                if (!$config->is_interface) {
                    /** $field @var \Nette\PhpGenerator\Property */
                    $field = $this->currentClass->addProperty($name);
                    $field->setStatic($is_static);
                    if ($config->is_enum) {
                        $field->setVisibility('protected');
                    } else {
                        $field->setVisibility('private');
                    }
                    $field->addDocument($comment)->addDocument('@var '.$field_class_full);
                }

                $create_setter = $config->create_setter;
                if (array_key_exists('setter', $field_properties)) {
                    $create_setter = $field_properties['setter'];
                }

                $create_getter = $config->create_getter;
                if (array_key_exists('getter', $field_properties)) {
                    $create_getter = $field_properties['getter'];
                }

                if ($config->is_interface) {
                    if ($create_getter) {
                        $this->addGetter($name, $field_class_full, $is_static, false);
                    }

                    if ($create_setter) {
                        $this->addSetter($name, $field_class_full, $is_static, false);
                    }
                } else {
                    if ($create_getter) {
                        $this->addGetter($name, $field_class_full, $is_static, true);
                    }

                    if ($create_setter) {
                        $this->addSetter($name, $field_class_full, $is_static, true);
                    }
                }
            }
            if ($config->add_constructor) {
                $mc_constructor->setBody($body, []);
            }
        }
    }

    public function toString()
    {
        return (string)$this->currentFile;
    }

    public function createFileOnDir(Local $adapter)
    {
        $filesystem = new Filesystem($adapter);
        $outFile = str_replace('\\', '/', $this->currentClass->getNamespace()->getName().'\\'.$this->currentClass->getName()).'.php';
        if ($filesystem->has($outFile)) {
            $filesystem->put($outFile, (string)$this->currentFile);
        } else {
            $filesystem->write($outFile, (string)$this->currentFile);
        }
        return $outFile; //$io->text('Outfile: '.$outFile);
    }
}
