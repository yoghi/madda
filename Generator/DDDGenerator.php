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

    public function __construct()
    {
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
        $rym = new Reader();
        $rym->readYaml($fullPathFile);
        $specList = $rym->getProperties();
        print_r($specList);
    }

    public function generate()
    {
    }
}
