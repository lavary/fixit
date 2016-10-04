<?php

namespace Fixit\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Definition implements ConfigurationInterface {
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('fixit');

        $rootNode            
            
            ->children()
                
                 ->ArrayNode('keyword')
                    ->prototype('scalar')->end()
                    ->defaultValue(['fixme', 'fix-me', 'todo', 'to-do', 'warning'])
                    ->info('The keywords to look for' . PHP_EOL)
                ->end()

                ->scalarNode('pattern')
                    ->defaultValue('\/\/\s*@?(%keyword%):?\s+(.+)')
                    ->info('The comment\s pattern to match' . PHP_EOL)
                ->end()

                ->ArrayNode('titles')
                    ->prototype('scalar')->end()
                    ->defaultValue(['Line', 'Type', 'Comment'])
                    ->info('Column\' title' . PHP_EOL)
                ->end()
                
                ->scalarNode('output_type')
                    ->defaultValue('table')
                    ->info('Default output type' . PHP_EOL)
                ->end()

                ->ArrayNode('include')
                    ->prototype('scalar')->end()
                    ->info('The directories to include' . PHP_EOL)
                ->end()

                 ->ArrayNode('exclude')
                    ->prototype('scalar')->end()
                    ->info('The directories to exclude' . PHP_EOL)
                ->end()

                ->ArrayNode('include_file')
                    ->prototype('scalar')->end()
                    ->info('The files to include' . PHP_EOL)
                ->end()

                ->ArrayNode('exclude_file')
                    ->prototype('scalar')->end()
                    ->info('The Files to exclude' . PHP_EOL)
                ->end()

            ->end()
        ; 

        return $treeBuilder;
    }

}