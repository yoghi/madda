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
    public $is_enum;
    public $is_interface;
    public $is_singleton;
    public $create_getter;
    public $create_setter;
    public $add_constructor;
}
