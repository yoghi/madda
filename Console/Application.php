<?php

namespace Yoghi\Bundle\MaddaBundle\Console;

/*
 * This file is part of the MADDA project.
 *
 * (c) Stefano Tamagnini <>
 *
 * This source file is subject to the GPLv3 license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Console\Application as BaseApplication;
use Yoghi\Bundle\MaddaBundle\Command\CheckSecurityCommand;
use Yoghi\Bundle\MaddaBundle\Command\GenerateModelCommand;

/**
 * @author Stefano Tamagnini <>
 */
class Application extends BaseApplication
{
    const VERSION = '1.0.0-DEV';

    /**
     * Constructor.
     */
    public function __construct()
    {
        error_reporting(-1);
        parent::__construct('Madda', self::VERSION);
        $this->add(new CheckSecurityCommand());
        $this->add(new GenerateModelCommand());
    }

    public function getLongVersion()
    {
        $version = parent::getLongVersion().' by <comment>Stefano Tamagnini</comment>';
        $commit = '@git-commit@';
        if ('@'.'git-commit@' !== $commit) {
            $version .= ' ('.substr($commit, 0, 7).')';
        }

        return $version;
    }
}
