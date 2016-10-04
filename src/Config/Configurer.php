<?php

namespace Fixit\Config;

trait Configurer {

    /**
     * Configuration object
     *
     * @var \Crunz\Configuration\Configuration
     */
    protected $config = null;

    /**
     * Create an instance of Configuration
     *
     * @param string $filename
     */
    public function loadConfig($filename = null)
    {
        if (is_null($filename) && !file_exists($filename)) {
            $filename = __DIR__ . '/../../config.yml';
        }
        
        $this->config = Config::getInstance()->load($filename);
    }

    /**
     * Return a configuration value by key
     *
     * @param  string $key
     *
     * @return string
     */
    protected function config($key)
    {
        if (is_null($this->config)) {
            return;
        }

        return $this->config->get($key);
    }

}