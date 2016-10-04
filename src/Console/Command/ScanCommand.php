<?php

namespace Fixit\Console\Command;

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
    use \Fixit\Config\Configurer;

    /**
     * Configures the current command
     *
     */
    protected function configure()
    {       
       $this->setName('scan')
            ->setDescription('Scan the code base to find the previously marked issues.')
            ->setDefinition([

               new InputOption('configuration', null, InputOption::VALUE_REQUIRED,                               'Configuration file'),
               new InputOption('keyword',       null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Keywords to look for'),
               new InputOption('include',   null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Directories to include'), 
               new InputOption('exclude',   null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Directories to exclude'), 
               new InputOption('include_file',  null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Files to include'), 
               new InputOption('exclude_file',  null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Files to exclude'), 
               new InputOption('output_type',   null, InputOption::VALUE_REQUIRED                              , 'Render the output in a tabular format'),   
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

        $this->loadConfig($this->options['configuration']);
        $this->options = array_merge($this->config->all(), array_filter($this->options));
        
        $files = $this->collect(
                    $this->options['include'],
                    $this->options['exclude'],
                    $this->options['include_file'],
                    $this->options['exclude_file']
                 );
      
        $ic = 0;      
        $collection = [];
        
        foreach ($files as $file) {                        
            
            $grepOutput  = [];
            $path        = $file->getRealPath();
           
            //var_dump('grep -oniE "' . $this->pattern() . '" ' . $path);
            //exit();

            exec('grep -oniE "' . $this->pattern() . '" ' . $path, $grepOutput);
            
            // Rendering the output
            if (count($grepOutput)) {                               
                $collection[$path] = $grepOutput;
                $ic += count($grepOutput);                
            } 
        } 

        if ($ic) {
            $this->renderOutput($collection);
            $this->styler->newLine();
        }

        $this->styler->text($files->count() . ' file(s) scanned.');
        $this->styler->text($ic . ' issue(s) were found in ' . count($collection) . ' file(s).');
        $this->styler->newLine();
    }  

    /**
     * Render the output according to the command option --as
     *
     * @param array $collection
     *
     */
    protected function renderOutput($collection)
    {
        if ($this->options['output_type'] == 'table') {

            foreach ($collection as $filename => $batch) {
                $this->styler->text('- File:' . $filename);
                $this->asTable($batch);
            }
        
        } else if ($this->options['output_type'] == 'list') {
            
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
        $table->setHeaders($this->titles());
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
        $titles  = $this->titles();
        
        foreach ($output as $key => $entry) {         
            
            $components = $this->parseRow($entry);                                                    
            
            foreach ($titles as $titleKey => $titleValue) {
                $entries[$key][$titleValue] = isset($components[$titleKey]) ? $components[$titleKey] : '-';
            }
            
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
        preg_match('/(\d+):' . $this->pattern() . '/i', $row, $sections);
        
        if (is_array($sections)) {
            array_shift($sections);  
        }

        return $sections;  
    }

    /**
     * Return coulms titles for the output
     *
     *
     * @return array
     */
    protected function titles()
    {  
        return $this->options['titles'];
    }

     /**
     * Get pattern for the comment
     *
     *
     * @return strin
     */
    protected function pattern()
    {  
        return str_replace('%keyword%', implode('|', $this->options['keyword']), $this->options['pattern']);
    }   

    /**
     * Collect files to parse
     *
     * @param  string|array $include_dir
     * @param  string|array $exclude_dir
     * @param  string|array $include_file
     * @param  string|array $exclude_file
     *
     * @return Iterator
     */
    protected function collect($include_dir, $exclude_dir, $include_file, $exclude_file)
    {            
        if (is_null($include_dir)) {
            return [];
        }

        $finder   = new Finder();
        $iterator = $finder->files();
                                
        $iterator->files()
                 ->ignoreVCS(true)
                 ->ignoreDotFiles(true)
                 ->in($include_dir)
                 ->exclude($exclude_dir);
        
        // Files to include
        foreach ((array) $include_file as $file) {
            $iterator->name($file);
        }

        // Files to exclude
        foreach ((array) $exclude_file as $file) {
            $iterator->notName($file);  
        }

        return $iterator;
    }

}