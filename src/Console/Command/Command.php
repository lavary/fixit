<?php

namespace Fixit\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand {
    
    /**
     * Command arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Command options
     *
     * @var array
     */
    protected $options;

    /**
     * The SymfonyStyle instance
     *
     * @var Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $styler;

    /**
     * Input object
     *
     * @var use Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * output object
     *
     * @var use Symfony\Component\Console\Input\OutputInterface
     */
    protected $output;    
}