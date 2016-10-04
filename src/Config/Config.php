<?php

namespace Fixit\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Fixit\Singleton;

class Config extends Singleton {

    /**
     * Store parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The instance of the configuration class
     *
     * @var $this
     */
    protected static $instance;

    /**
     * Process the configuration file into an array
     *
     */
    protected function __construct()
    {
    }

    /**
     * Process the configuration file into an array
     *
     * @param string $filename
     */
    public function load($filename)
    {
        $this->parameters = $this->process($filename);

        return $this;
    }

    /**
     * Handle the configuration settings
     *
     * @param string $filename
     *
     * @return array
     */
    protected function process($filename)
    {    
        $proc = new Processor();       
        
        try {
            return $proc->processConfiguration(
                        new Definition(),
                        $this->parse($filename)
                    );
        } catch (InvalidConfigurationException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Load configuration files and parse them
     *
     * @return array
     */
    protected function parse($filename)
    {    
        $conf[] = Yaml::parse(
            file_get_contents($filename)
        );

        return $conf;
    }

    /**
     * Return a parameter based on a key
     *
     * @param  string $key
     *
     * @return string
     */
    public function get($key, $default = null)
    {       
        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }
        
        $array = $this->parameters;

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Return all the parameters as an array
     *
     * @return array
     */
    public function all()
    {
       return $this->parameters;
    }
}