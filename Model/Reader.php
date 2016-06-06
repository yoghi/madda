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
 use Yoghi\Bundle\MaddaBundle\Exception\MaddaException;
 use Arrayzy\ArrayImitator as A;

 /**
 * @author Stefano Tamagnini <>
 */
class Reader
{

    private $specList = array(
      'ddd' => array(),
      'classes' => array()
    );

    public function __construct()
    {
    }

    public function readYaml($baseDirectory, $fileName)
    {
        $fullPath = $baseDirectory.'/'.$fileName;
        if (!file_exists($fullPath)) {
            throw new MaddaException("File $fullPath di configurazione inesistente");
        }
        $yaml = new Parser();
        try {
            $specListArray = new A($this->specList);
            $parsed = $yaml->parse(file_get_contents($fullPath));
            if (null != $parsed) {
                /** @see https://github.com/bocharsky-bw/Arrayzy#merge*/
                $specListArray = $specListArray->merge($parsed, true);
            }
            $this->specList = $specListArray->toArray();
        } catch (ParseException $e) {
            throw new MaddaException($e->getMessage());
        }
    }

    public function getProperties()
    {
        return $this->specList;
    }
}
