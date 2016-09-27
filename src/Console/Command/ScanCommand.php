<?php

namespace Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

use Gitonomy\Git\Repository;

class ScanCommand extends Command
{
    /**
     * Configures the current command
     *
     */
    protected function configure()
    {
       $this->setName('scan')
            ->setDescription('Scan the code base to find the previously marked issues.')
            ->setDefinition([
               
               new InputArgument('src',    InputArgument::REQUIRED, 'The source directory to start scanning'), 
               
               new InputOption('ext',      null, InputOption::VALUE_REQUIRED,                               'Type of files to parse'), 
               new InputOption('level',    null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Level of the issue'), 
               new InputOption('as',       null, InputOption::VALUE_REQUIRED                              , 'Render the output in a tabular format', 'table'),   
            ])
            ->setHelp('Scan the code base to find the previously marked issues.');
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
        $this->arguments = $input->getArguments();
        $this->options   = $input->getOptions();
        $this->styler    = new SymfonyStyle($input, $output);
        $this->output    = $output;

        $files      = $this->collect($this->arguments['src'], $this->options['ext']);
        $ic         = 0;      
        $fc         = 0;
        $collection = [];
        
        foreach ($files as $file) {                        
            
            $grepOutput  = [];
            $path        = $file->getRealPath();
            $fc++;
           
            exec('grep -onE "//[[:space:]]*@' . $this->level() . '[[:space:]]+.*$" ' . $path, $grepOutput);
            
            // Rendering the output
            if (count($grepOutput)) {                               
                $collection[$path] = $grepOutput;
                $ic += count($grepOutput);                
            } 
        } 

        if ($ic) {
            $this->styler->newLine();
            $this->styler->text($fc . ' file(s) scanned.');
            $this->styler->text($ic . ' issue(s) were found in ' . count($collection) . ' file(s).');
            $this->styler->newLine();
            
            $this->renderOutput($collection);

        } 
    }  

    /**
     * Return the appropriate issue level
     *
     * @return string
     */
    protected function level()
    {
        return count($this->options['level']) ? '(' . implode('|', $this->options['level']) . ')' : '[[:alpha:]]+';
    }

    /**
     * Render the output according to the command option --as
     *
     * @param array $collection
     *
     */
    public function renderOutput($collection)
    {
        if ($this->options['as'] == 'table') {

            foreach ($collection as $filename => $batch) {
                $this->styler->text('- File:' . $filename);
                $this->asTable($batch);
            }
        
        } else if ($this->options['as'] == 'list') {
            
            foreach ($collection as $filename => $batch) {
                $this->styler->text('- File:' . $filename );
                $this->asList($batch);
            }

        }  else  {
            $this->asJson($collection);
        }         
    }

    /**
     * Render output in tabular format
     *
     * @param array $output
     *
     */
    protected function asTable($output)
    {         
        $table = new Table($this->output);
        $table->setHeaders(['#', 'Line', 'Type', 'Comment']);
        $table->setRows($this->populateRows($output));
        $table->setStyle('compact');
        
        $this->styler->newLine();
        $table->render();
        $this->styler->newLine();

    }

    /**
     * Render output as a list
     *
     * @param array $output
     *
     */
    protected function asList($output)
    {  
        $this->styler->listing($this->populateRows($output, true));  
    }

    /**
     * Render the output as JSON
     *
     * @param array $output
     *
     */
    protected function asJson($output)
    {  
        $entries = [];
        foreach ($output as $filename => $batch) {
            $entries[] = [
                
                'file'  => $filename,
                'items' => $this->populateRows($batch),

            ];
        }

        $this->styler->text(stripslashes(json_encode($entries)));
    }
 
    /**
     * Populate columns for rendering the output
     *
     * @param array   $output
     * @param boolean $mergeColumns
     *
     * @return array
     */
    protected function populateRows($output, $mergeColumns = false)
    {  
        $entries = [];
        $row     = 0;

        foreach ($output as $key => $entry) {         
            
            $components = $this->parseRow($entry);                                                    
            $entries[$key] = [
                
                'row'     => ++$row,
                'line'    => $components[1],
                'type'    => $components[2],
                'comment' => $components[3],

            ];

            if ($mergeColumns === true) {
                $entries[$key] = implode(' ', $entries[$key]);
            }            
        } 

        return $entries; 
    }

    /**
     * Parse the passed entry and return the components
     *
     * @param string $row
     *
     * @return array
     */
    protected function parseRow($row)
    {  
        $sections = [];
        preg_match('|(\d+)://\s*@(\w+)\s+(.+)|', $row, $sections);

        return $sections;    
    }

    /**
     * Collect all task files
     *
     * @param  string $source
     *
     * @return Iterator
     */
    protected function collect($src, $ext = null)
    {    
        if(!file_exists($src)) {
            return [];
        }
        
        $finder   = new Finder();
        $iterator = $finder->files();
                         
        if (!is_null($ext)) {
            $iterator->name('*.' . ltrim($ext, '.'));
        }

        $iterator->in($src);

        return $iterator;
    }

}