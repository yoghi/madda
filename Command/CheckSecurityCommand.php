<?php

namespace Yoghi\Bundle\MaddaBundle\Command;

use Psr\Log\LoggerInterface;
use SensioLabs\Security\SecurityChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckSecurityCommand extends Command
{
    private $logger;
    // private $errors;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('security:check')
            ->setDescription('security check')
            // ->addArgument('srcfile', InputArgument::REQUIRED, 'File xml sorgente')
            // ->addArgument('tipologia', InputArgument::REQUIRED, 'Tipologia delle sessioni')
            // ->addOption('clean', null, InputOption::VALUE_OPTIONAL, 'Option clean output directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Secuirty Check');

        $fileName = __DIR__.'composer.lock';

        if ($this->logger) {
            $this->logger->info('Start security check on '.$fileName);
        }

        //@see: https://github.com/sensiolabs/security-checker
        $checker = new SecurityChecker();
        $alerts = $checker->check('composer.lock');

        count($alerts) > 0 ?  $io->error($alerts) : $io->success('security checked!');
    }
}
