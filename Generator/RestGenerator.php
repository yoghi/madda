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

use Raml\Parser;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Psr\Log\LoggerInterface;
use Raml\Exception\InvalidJsonException;
use Raml\Exception\RamlParserException;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Stefano Tamagnini <>
 */
class RestGenerator extends AbstractFileGenerator
{

    /**
     * Modello virtuale delle API
     * @var \Raml\ApiDefinition
     */
    private $apiDef;

    /**
     * Informazioni esterne usate per generare i web service
     * @var array
     */
    private $mapExternalInfo;

    /**
     * Name bundle
     * @var string
     */
    private $bundleName;


    public function __construct($bundleName = 'AppBundle', $mapExternalInfo = array())
    {
        $this->errors = array();
        $this->bundleName = $bundleName;
        $this->mapExternalInfo = $mapExternalInfo;
    }


    /**
     * Rimuove le parentesi graffe e capitalizza le parole della stringa passata in ingresso
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    private function removeGraph(&$str)
    {
        $str = str_replace('{', '', $str);
        $str = str_replace('}', '', $str);
        $str = ucfirst($str);
    }

    public function generateRest($ramlFile, Local $directoryOutput)
    {
        $parser = new Parser();
        try {

            /**
             * file di routing symfony
             * @var array
             */
            $routing = array();

            /**
             * Mappa delle proprieta dei controller da generare
             * @var array
             */
            $mappaClassDef = array();

            $this->apiDef = $parser->parse($ramlFile);
            $this->logger->info('Title: '.$this->apiDef->getTitle());

            $baseUrl = $this->apiDef->getBaseUrl();

            $parametriBaseUrl = $this->apiDef->getBaseUriParameters();
            /** @var \Raml\BaseUriParameter $definition */
            foreach ($parametriBaseUrl as $varName => $definition) {
                if (!array_key_exists($varName, $this->mapExternalInfo)) {
                    $this->error('Missing: '.$varName.' -> '.$definition->getDescription());
                    $this->mapExternalInfo[$varName] = 'undefined';
                }
                $baseUrl = str_replace($varName, $this->mapExternalInfo[$varName], $baseUrl);
            }

            $this->info('BaseUrl '.$baseUrl); //corrisponde a host: "{subdomain}.example.com" dentro routing.yml

            $enabledProtocols = $this->apiDef->getProtocols(); //serve per fare controlli su http/https -> schemes:  [https] dentro routing.yml

            $infoSecuritySchema = $this->apiDef->getSecuredBy(); // descrive i vari security schema usati nelle varie risorse

            /** @var: \Raml\Resource[] */
            $resources = $this->apiDef->getResources();

            $namespace = $this->bundleName.'\Controller';

            /** @var: \Raml\Resource $resource */
            foreach ($resources as $resource) {
                $displayName = $resource->getDisplayName();
                $this->info('Controller per path: '.$displayName);

                $names = explode('/', $displayName);
                preg_match_all("/(\/{[a-zA-Z]+}(\/)?)+/i", $displayName, $methodParam);
                array_walk($names, array($this, 'removeGraph'));
                $className = join('', $names);

                $methods = $resource->getMethods();

                if (count($methods) > 0) {
                    /** @var \Raml\Method $method */
                    foreach ($methods as $method) {
                        $controllerName = ucfirst($className);

                        // Creo $appBundle / $workspace Controller . php
                        $this->info('Genera: '.$namespace.$controllerName.'Controller');
                        $controllerProperties = array();
                        $controllerProperties['name'] = $controllerName.'Controller';
                        $controllerProperties['namespace'] = $namespace;
                        $controllerProperties['extend'] = 'Symfony\Bundle\FrameworkBundle\Controller\Controller';

                        $methodListParams = join(',', $methodParam[0]);

                        $type = strtolower($method->getType());
                        $methodCallName = $type.$controllerName;
                        $actionName = $methodCallName.'Action';
                        $this->info('Call Method: '.$actionName.'('.$methodListParams.')');

                        $controllerProperties['methods'] = array();
                        $controllerProperties['methods'][$actionName] = array();
                        $controllerProperties['methods'][$actionName]['params'] = array(

                        );

                        $description = $method->getDescription();
                        $this->info('Description: '.$description);

                        $entryName = strtolower($className).'_'.$type;
                        $routing[$entryName]['path'] = $displayName;
                        $routing[$entryName]['defaults']['_controller'] = $this->bundleName.':'.$controllerName.':'.$methodCallName;
                        $routing[$entryName]['host'] = $baseUrl;
                        $routing[$entryName]['methods'] = array();
                        $routing[$entryName]['methods'][] = strtoupper($type);
                        $routing[$entryName]['schemas'] = $enabledProtocols;
                        $routing[$entryName]['requirements'] = array();
                        $routing[$entryName]['requirements'][] = 'FIXME';

                        $mappaClassDef[$controllerName.'Controller'] = $controllerProperties;
                    } //fine methods
                }

                /* @var \Raml\Resource $subResources */
                $subResources = $resource->getResources();

                foreach ($subResources as $subResource) {
                    //$this->analyzeResource($subResource, $directoryOutput);
                }
            } //fine reousrces

            // $yaml = Yaml::dump($routing, 2);
            $this->currentFile = Yaml::dump($routing, 2);
            $this->_createFileOnDir($directoryOutput, $this->bundleName.'/Resources/config/routing.yml');

            foreach ($mappaClassDef as $className => $controllerProp) {
                $this->info('Devo creare '.$className);
                $gClassgen = new ClassGenerator($namespace, $className);
                $gClassgen->setLogger($this->logger);
                $config = new ClassConfig();
                $typesReferenceArray = array();
                $typesDescArray = array();
                $gClassgen->generateClassType($controllerProp, $typesReferenceArray, $typesDescArray, $config);
                $gClassgen->createFileOnDir($directoryOutput);
            }

            $this->info('Scrittura su '.$directoryOutput);
        } catch (InvalidJsonException $e) {
            $this->error('['.$e->getErrorCode().'] ' .$e->getMessage());
            $this->error($e->getTraceAsString());
        } catch (RamlParserException $e) {
            $this->error('['.$e->getErrorCode().'] ' .$e->getMessage());
        }
    }
}
