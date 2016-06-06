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

 /**
 * @author Stefano Tamagnini <>
 */
class ClassGenerator
{
    private $currentClass;
    private $currentFile;
    private $errors;

    public function __construct($namespace, $class_name)
    {
        $this->currentFile = new PhpFile;
        $this->currentClass = $this->currentFile->addClass($namespace.'\\'.ucfirst($class_name));
    }

    /**
     * Aggiungere il costruttore
     */
    private function addConstructor()
    {
        $mc = $this->currentClass->addMethod('__construct');
        $mc->setStatic(false);
        $mc->setVisibility('public');
        $mc->addDocument('costruttore');
        $mc->setFinal(true);
    }

    private function addGetter($field_name, $field_class_full, $is_static, $is_concrete)
    {
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

    public function generateClassType($properties, $types_reference, $types_description, ClassConfig $config)
    {
        // extend class
        if (array_key_exists('extend', $properties)) {
            $this->currentClass->setExtends($properties['extend']);
            $this->currentClass->getNamespace()->addUse($properties['extend']);
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
                $this->currentClass->getNamespace()->addUse($implement_use);
            }
        }

        if (array_key_exists('fields', $properties)) {
            if ($config->add_constructor) {
                $this->addConstructor();
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
                            $this->currentClass->getNamespace()->addUse($field_namespace);
                            if ($config->add_constructor) {
                                $mc->addParameter($name)->setTypeHint($field_class_full);
                            }
                        } else {
                            $this->errors[] = ' Missing class '.$field_class_name.' on '.$this->currentClass->getName();
                        }
                    } else {
                        if ($config->add_constructor && !$is_autoinizialize) {
                            $mc->addParameter($name)->setTypeHint($field_class_name);
                        }
                        $field_class_full = $field_class_name;
                    }
                } else {
                    $field_class_name = $field_properties['primitive'];
                    $field_namespace = null;
                    $field_class_full = $field_properties['primitive'];
                    if ($config->add_constructor) {
                        //FIXME: se sono in php7 ho anche gli altri elementi primitivi
                        //@see: http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
                        if ($field_class_full == 'array') {
                            $mc->addParameter($name)->setTypeHint('array');
                        } else {
                            $mc->addParameter($name);
                        }
                    }
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
                    if (null !=  $default_value) {
                        $body .= ' $this->'.$name.' = '.$default_value.';';
                    } else {
                        $this->logger->error('autoinizialize for '.$field_class_full.' not defined on element '.$this->currentClass->getName());
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
                $mc->setBody($body, []);
            }
        }
    }

    public function toString()
    {
        return (string)$this->currentFile;
    }

    public function createFile($directory)
    {
        $adapter = new Local($directory);
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
