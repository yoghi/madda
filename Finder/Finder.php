<?php

namespace Yoghi\Bundle\MaddaBundle\Finder;

use Psr\Log\LoggerInterface;

class Finder
{
    private $files;

    /**
     * [$logger description]
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->files = array();
    }

    /**
     * [search description]
     * @param  [type] $dir       [description]
     * @param  string $extension extension to find, Ex: yml, php, raml
     */
    public function search($dir, $extension)
    {
        $this->logger->info("Finder invocato su directory : ".$dir);
        $di = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new \RecursiveIteratorIterator($di);

        foreach ($it as $file) {
            $this->logger->debug("Valuto file", array('filename' => $file));
            if (pathinfo($file, PATHINFO_EXTENSION) == $extension) {
                $this->files[] = $file;
            }
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getYmlFiles()
    {
        return $this->files;
    }
}
