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


/**
 * @author Stefano Tamagnini <>
 */
class ClassConfig
{
    public $isEnum;
    public $isInterface;
    public $isFinalClass;

    /** NOTE: anti-pattern! */
    public $isSingleton;

    public $haveGetter;
    public $haveSetter;
    public $haveConstructor;
}
