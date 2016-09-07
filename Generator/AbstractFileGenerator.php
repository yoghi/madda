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

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * @author Stefano Tamagnini <>
 */
abstract class AbstractFileGenerator
{
    /**
     * [$currentFile description]
     *
     * @var [type]
     */
    protected $currentFile;

    /**
     * Array process errors
     *
     * @var array
     */
    protected $errors;

    /**
     * [$logger description]
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function error($message, $context = [])
    {
        if (!is_null($this->logger)) {
            $this->logger->error($message, $context);
        }
    }

    protected function info($message, $context = [])
    {
        if (!is_null($this->logger)) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * errori durante la generazione
     *
     * @return array of string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function toString()
    {
        return (string) $this->currentFile;
    }

    protected function _createFileOnDir(Local $adapter, $outFile)
    {
        $filesystem = new Filesystem($adapter);

        $dir = pathinfo($adapter->getPathPrefix().$outFile, PATHINFO_DIRNAME).'/';
        if (!is_dir($dir)) {
            $this->logger->info('Creo directory mancante: '.$dir);
            mkdir($dir, 0700, true);
        }

        if ($filesystem->has($outFile)) {
            $filesystem->put($outFile, (string) $this->currentFile);
        } else {
            $filesystem->write($outFile, (string) $this->currentFile);
        }

        return $outFile; //$io->text('Outfile: '.$outFile);
    }
}
