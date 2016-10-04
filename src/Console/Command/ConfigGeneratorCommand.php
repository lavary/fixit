<?php

namespace Fixit\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class ConfigGeneratorCommand extends Command
{
    /**
     * Configures the current command
     *
     */
    protected function configure()
    {
       $this->setName('config:publish')
            ->setDescription('Creates a config file.')
            ->setHelp('This generates a config file in YML format.');
    } 

    /**
     * Executes the current command
     *
     * @param use Symfony\Component\Console\Input\InputInterface $input
     * @param use Symfony\Component\Console\Input\OutputIterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {              
        
        $this->input  = $input;
        $this->output = $output;

        $path = $this->ask('Please input the destination directory');
        $src  = __DIR__ . '/../../../config.yml';

        
        if (file_exists($path) && is_dir($path)) {
            $path = rtrim($path, '/') . '/config.yml';
            if (copy($src, $path)) {    
                $output->writeln('<info>The configuration file was generated successfully.</info>');
                exit();             
            } 
        }

        $output->writeln('<comment>There was a problem when generating the file.</comment>');
        exit();
    }

    /**
     * Ask a question
     *
     * @param  string $quetion
     *
     * @return string
     */
    protected function ask($question)
    {
        $helper   = $this->getHelper('question');
        $question = new Question("<question>{$question}</question>");
        
        return $helper->ask($this->input, $this->output, $question);
    }

}