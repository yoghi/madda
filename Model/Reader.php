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

 use Symfony\Component\Yaml\Parser;
 use Symfony\Component\Yaml\Exception\ParseException;
 use Yoghi\Bundle\MaddaBundle\Exception\MaddaException;
 use Arrayzy\ArrayImitator as A;

 /**
 * @author Stefano Tamagnini <>
 */
class Reader
{
    /**
     * Array delle definizioni
     * @var \Arrayzy\ArrayImitator
     */
    private $specList;

    public function __construct()
    {
        $specListArray = array(
          'ddd' => array(),
          'classes' => array()
        );
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
                /** @see https://github.com/bocharsky-bw/Arrayzy#merge*/
                $this->specList = $this->specList->merge($parsed, true);
            }
        } catch (ParseException $e) {
            throw new MaddaException($e->getMessage());
        }
    }

    /**
     * @deprecated
     * @return array proprietà definite via yaml
     */
    public function getProperties()
    {
        return $this->specList->toArray();
    }

    public function getClassesDefinition()
    {
        return $this->specList['classes'];
    }

    public function getClassDefinitionAttributes($key)
    {
        $x = new A($this->specList->offsetGet('classes'));
        return $x->offsetGet($key);
    }

    public function getDomainDefinitionAttributes($key)
    {
        $x = new A($this->specList->offsetGet('ddd'));
        return $x->offsetGet($key);
    }
}
