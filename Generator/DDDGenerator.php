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
     * Array process errors
     * @var array
     */
    private $errors;

    public function __construct()
    {
        $this->rym = new Reader();
        $this->errors = array();
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

    private function info($message, $context)
    {
        if (!is_null($this->logger)) {
            $this->logger->info($message, $context);
        }
    }

    private function error($message, $context)
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
            $this->info('Generazione classe '.$className, array('class' => $className)); //, 'properties' => $properties
            if (!array_key_exists('ddd', $properties)) {
                $this->error('missing ddd section into yml for class '.$className);
                $this->errors[] = 'missing ddd section into yml for class '.$className;
                $this->info('force '.$className.' to type class');
                $properties['ddd'] = array();
                $properties['ddd']['type'] = 'class';
            }

            $namespace = '';
            if (array_key_exists('namespace', $properties)) {
                $namespace = $properties['namespace'];
            } else {
                $this->logger->error('Missing namespace for '.$className);
                $this->errors[] = 'Missing namespace for '.$className;
            }

            $dddType = $properties['ddd']['type'];
            //FIXME: , 'class' gestito diversamente
            if (in_array($dddType, array('interface'))) {
                $comments = '';
                if (array_key_exists('description', $properties)) {
                    $comments = $properties['description'];
                }
                $g = new ClassGenerator($namespace, $className, $comments);
                $g->setLogger($this->logger);
                $config = new ClassConfig();
                $config->is_interface = true;
                //$properties = array(); // 'fields', 'extend', 'implements'
                $types_reference = array(); //dipendenza dei field da altre classi
                //SERVE? $types_reference[$className] = $namespace;
                $types_description = array(); //descrizione delle classi da cui dipendono i field
                $g->generateClassType($properties, $types_reference, $types_description, $config);
                $g->createFileOnDir($directoryOutput);
                // DOMANDA: perche' non passarle tutte??
                // if (array_key_exists('fields', $properties)) {
                //     $types_field[$className] = $properties['fields'];
                // }
                // $this->generateClassType($fileInterface, $interface, $properties, $types_reference, $types_description, false, true, true, false, false, $filesystem, $io);
            }
        }
    }
}
