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
class ClassGenerator extends AbstractFileGenerator
{
    /**
     * [$currentClass description]
     * @var \Nette\PhpGenerator\ClassType
     */
    private $currentClass;

    public function __construct($namespace, $className, $document = 'Generated Class')
    {
        $this->currentFile = new PhpFile;
        $this->currentClass = $this->currentFile->addClass($namespace.'\\'.ucfirst($className));
        $this->currentClass->addComment($document);
    }

    /**
     * Aggiungere il costruttore
     * @return Nette\PhpGenerator\Method
     */
    private function addConstructor()
    {
        $this->info('Aggiungo costruttore', array( 'class' => $this->currentClass->getName() ));
        $mc = $this->currentClass->addMethod('__construct');
        $mc->setStatic(false);
        $mc->setVisibility('public');
        $mc->addComment('costruttore');
        $mc->setFinal(true);
        return $mc;
    }

    private function addSingleton($comment, $createGetter)
    {
        $this->info('Aggiungo supporto singleton', array(
          'class' => $this->currentClass->getName()
        ));
        $fullClassName = $this->currentClass->getNamespace()->getName().'\\'.$this->currentClass->getName();
        if ($createGetter) {
            $mSingleton = $this->currentClass->addMethod('getInstance');
            $mSingleton->setStatic(true);
            $mSingleton->setVisibility('public');
            $mSingleton->addComment('Singleton NO THREAD SAFE!');
            $mSingleton->addComment('@return '.$fullClassName.'|null');
            $mSingleton->setFinal(true);
            $body = 'if ( is_null(self::$instance) ) {';
            $body .= ' self::$instance = new '.$this->currentClass->getName().'();';
            $body .= '}';
            $body .= 'return self::$instance;';
            $mSingleton->setBody($body);
        }
        $field = $this->currentClass->addProperty('instance');
        $field->setVisibility('protected');
        $field->setStatic(true);
        $field->addComment($comment)->addComment('@var '.$fullClassName);
    }

    private function addGetter($fieldName, $fieldClassFull, $isStatic, $isConcrete)
    {
        $this->info('Aggiungo getter', array(
          'class' => $this->currentClass->getName(),
          'field' => $fieldName,
          'type' => $fieldClassFull,
          'static' => $isStatic,
          'concrete' => $isConcrete
        ));

        /** $methodGetter @var \Nette\PhpGenerator\Method */
        $methodGetter = $this->currentClass->addMethod('get'.ucfirst($fieldName));
        $methodGetter->setStatic($isStatic);
        $methodGetter->addComment('@return '.$fieldClassFull);
        if ($isConcrete) {
            $methodGetter->setFinal(true);
            if ($isStatic) {
                $methodGetter->setBody('return self::$?;', [$fieldName]);
            } else {
                $methodGetter->setBody('return $this->?;', [$fieldName]);
            }
        }
    }

    private function addTrait($trait, $typesReference)
    {
        $traitFull = $trait;
        if (array_key_exists($trait, $typesReference)) {
            $traitNamespace = $typesReference[$trait];
            $traitFull = $traitNamespace.'\\'.$trait;
        }
        $this->currentClass->getNamespace()->addUse($traitFull);
        $this->currentClass->addTrait($traitFull);
        $this->info('Add trait', array(
          'class' => $this->currentClass->getName(),
          'trait' => $traitFull
        ));
    }

    private function addSetter($fieldName, $fieldClassFull, $isStatic, $isConcrete)
    {
        $this->info('Aggiungo setter', array(
          'class' => $this->currentClass->getName(),
          'field' => $fieldName,
          'type' => $fieldClassFull,
          'static' => $isStatic,
          'concrete' => $isConcrete
        ));

        /** $methodSetter @var \Nette\PhpGenerator\Method */
        $methodSetter = $this->currentClass->addMethod('set'.ucfirst($fieldName));
        $methodSetter->setStatic($isStatic);
        $methodSetter->addComment('@var '.$fieldName.' '.$fieldClassFull);
        $methodSetter->addParameter($fieldName)->setTypeHint($fieldClassFull);

        if ($isConcrete) {
            $methodSetter->setFinal(true);
            if ($isStatic) {
                $methodSetter->setBody('self::$? = $?;', [$fieldName, $fieldName]);
            } else {
                $methodSetter->setBody('$this->? = $?;', [$fieldName, $fieldName]);
            }
            $methodSetter->addParameter($fieldName)->setTypeHint($fieldClassFull);
        }
    }

