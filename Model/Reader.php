<?php

namespace Yoghi\Bundle\MaddaBundle\Model;

/*
 * This file is part of the MADDA project.
 *
 * (c) Stefano Tamagnini <>
 *
 * This source file is subject to the GPLv3 license that is bundled
 * with this source code in the file LICENSE.
 */

 use Arrayzy\ArrayImitator as A;
 use Symfony\Component\Yaml\Exception\ParseException;
 use Symfony\Component\Yaml\Parser;
 use Yoghi\Bundle\MaddaBundle\Exception\MaddaException;

 /**
 * @author Stefano Tamagnini <>
 */
class Reader
{
    /**
     * Array delle definizioni
     *
     * @var \Arrayzy\ArrayImitator
     */
    private $specList;

    public function __construct()
    {
        $specListArray = [
          'ddd' => [],
          'classes' => [],
        ];
        $this->specList = new A($specListArray);
    }

    public function readYaml($fullPath)
    {
        if (!file_exists($fullPath)) {
            throw new MaddaException("File $fullPath di configurazione inesistente");
        }
        $yaml = new Parser();
        try {
            $parsed = $yaml->parse(file_get_contents($fullPath));
            if (null != $parsed) {
                /* @see https://github.com/bocharsky-bw/Arrayzy#merge*/
                $this->specList = $this->specList->merge($parsed, true);
            }
        } catch (ParseException $e) {
            throw new MaddaException($e->getMessage());
        }
    }

    /**
     * @deprecated
     *
     * @return array proprietà definite via yaml
     */
    public function getProperties()
    {
        return $this->specList->toArray();
    }

    /**
     * [getClassesDefinition description]
     *
     * @return array|false [description]
     */
    public function getClassesDefinition()
    {
        return $this->specList['classes'];
    }

    public function getClassDefinitionAttributes($key)
    {
        $classDefList = new A($this->specList->offsetGet('classes'));

        return $classDefList->offsetGet($key);
    }

    public function getDomainDefinitionAttributes($key)
    {
        $domainDefList = new A($this->specList->offsetGet('ddd'));

        return $domainDefList->offsetGet($key);
    }
}
