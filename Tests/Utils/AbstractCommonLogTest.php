<?php

namespace Yoghi\Bundle\MaddaBundleTest\Utils;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use org\bovigo\vfs\vfsStream;

trait AbstractCommonLogTest
{
    private static $directoryV;

    /**
     * [$logger description].
     *
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    public static function setUpBeforeClass()
    {
        self::$directoryV = vfsStream::setup();
    }

    public function setUp()
    {
        $this->logger = new Logger('phpunit-logger');
        $directoryLogOutput = self::$directoryV->url().'/log';
        if (!file_exists($directoryLogOutput)) {
            mkdir($directoryLogOutput, 0700, true);
        }
        $output = "%level_name% > %message% %context% %extra%\n";
        $formatter = new LineFormatter($output);
        $handler = new StreamHandler($directoryLogOutput.'/phpunit.log', Logger::DEBUG, true, null, false);
        touch($directoryLogOutput.'/phpunit.log');
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
        $this->logger->info('Avviato test -> '.$this->getName());
    }

    protected function readLog()
    {
        $fileLog = self::$directoryV->url().'/log/phpunit.log';

        return file_get_contents($fileLog);
    }

    public function tearDown()
    {
        $fileLog = self::$directoryV->url().'/log/phpunit.log';
        if ($this->hasFailed()) {
            echo "\n---- LOG ----\n";
            if (is_readable($fileLog)) {
                echo file_get_contents($fileLog);
            }
            echo "------------\n";
        }
        if (file_exists($fileLog)) {
            unlink($fileLog);
        }
        $this->logger = null;
    }
}
