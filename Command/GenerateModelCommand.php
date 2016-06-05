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
use Symfony\Component\Yaml\Parser;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

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

    protected function generateClasses($baseDirectory, $path, $directoryOutput, $io)
    {
        $adapter = new Local($directoryOutput);
        $filesystem = new Filesystem($adapter);
        $io->section('Analisi di '.$baseDirectory.'/'.$path);
        $yaml = new Parser();
        $spec_list = array();
        try {
            $spec_list = $yaml->parse(file_get_contents($baseDirectory.'/'.$path));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }

        if (!array_key_exists('ddd', $spec_list)) {
            $this->logger->error('missing ddd section into yml');
            $this->errors[] = 'missing ddd section into yml';
        } else {
            $ddd_definition = $spec_list['ddd'];

            $types_reference = array();
            $types_description = array();
            $types_field = array();

            foreach ($spec_list['classes'] as $class_name => $properties) {
                $is_constructor_enable = true;
                if (!array_key_exists('ddd', $properties)) {
                    $this->logger->error('missing ddd section into yml for class '.$class_name);
                    $this->errors[] = 'missing ddd section into yml for class '.$class_name;
                    $io->note('force '.$class_name.' to type class');
                    $properties['ddd'] = array();
                    $properties['ddd']['type'] = 'class';
                }
                $ddd_reference = $properties['ddd']['type'];
                $ddd_is_root_aggregate = false;
                if (in_array($ddd_reference, array('interface', 'class'))) {
                    if (array_key_exists('namespace', $properties)) {
                        $namespace = $properties['namespace'];
                        $fileInterface = new PhpFile;
                        $interface = $fileInterface->addInterface($namespace.'\\'.$class_name);
                        $types_reference[$class_name] = $namespace;
                        if (array_key_exists('fields', $properties)) {
                            $types_field[$class_name] = $properties['fields'];
                        }
                        $this->generateClassType($fileInterface, $interface, $properties, $types_reference, $types_description, false, true, true, false, false, $filesystem, $io);
                    } else {
                        $this->logger->error('Missing namespace for '.$ddd_reference);
                        $this->errors[] = 'Missing namespace for '.$ddd_reference;
                    }
                } else {
                    if (!array_key_exists($ddd_reference, $ddd_definition)) {
                        $this->logger->error('Missing ddd reference for : '.$ddd_reference.' into '.$class_name);
                        $this->errors[] = 'Missing ddd reference for : '.$ddd_reference.' into '.$class_name;
                    } else {
                        $ddd_reference_properties = $ddd_definition[$ddd_reference];
                        $namespace = $ddd_reference_properties['package'];

                        $ddd_is_root_aggregate = $ddd_reference == 'aggregate' && isset($properties['ddd']['root']) && boolval($properties['ddd']['root']) ? true : false;

                        if ($ddd_is_root_aggregate) {
                            $io->note($class_name.' is AggregateRoot');
                        }

                        if (array_key_exists('namespace', $properties)) {
                            $namespace = $properties['namespace'];
                        }

                        $create_getter = false;
                        $create_setter = false;
                        if (array_key_exists('getter', $ddd_reference_properties)) {
                            $create_getter = $ddd_reference_properties['getter'];
                        }
                        if (array_key_exists('setter', $ddd_reference_properties)) {
                            $create_setter = $ddd_reference_properties['setter'];
                        }

                        if (array_key_exists('extend', $ddd_reference_properties)) {
                            $ddd_extend = $ddd_reference_properties['extend'];
                            if (!array_key_exists('extend', $properties)) {
                                $properties['extend'] = $ddd_extend; //No multi-inheritance
                            }
                        }

                        $ddd_reference_fields = array();
                        if (array_key_exists('fields', $ddd_reference_properties)) {
                            foreach ($ddd_reference_properties['fields'] as $key => $value) {
                                $ddd_reference_fields[$key] = $value;
                            }
                        }

                        //TODO: gestire gli [] dentro la definizione del modello se serve...

                        //TODO: aggiungere le validazioni
                        // validationRule:
                        //   events:
                        //     create:
                        //       fields: [ id, sessione, tipologiaCampo]
                        //     delete:
                        //       fields: [ id ]
                        //     addDocument:
                        //       fields: [ id, documentoCorrelato ]


                        if ($ddd_is_root_aggregate) {

                            // per creare eventi devo avere la definition !
                            if (array_key_exists('events', $ddd_definition)) {
                                $ddd_events_properties = $ddd_definition['events'];
                                $events_namespace = $ddd_events_properties['package'];
                                $events_implement = '';
                                if (array_key_exists('implement', $ddd_events_properties)) {
                                    $events_implement = $ddd_events_properties['implement'];
                                }
                                $events_extend = '';
                                if (array_key_exists('extend', $ddd_events_properties)) {
                                    $events_extend = $ddd_events_properties['extend'];
                                }
                                $namespace_implement_class = $types_reference[$events_implement];
                                $events_implement_full = $namespace_implement_class.'\\'.$events_implement;
                                // $description_implement_class = $types_description[$events_implement];
                                $ddd_reference_fields_event = array();
                                if (array_key_exists($events_implement, $types_field)) {
                                    $fields_implement_class = $types_field[$events_implement];
                                    foreach ($fields_implement_class as $key => $value) {
                                        $ddd_reference_fields_event[$key] = $value;
                                    }
                                }
                                if (array_key_exists('fields', $ddd_events_properties)) {
                                    foreach ($ddd_events_properties['fields'] as $key => $value) {
                                        $ddd_reference_fields_event[$key] = $value;
                                    }
                                }
                                // -- fine setup

                                $events_to_create = array();

                                // iterazione tra gli eventi definiti
                                if (array_key_exists('events', $ddd_reference_properties)) {
                                    $events_to_create = $ddd_reference_properties['events'];
                                }

                                // events:
                                //   - add_document
                                if (array_key_exists('events', $properties)) {
                                    $events_to_create = array_merge($events_to_create, $properties['events']);
                                }

                                foreach ($events_to_create as $event) {
                                    $fileEvent = new PhpFile;
                                    $events_class_name = $class_name . str_replace('_', '', ucwords($event, '_')).'Event';
                                    $eventClass = $fileEvent->addClass($events_namespace.'\\'.$events_class_name);
                                    $eventClass->setFinal(true);

                                    $properties_eventClass = array();
                                    if ('' != $events_extend) {
                                        $properties_eventClass['extend'] = $events_extend;
                                    }
                                    if ('' != $events_implement) {
                                        $properties_eventClass['implements'] = array($events_implement_full);
                                    }

                                    $properties_eventClass['fields'] = array();
                                    foreach ($ddd_reference_fields_event as $fkey => $fvalue) {
                                        $properties_eventClass['fields'][$fkey] = $fvalue;
                                    }

                                    $types_field[$events_class_name] = $properties_eventClass;

                                    $this->generateClassType($fileEvent, $eventClass, $properties_eventClass, $types_reference, $types_description, false, false, false, false, true, $filesystem, $io);
                                }
                            } else {
                                $this->logger->error('event declare but missing ddd section events into yml');
                                $this->errors[] = 'event declare but missing ddd section events into yml';
                            }
                        } //proprieta di field -> events

                        $file = new PhpFile;
                        $class = $file->addClass($namespace.'\\'.$class_name);

                        $types_reference[$class_name] = $namespace;

                        if (array_key_exists('description', $properties)) {
                            $types_description[$class_name] = $properties['description'];
                        }

                        if (array_key_exists('traits', $properties)) {
                            if (is_array($properties['traits'])) {
                                foreach ($properties['traits'] as $trait) {
                                    $class->getNamespace()->addUse($trait);
                                    $class->addTrait($trait);
                                    $io->note('Add trait '.$trait);
                                }
                            } else {
                                $traitObject = $properties['traits'];
                                $class->getNamespace()->addUse($traitObject);
                                $class->addTrait($traitObject);
                            }
                        }

                        $is_enum = false;
                        if (array_key_exists('enum', $properties)) {
                            $class->setAbstract(true);
                            $is_enum = true;
                            $create_setter = false;
                            $is_constructor_enable = false; //ENUM quindi verra creato nelle specializzazioni.

                            if (!array_key_exists('fields', $properties)) {
                                $properties['fields']= array();
                            }

                            $properties['fields']['name'] = array(
                              'primitive' => 'string',
                              'description' => 'nome esplicativo della enum'
                            );

                            $properties['fields']['instance'] = array(
                              'class' => $class_name,
                              'description' => 'singleton',
                              'getter' => false,
                              'static' => true
                            );

                            $maskedFields = array();
                            $maskedFields['name'] = array();
                            $maskedFields['instance'] = array();
                            $arguments = array_diff_key($properties['fields'], $maskedFields);

                            $m = $class->addMethod('parseString');
                            $m->setStatic(true);
                            $m->addDocument('@return '.$namespace.'\\'.$class_name.'|null')->setFinal(true);
                            $m->addParameter("parseString");
                            $setting = '';
                            foreach ($arguments as $argument_key => $argument_value) {
                                if (array_key_exists('class', $argument_value)) {
                                    $classRef = $argument_value['class'];
                                    $namespace = $types_reference[$classRef];
                                    $class->getNamespace()->addUse($namespace.'\\'.$classRef);
                                    $m->addParameter($argument_key)->setTypeHint($namespace.'\\'.$classRef);
                                    $setting .= '$'.$argument_key.',';
                                } else {
                                    $m->addParameter($argument_key);
                                }
                            }
                            $m->setBody('$class_name = ?.\'\\\\\'.$parseString;
                                        if (class_exists($class_name)) {
                                            $x = $class_name::instance('.rtrim($setting, ",").');
                                            return $x;
                                        }; return null;', [$namespace.'\\'.$class_name]);

                            //base enum class
                            $types_field[$class_name] = $properties['fields'];

                            $elementiEnum = $properties['enum'];
                            $namespaceEnum = $namespace.'\\'.$class_name;
                            foreach ($elementiEnum as $enumValue) {
                                $fileEnum = new PhpFile;
                                $enumClass = $fileEnum->addClass($namespaceEnum.'\\'.$enumValue);
                                $enumClass->setFinal(true);

                                $properties_enumClass = array();
                                $properties_enumClass['extend'] = $namespace.'\\'.$class_name;

                                $m = $enumClass->addMethod('instance');
                                $m->setStatic(true);
                                $m->addDocument('@return '.$namespaceEnum.'\\'.$enumValue)->setFinal(true);

                                $setting = '';
                                if (count($arguments) > 0) {
                                    foreach ($arguments as $argument_key => $argument_value) {
                                        if (array_key_exists('class', $argument_value)) {
                                            $classRef = $argument_value['class'];
                                            $namespace = $types_reference[$classRef];
                                            $enumClass->getNamespace()->addUse($namespace.'\\'.$classRef);
                                            $m->addParameter($argument_key)->setTypeHint($namespace.'\\'.$classRef);
                                            $setting .= '$'.$argument_key.',';
                                        } else {
                                            $m->addParameter($argument_key);
                                        }
                                    }
                                    $m->setBody('self::$instance = new '.$enumValue.'('.rtrim($setting, ",").'); return self::$instance;', []);
                                } else {
                                    $m->setBody('self::$instance = new '.$enumValue.'(); return self::$instance;', []);
                                }

                                $m2 = $enumClass->addMethod('__construct');
                                $m2->setStatic(false);
                                $m2->setVisibility('private');
                                $m2->addDocument('costruttore')->setFinal(true);

                                $setting = '';
                                if (count($arguments) > 0) {
                                    foreach ($arguments as $argument_key => $argument_value) {
                                        if (array_key_exists('class', $argument_value)) {
                                            $classRef = $argument_value['class'];
                                            $namespace = $types_reference[$classRef];
                                            $enumClass->getNamespace()->addUse($namespace.'\\'.$classRef);
                                            $m2->addParameter($argument_key)->setTypeHint($namespace.'\\'.$classRef);
                                            $setting .= '$this->'.$argument_key.' = $'.$argument_key.';';
                                        } else {
                                            $m2->addParameter($argument_key);
                                        }
                                    }
                                }

                                $m2->setBody('$this->name = ?; '.$setting, [$enumValue]);

                                $this->generateClassType($fileEnum, $enumClass, $properties_enumClass, $types_reference, $types_description, false, false, false, false, false, $filesystem, $io);
                            } //for enum foglie
                        } else { //else not enum!
                            $class->setFinal(true);
                        }

                        $types_field[$class_name] = $properties['fields'];

                        $this->generateClassType($file, $class, $properties, $types_reference, $types_description, $is_enum, false, $create_getter, $create_setter, $is_constructor_enable, $filesystem, $io);
                    }
                }
            }
        } //for
    }



    protected function scan($filesystem, $baseDirectory, $directory, $directoryOutput, $io)
    {
        $contents = $filesystem->listContents($directory, false);
        foreach ($contents as $object) {
            if ($object['type'] == 'dir') {
                $this->scan($filesystem, $baseDirectory, $object['path'], $directoryOutput, $io);
            } else {
                if ($object['extension'] == 'yml' && $object['basename'] == 'model.yml') {
                    $this->generateClasses($baseDirectory, $object['path'], $directoryOutput, $io);
                }
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // throw new \Exception('boo');

        $directory = realpath($input->getArgument('directory'));
        $directoryOutput = $input->getArgument('outputdirectory');

        /** @var $logger LoggerInterface */
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
            $adapter = new Local($directory);
            $filesystem = new Filesystem($adapter);
            $contents = $filesystem->listContents('.', false);
            foreach ($contents as $object) {
                if ($object['type'] == 'dir') {
                    $this->scan($filesystem, $directory, $object['path'], $directoryOutput, $io);
                } else {
                    if ($object['extension'] == 'yml' && $object['basename'] == 'model.yml') {
                        $this->generateClasses($directory, $object['path'], $directoryOutput, $io);
                    }
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