    private function addParseString()
    {
        $fieldClassFull = $this->currentClass->getNamespace()->getName().'\\'.$this->currentClass->getName();

        $this->info('Aggiungo parseString', array(
          'class' => $this->currentClass->getName()
        ));

        /** $methodParseString @var \Nette\PhpGenerator\Method */
        $methodParseString = $this->currentClass->addMethod('parseString');
        $methodParseString->setFinal(true);
        $methodParseString->setStatic(true);
        $methodParseString->addComment('@return '.$fieldClassFull.'|null');
        $methodParseString->addParameter('parseString');
        $body = '$class_name = \''.$fieldClassFull.'\'.\'\\\\\'.$parseString;'."\n";
        $body .= 'if (class_exists($class_name)) {';
        $body .= "\t".'$x = $class_name::instance();';
        $body .= "\t".'return $x;';
        $body .= '}';
        $body .= 'return null;';
        //$methodParseString->setBody('self::$? = $?;', [$field_name, $field_name]);
        $methodParseString->setBody($body);
    }

    /**
     * [generateClassType description]
     * @param  string      $properties        elementi possibili 'fields', 'extend', 'implements'
     * @param  array       $typesReference   [description]
     * @param  array       $typesDescription [description]
     * @param  ClassConfig $config            [description]
     */
    public function generateClassType(array $properties, $typesReference, $typesDescription, ClassConfig $config)
    {
        $phpNamespace = $this->currentClass->getNamespace();
        if ($config->isInterface) {
            $this->info('Passo a interfaccia', array($this->currentClass->getName()));
            $docs = $this->currentClass->getComment();
            $this->currentClass = $this->currentFile->addInterface($phpNamespace->getName().'\\'.ucfirst($this->currentClass->getName()));
            $this->currentClass->setComment($docs);
            $this->info('Check haveConstructor, in caso metto a false', array($config->haveConstructor));
            $config->haveConstructor = false;
        }

        $this->info('Generate', array( 'class' => $this->currentClass->getName(), 'namespace' => $phpNamespace->getName(), 'comment' => $this->currentClass->getComment(), 'properties' => $properties ));

        // extend class
        if (array_key_exists('extend', $properties)) {
            $extendClassName = $properties['extend'];
            $this->info('Aggiungo extend', array(
              'class' => $this->currentClass->getName(),
              'extend' => $extendClassName
            ));
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
            foreach ($implementsList as $implementUse) {
                $this->info('Aggiungo implement', array(
                  'class' => $this->currentClass->getName(),
                  'implements' => $implementUse
                ));
                $this->currentClass->getNamespace()->addUse($implementUse);
            }
        }

        // traits
        if (array_key_exists('traits', $properties)) {
            if (is_array($properties['traits'])) {
                foreach ($properties['traits'] as $trait) {
                    $this->addTrait($trait, $typesReference);
                }
            } else {
                $traitObject = $properties['traits'];
                $this->addTrait($traitObject, $typesReference);
            }
        }

        if ($config->isFinalClass) {
            $this->currentClass->setFinal(true);
        }

        $first = true;
        if (array_key_exists('fields', $properties)) {
            /** @var $methodConstructor \Nette\PhpGenerator\Method */
            $methodConstructor = null;
            if ($config->haveConstructor) {
                $methodConstructor = $this->addConstructor();
            }

            $body = '';

            foreach ($properties['fields'] as $name => $fieldProperties) {
                $isStatic = false;
                $isAutoinizialize = false;
                $defaultValue = null;
                if (array_key_exists('static', $fieldProperties)) {
                    $isStatic = $fieldProperties['static'];
                }

                if (array_key_exists('autoinizialize', $fieldProperties)) {
                    $isAutoinizialize = boolval($fieldProperties['autoinizialize']);
                }

                if (array_key_exists('default', $fieldProperties)) {
                    $defaultValue = $fieldProperties['default'];
                }

                if (!$isAutoinizialize) {
                    if (null !=  $defaultValue) {
                        //TODO: usare "primitive type per determinare il corretto IF"
                        //FARE UN TEST PER I BOOLEAN
                        //@see https://www.virendrachandak.com/techtalk/php-isset-vs-empty-vs-is_null/
                        $body .= 'if ( empty($'.$name.') ) { '."\n";
                        if ($isStatic) {
                            $body .= ' self::$';
                        } else {
                            $body .= ' $this->';
                        }
                        $body .= $name.' = '.$defaultValue.';'."\n";
                        $body .= '} else {';
                        if ($isStatic) {
                            $body .= ' self::$';
                        } else {
                            $body .= ' $this->';
                        }
                        $body .= $name.' = $'.$name.';'."\n";
                        $body .= '}'."\n";
                    } else {
                        if (!$isStatic) {
                            $body .= ' $this->'.$name.' = $'.$name.';'."\n";
                        }
                    }
                } else {
                    if (!empty($defaultValue) || is_int($defaultValue)) {
                        if (substr(rtrim($defaultValue), -1) == ";") {
                            $this->error('autoinizialize for '.$name.' on class '.$this->currentClass->getName().' have default with ";" please remove!');
                            $defaultValue = substr($defaultValue, 0, strlen($defaultValue)-1);
                        }
                        if (!$isStatic) {
                            if ($isAutoinizialize) {
                                $body .= '// autoinizialize'."\n";
                                $body .= '$this->'.$name.' = '.$defaultValue.';'."\n";
                            } else {
                                if ($defaultValue) {
                                    $body .= 'if ( !is_null($'.$name.') ) {'."\n";
                                    $body .= ' $this->'.$name.' = $'.$name.';'."\n";
                                    $body .= '} else {'."\n";
                                    $body .= ' $this->'.$name.' = '.$defaultValue.';'."\n";
                                    $body .= '}'."\n";
                                  // $body .= '$this->'.$name.' = '.$defaultValue.';'."\n";
                                } else {
                                    $body .= 'if ( is_null($'.$name.') ) {'."\n";
                                    $body .= ' $this->'.$name.' = '.$defaultValue.';'."\n";
                                    $body .= '}'."\n";
                                }
                            }
                        }
                    } else {
                        $this->error('autoinizialize for '.$name.' not defined on element '.$this->currentClass->getName());
                        $this->errors[] = 'autoinizialize for '.$name.' not defined on element '.$this->currentClass->getName();
                    }
                }

                $fieldClassFull = '';
                if (array_key_exists('class', $fieldProperties)) {
                    $fieldClassName = ucfirst($fieldProperties['class']);

                    if (array_key_exists($fieldClassName, $typesReference)) {
                        $fieldNamespace = $typesReference[$fieldClassName];
                        $fieldClassFull = $fieldNamespace.'\\'.$fieldClassName;
                        $this->info('Trovato field namespace tra le reference', array(
                          'class' => $this->currentClass->getName(),
                          'field' => $fieldClassName,
                          'className' => $fieldClassFull
                        ));
                    } else {
                        //FIXME: strpos is better
                        if ($fieldClassName[0] == '\\') {
                            //Class: \DateTime
                            $fieldClassFull = $fieldClassName;
                        } else {
                            $fieldClassFull = $phpNamespace->getName().'\\'.$fieldClassName;
                            $this->info('Uso class for field same namespace', array(
                              'class' => $this->currentClass->getName(),
                              'field' => $fieldClassName,
                              'className' => $fieldClassFull
                            ));
                        }
                    }

                    if ($config->haveConstructor && !$isStatic) {
                        $parameter = null;
                        if (!$isAutoinizialize) {
                            $this->info('Aggiungo parametro al costruttore', array(
                                'class' => $this->currentClass->getName(),
                                'parameter' => $name,
                                'className' => $fieldClassFull,
                                'default' => $defaultValue,
                                'autoinizialize' => $isAutoinizialize
                            ));
                            if (!$first) {
                                $parameter = $methodConstructor->addParameter($name, null); //solo i primitivi hanno un default, gli altri null come object
                                $parameter->setTypeHint($fieldClassFull);
                            } else {
                                $parameter = $methodConstructor->addParameter($name);
                                $parameter->setTypeHint($fieldClassFull);
                            }
                        } else {
                            $this->info('Skip parametro al costruttore -> autoinizialize true', array(
                              'class' => $this->currentClass->getName(),
                              'parameter' => $name,
                              'className' => $fieldClassFull,
                              'default' => $defaultValue,
                              'autoinizialize' => $isAutoinizialize
                            ));
                        }
                    }

                    if (array_key_exists($fieldClassName, $typesReference)) {
                        $this->info('Add field type class with namespace', array(
                            'class' => $this->currentClass->getName(),
                            'field' => $fieldClassName,
                            'className' => $fieldClassFull
                        ));
                        $this->currentClass->getNamespace()->addUse($fieldClassFull);
                    }
                } else {
                    //tipo primitivo
                    $fieldClassName = $fieldProperties['primitive'];
                    $fieldNamespace = null;
                    $fieldClassFull = $fieldProperties['primitive'];
                    if ($config->haveConstructor && !$isStatic) {
                        //FIXME: se sono in php7 ho anche gli altri elementi primitivi
                        //@see: http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration

                        $parameter = null;

                        if (!$isAutoinizialize) {
                            if (is_null($defaultValue)) {
                                $this->info('Aggiungo parametro al costruttore', array(
                                  'class' => $this->currentClass->getName(),
                                  'parameter' => $name,
                                  'className' => $fieldClassFull,
                                  'default' => $defaultValue,
                                  'autoinizialize' => $isAutoinizialize
                                ));

                                //PHP7 ONLY
                                // if ($fieldClassFull == 'int') {
                                //     $parameter->setTypeHint('int');
                                // }

                                if (!$first) {
                                    $parameter = $methodConstructor->addParameter($name, null);
                                } else {
                                    $parameter = $methodConstructor->addParameter($name);
                                }

                                if ($fieldClassFull == 'array') {
                                    $parameter->setTypeHint('array');
                                } else {
                                    if ($defaultValue != null) {
                                        /** @var $parameter \Nette\PhpGenerator\Parameter */
                                        $parameter->setDefaultValue(''.$defaultValue);
                                    }
                                }
                            }
                        }
                    }
                }

                $this->info('Check autoinizialize field', array(
                  'class' => $this->currentClass->getName(),
                  'field' => $name,
                  'autoinizialize' => $isAutoinizialize,
                  'default' => $defaultValue
                ));

                $comment = 'no description available';
                if (array_key_exists('description', $fieldProperties)) {
                    $comment = $fieldProperties['description'];
                } else {
                    if (!is_null($typesDescription) && array_key_exists($fieldClassName, $typesDescription)) {
                        $comment = $typesDescription[$fieldClassName];
                    }
                }

                if (!$config->isInterface) {
                    /** $field @var \Nette\PhpGenerator\Property */
                    $field = $this->currentClass->addProperty($name);
                    $field->setStatic($isStatic);
                    if ($config->isEnum) {
                        $field->setVisibility('protected');
                    } else {
                        $field->setVisibility('private');
                    }
                    $field->addComment($comment)->addComment('@var '.$fieldClassFull);
                }

                $createSetter = $config->haveSetter;
                if (array_key_exists('setter', $fieldProperties)) {
                    $createSetter = $fieldProperties['setter'];
                }

                $createGetter = $config->haveGetter;
                if (array_key_exists('getter', $fieldProperties)) {
                    $createGetter = $fieldProperties['getter'];
                }

                if ($config->isInterface) {
                    if ($createGetter) {
                        $this->addGetter($name, $fieldClassFull, $isStatic, false);
                    }

                    if ($createSetter) {
                        $this->addSetter($name, $fieldClassFull, $isStatic, false);
                    }
                } else {
                    if ($createGetter) {
                        $this->addGetter($name, $fieldClassFull, $isStatic, true);
                    }

                    if ($createSetter) {
                        $this->addSetter($name, $fieldClassFull, $isStatic, true);
                    }
                }
                if (!$isAutoinizialize) {
                    $first = false;
                }
            }
            if ($config->haveConstructor) {
                $methodConstructor->setBody($body, []);
            }
        } //end fields

        if (array_key_exists('methods', $properties)) {
            $body = '';

            foreach ($properties['methods'] as $methodName => $methodsProperties) {
                $this->info('Aggiungo method', array(
                  'class' => $this->currentClass->getName(),
                  'methodName' => $methodName,
                  'methodProp' => $methodsProperties
                ));

                /** $newMethodCall @var \Nette\PhpGenerator\Method */
                $newMethodCall = $this->currentClass->addMethod($methodName);
                $newMethodCall->setFinal(true);

                $newMethodCall->setStatic(false);
                if (array_key_exists('static', $methodsProperties)) {
                    $newMethodCall->setStatic($methodsProperties['static']);
                }

                if (array_key_exists('description', $methodsProperties)) {
                    $newMethodCall->setVisibility($methodsProperties['visibility']);
                } else {
                    $newMethodCall->setVisibility('public');
                }

                if (array_key_exists('description', $methodsProperties)) {
                    $newMethodCall->addComment($methodsProperties['description']);
                } else {
                    $returnType = 'void';
                    if (array_key_exists('@return', $methodsProperties)) {
                        $returnType = $methodsProperties['@return'];
                        //TODO: .'|null' va messo in quale condizione?
                        $newMethodCall->addComment('@return '.$returnType);
                    } else {
                        $newMethodCall->addComment('@return void');
                    }
                }

                if (array_key_exists('params', $methodsProperties)) {
                    foreach ($methodsProperties['params'] as $paramName => $paramProp) {
                        if (array_key_exists('class', $paramProp)) {
                            $newMethodCall->addParameter($paramName)->setTypeHint($paramProp['class']);
                        }
                        if (array_key_exists('primitive', $paramProp)) {
                            $newMethodCall->addParameter($paramName);
                        }
                    }
                }
                $body = ' // FIMXE: da implementare ';
                if (array_key_exists('body', $methodsProperties)) {
                    $body = $methodsProperties['body'];
                }
                $newMethodCall->setBody($body);
            }
        }

        if ($config->isEnum) {
            $this->currentClass->setAbstract(true);
            $this->addSingleton('Singleton instance for enum', false);
            $this->addParseString();
        }

        if ($config->isSingleton) {
            $this->addSingleton('Singleton instance', true);
        }
    }

    public function createFileOnDir(Local $adapter)
    {
        $outFile = str_replace('\\', '/', $this->currentClass->getNamespace()->getName().'\\'.$this->currentClass->getName()).'.php';
        $this->_createFileOnDir($adapter, $outFile);
    }
}
