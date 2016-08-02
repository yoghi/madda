<?php

namespace Yoghi\Bundle\MaddaBundle\Finder;

use Psr\Log\LoggerInterface;

class Finder
{
    private $files;

    /**
     * [$logger description]
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->files = array();
    }

    /**
     * [search description]
     * @param  string $dir search directory
     * @param  string $extension extension to find, Ex: yml, php, raml
     */
    public function search($dir, $extension)
    {
        if (isset($this->logger)) {
            $this->logger->info("Finder invocato su directory : ".$dir);
        }
        $rdi = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $rit = new \RecursiveIteratorIterator($rdi);

        foreach ($rit as $file) {
            if (isset($this->logger)) {
                $this->logger->debug("Valuto file", array('filename' => $file));
            }
            if (pathinfo($file, PATHINFO_EXTENSION) == $extension) {
                $this->files[] = $file;
            }
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getFindedFiles()
    {
        return $this->files;
    }
}
