<?php

namespace Yoghi\Bundle\MaddaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use SensioLabs\Security\SecurityChecker;

class CheckSecurityCommand extends ContainerAwareCommand
{

    private $logger;
    private $errors;

    protected function configure()
    {
        $this
            ->setName('security:check')
            ->setDescription('verifica sicurezza')
            // ->addArgument('srcfile', InputArgument::REQUIRED, 'File xml sorgente')
            // ->addArgument('tipologia', InputArgument::REQUIRED, 'Tipologia delle sessioni')
            // ->addOption('clean', null, InputOption::VALUE_OPTIONAL, 'Option clean output directory')
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //@see: https://github.com/sensiolabs/security-checker
        $checker = new SecurityChecker();
        $alerts = $checker->check('/path/to/composer.lock');
        print_r($alerts);
    }
}
