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

    public function __construct($namespace, $class_name, $document = 'Generated Class')
    {
        $this->currentFile = new PhpFile;
        $this->currentClass = $this->currentFile->addClass($namespace.'\\'.ucfirst($class_name));
        $this->currentClass->addComment($document);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Aggiungere il costruttore
     * @return Nette\PhpGenerator\Method
     */
    private function addConstructor()
    {
        if (isset($this->logger)) {
            $this->logger->info('Aggiungo costruttore', array( 'class' => $this->currentClass->getName() ));
        }
        $mc = $this->currentClass->addMethod('__construct');
        $mc->setStatic(false);
        $mc->setVisibility('public');
        $mc->addComment('costruttore');
        $mc->setFinal(true);
        return $mc;
    }

    private function addSingleton($comment, $createGetter)
    {
        if (isset($this->logger)) {
            $this->logger->info('Aggiungo supporto singleton', array(
              'class' => $this->currentClass->getName()
            ));
        }
        $full_class_name = $this->currentClass->getNamespace()->getName().'\\'.$this->currentClass->getName();
        if ($createGetter) {
            $m = $this->currentClass->addMethod('getInstance');
            $m->setStatic(true);
            $m->setVisibility('public');
            $m->addComment('Singleton NO THREAD SAFE!');
            $m->addComment('@return '.$full_class_name.'|null');
            $m->setFinal(true);
            $body = 'if ( is_null(self::$instance) ) {';
            $body .= ' self::$instance = new '.$this->currentClass->getName().'();';
            $body .= '}';
            $body .= 'return self::$instance;';
            $m->setBody($body);
        }
        $field = $this->currentClass->addProperty('instance');
        $field->setVisibility('protected');
        $field->setStatic(true);
        $field->addComment($comment)->addComment('@var '.$full_class_name);
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
        $m->addComment('@return '.$field_class_full);
        if ($is_concrete) {
            $m->setFinal(true);
            if ($is_static) {
                $m->setBody('return self::$?;', [$field_name]);
            } else {
                $m->setBody('return $this->?;', [$field_name]);
            }
        }
    }

    private function addTrait($trait, $types_reference)
    {
        if (array_key_exists($trait, $types_reference)) {
            $trait_namespace = $types_reference[$trait];
            $trait_full = $trait_namespace.'\\'.$trait;
        } else {
            $trait_full = $trait;
        }
        $this->currentClass->getNamespace()->addUse($trait_full);
        $this->currentClass->addTrait($trait_full);
        if (isset($this->logger)) {
            $this->logger->info('Add trait', array(
          'class' => $this->currentClass->getName(),
          'trait' => $trait_full
        ));
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
        $m->addComment('@var '.$field_name.' '.$field_class_full);
        $m->addParameter($field_name)->setTypeHint($field_class_full);

        if ($is_concrete) {
            $m->setFinal(true);
            if ($is_static) {
                $m->setBody('self::$? = $?;', [$field_name, $field_name]);
            } else {
                $m->setBody('$this->? = $?;', [$field_name, $field_name]);
            }
            $m->addParameter($field_name)->setTypeHint($field_class_full);
        }
    }

    private function addParseString()
    {
        $field_class_full = $this->currentClass->getNamespace()->getName().'\\'.$this->currentClass->getName();

        if (isset($this->logger)) {
            $this->logger->info('Aggiungo parseString', array(
              'class' => $this->currentClass->getName()
          ));
        }

        /** $m @var \Nette\PhpGenerator\Method */
        $m = $this->currentClass->addMethod('parseString');
        $m->setFinal(true);
        $m->setStatic(true);
        $m->addComment('@return '.$field_class_full.'|null');
        $m->addParameter('parseString');
        $body = '$class_name = \'TestNamespace\EnumTest\'.\'\\\\\'.$parseString;'."\n";
        $body .= 'if (class_exists($class_name)) {';
        $body .= "\t".'$x = $class_name::instance();';
        $body .= "\t".'return $x;';
        $body .= '}';
        $body .= 'return null;';
        //$m->setBody('self::$? = $?;', [$field_name, $field_name]);
        $m->setBody($body);
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
        $phpNamespace = $this->currentClass->getNamespace();
        if ($config->is_interface) {
            if (isset($this->logger)) {
                $this->logger->info('Passo a interfaccia', array($this->currentClass->getName()));
            }
            $docs = $this->currentClass->getComment();
            $this->currentClass = $this->currentFile->addInterface($phpNamespace->getName().'\\'.ucfirst($this->currentClass->getName()));
            $this->currentClass->setComment($docs);
            if (isset($this->logger)) {
                $this->logger->info('Check add_constructor, in caso metto a false', array($config->add_constructor));
            }
            $config->add_constructor = false;
        }

        $this->logger->info('Generate', array( 'class' => $this->currentClass->getName(), 'namespace' => $phpNamespace->getName(), 'comment' => $this->currentClass->getComment() ));

        // extend class
        if (array_key_exists('extend', $properties)) {
            $extendClassName = $properties['extend'];
            if (isset($this->logger)) {
                $this->logger->info('Aggiungo extend', array(
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
                    $this->logger->info('Aggiungo implement', array(
                    'class' => $this->currentClass->getName(),
                    'implements' => $implement_use
                  ));
                }
                $this->currentClass->getNamespace()->addUse($implement_use);
            }
        }

        // traits
        if (array_key_exists('traits', $properties)) {
            if (is_array($properties['traits'])) {
                foreach ($properties['traits'] as $trait) {
                    $this->addTrait($trait, $types_reference);
                }
            } else {
                $traitObject = $properties['traits'];
                $this->addTrait($traitObject, $types_reference);
            }
        }

        if ($config->is_final) {
            $this->currentClass->setFinal(true);
        }

        $first = true;
        if (array_key_exists('fields', $properties)) {
            /** @var $mc_constructor Nette\PhpGenerator\Method */
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

                if (!$is_autoinizialize) {
                    if (null !=  $default_value) {
                        //TODO: usare "primitive type per determinare il corretto IF"
                        //FARE UN TEST PER I BOOLEAN
                        //@see https://www.virendrachandak.com/techtalk/php-isset-vs-empty-vs-is_null/
                        $body .= 'if ( empty($'.$name.') ) { ';
                        if ($is_static) {
                            $body .= ' self::$';
                        } else {
                            $body .= ' $this->';
                        }
                        $body .= $name.' = '.$default_value.';';
                        $body .= '} else {';
                        if ($is_static) {
                            $body .= ' self::$';
                        } else {
                            $body .= ' $this->';
                        }
                        $body .= $name.' = $'.$name.';';
                        $body .= '}';
                    } else {
                        if (!$is_static) {
                            $body .= ' $this->'.$name.' = $'.$name.';';
                        }
                    }
                } else {
                    if (!empty($default_value) || is_int($default_value)) {
                        if (substr(rtrim($default_value), -1) == ";") {
                            $this->logger->error('autoinizialize for '.$field_class_full.' on class '.$this->currentClass->getName().' have default with ";" please remove!');
                            $default_value = substr($default_value, 0, strlen($default_value)-1);
                        }
                        if (!$is_static) {
                            $body .= 'if ( is_null($'.$name.') ) {';
                            $body .= ' $this->'.$name.' = '.$default_value.';';
                            $body .= '}';
                        }
                    } else {
                        if (isset($this->logger)) {
                            $this->logger->error('autoinizialize for '.$field_class_full.' not defined on element '.$this->currentClass->getName());
                        }
                        $this->errors[] = 'autoinizialize for '.$field_class_full.' not defined on element '.$this->currentClass->getName();
                    }
                }

                $field_class_full = '';
                if (array_key_exists('class', $field_properties)) {
                    $field_class_name = ucfirst($field_properties['class']);

                    if (array_key_exists($field_class_name, $types_reference)) {
                        $field_namespace = $types_reference[$field_class_name];
                        $field_class_full = $field_namespace.'\\'.$field_class_name;
                        if (isset($this->logger)) {
                            $this->logger->info('Trovato field namespace tra le reference', array(
                            'class' => $this->currentClass->getName(),
                            'field' => $field_class_name,
                            'className' => $field_class_full
                          ));
                        }
                    } else {
                        //FIXME: strpos is better
                        if ($field_class_name[0] == '\\') {
                            //Class: \DateTime
                          $field_class_full = $field_class_name;
                        } else {
                            $field_class_full = $phpNamespace->getName().'\\'.$field_class_name;
                            if (isset($this->logger)) {
                                $this->logger->info('Uso class for field same namespace', array(
                              'class' => $this->currentClass->getName(),
                              'field' => $field_class_name,
                              'className' => $field_class_full
                            ));
                            }
                        }
                    }

                    if ($config->add_constructor && !$is_static) {
                        if (isset($this->logger)) {
                            $this->logger->info('Aggiungo parametro al costruttore', array(
                              'class' => $this->currentClass->getName(),
                              'parameter' => $name,
                              'className' => $field_class_full,
                              'default' => $default_value
                            ));
                        }
                        $parameter = null;
                        if (!$first) {
                            $parameter = $mc_constructor->addParameter($name, null); //solo i primitivi hanno un default, gli altri null come object
                        } else {
                            $parameter = $mc_constructor->addParameter($name);
                        }
                        $parameter->setTypeHint($field_class_full);
                    }

                    if (array_key_exists($field_class_name, $types_reference)) {
                        if (isset($this->logger)) {
                            $this->logger->info('Add field type class with namespace', array(
                            'class' => $this->currentClass->getName(),
                            'field' => $field_class_name,
                            'className' => $field_class_full
                          ));
                        }
                        $this->currentClass->getNamespace()->addUse($field_class_full);
                    }
                } else {
                    $field_class_name = $field_properties['primitive'];
                    $field_namespace = null;
                    $field_class_full = $field_properties['primitive'];
                    if ($config->add_constructor && !$is_static) {
                        //FIXME: se sono in php7 ho anche gli altri elementi primitivi
                        //@see: http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration

                        $parameter = null;
                        if ($first) {
                            $parameter = $mc_constructor->addParameter($name);
                        } else {
                            $parameter = $mc_constructor->addParameter($name, null);
                        }

                        if ($field_class_full == 'array') {
                            $parameter->setTypeHint('array');
                        } else {
                            if ($default_value != null) {
                                $parameter->setDefaultValue($default_value);
                            }
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
                    $field->addComment($comment)->addComment('@var '.$field_class_full);
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
                $first = false;
            }
            if ($config->add_constructor) {
                $mc_constructor->setBody($body, []);
            }
        }

        if ($config->is_enum) {
            $this->currentClass->setAbstract(true);
            $this->addSingleton('Singleton instance for enum', false);
            $this->addParseString();
        }

        if ($config->is_singleton) {
            $this->addSingleton('Singleton instance', true);
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

        $dir = pathinfo($adapter->getPathPrefix().$outFile, PATHINFO_DIRNAME).'/';
        if (!is_dir($dir)) {
            $this->logger->info('Creo directory mancante: '.$dir);
            mkdir($dir, 0700, true);
        }

        if ($filesystem->has($outFile)) {
            $filesystem->put($outFile, (string)$this->currentFile);
        } else {
            $filesystem->write($outFile, (string)$this->currentFile);
        }
        return $outFile; //$io->text('Outfile: '.$outFile);
    }
}
