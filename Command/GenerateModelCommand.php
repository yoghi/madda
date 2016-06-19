<?php

namespace Yoghi\Bundle\MaddaBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Yoghi\Bundle\MaddaBundle\Model\Reader;
use Yoghi\Bundle\MaddaBundle\Generator\ClassConfig;
use Yoghi\Bundle\MaddaBundle\Generator\DDDGenerator;
use Yoghi\Bundle\MaddaBundle\Generator\ClassGenerator;
use Yoghi\Bundle\MaddaBundle\Finder\Finder;
use Psr\Log\LoggerInterface;

class GenerateModelCommand extends Command
{

    private $logger;
    private $errors;

    protected function configure()
    {
        $this
            ->setName('generate:model')
            ->setDescription('Genera tutto il modello a partire da un file yml')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory sorgente')
            ->addArgument('outputdirectory', InputArgument::REQUIRED, 'Directory output delle classi generate')
            ->addOption('clean', null, InputOption::VALUE_OPTIONAL, 'Option clean output directory')
            // ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    // protected function generateClasses(Local $fullPathFile, Local $directoryOutput, $io)
    // {
        // $adapter = new Local($directoryOutput);
        // $filesystem = new Filesystem($adapter);
        // $io->section('Analisi di '.$baseDirectory.'/'.$fileName);
//
        // $rym = new Reader();
        // $rym->readYaml($baseDirectory, $fileName);
        // $specList = $rym->getProperties();
    // }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // throw new \Exception('boo');

        $directory = realpath($input->getArgument('directory'));
        $directoryOutput = $input->getArgument('outputdirectory');

        /** @var $logger Psr\Log\LoggerInterface */
        $this->logger = $this->getContainer()->get('logger');

        $io = new SymfonyStyle($input, $output);
        $io->title('DDD Model Generation');

        $clean = $input->hasOption('clean');
        if ($clean) {
            $io->section('Clean output directoty');
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            try {
                $fs->remove($directoryOutput);
            } catch (IOExceptionInterface $e) {
                $io->error($e->getMessage());
            }
            $io->text('clean of '.$directoryOutput.' completed');
        }

        if (is_dir($directory)) {
            $finder = new Finder();
            $finder->search($directory);
            foreach ($finder->getFindedFiles() as $file) {
                if (pathinfo($file, PATHINFO_FILENAME) == 'model.yml') {
                    $io->text("Analizzo model.yml in ".pathinfo($file, PATHINFO_DIRNAME));
                    $dddGenerator = new DDDGenerator();
                    $dddGenerator->setLogger($this->logger);
                    $dddGenerator->analyze($file);
                    $dddGenerator->generate($directoryOutput);
                }
            }

            $io->section('Php-Cs-Fixer on generated files');

            $fixer = new \Symfony\CS\Console\Command\FixCommand();

            $input = new ArrayInput(array(
               'path' => $directoryOutput,
               '--level' => 'psr2',
               '--fixers' => 'eof_ending,strict_param,short_array_syntax,trailing_spaces,indentation,line_after_namespace,php_closing_tag'
            ));

            $output = new BufferedOutput();
            $fixer->run($input, $output);
            $content = $output->fetch();

            $io->text($content);

            if (count($this->errors) == 0) {
                $io->success('Completed generation');
            } else {
                $io->error($this->errors);
            }
        } else {
            $io->caution('Directory '.$directory.' not valid');
        }

        // PER I WARNING RECUPERABILI
        //$io->note('Generate Class');
    }
}
