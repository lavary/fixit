
#Fixit

![Version](http://img.shields.io/packagist/v/lavary/fixit.svg?style=flat-square)

Track your "Fixme" comments real quick with just a command!

## Installation


```php
composer global require lavary/fixit
```

After the package is installed, a command-line utility named `fixit` is copied to your `~/.composer/vendor/bin` directory, if installed globally.

To make sure the command is available from anywhere, make sure `~/.composer/vendor/bin` is added to your `PATH` environment variable. Otherwise, you'll have to use the full path to the command.

> You can also install it for a specific project; In that case, it is copied to the `vendor/bin` directory of the project.

To see the available arguments and options, you may run the following command:

```bash
fixit --help
```

## Usage

Fixit tracks and collects all the comments in specified files certain keywords set in the configuration file or as command options.

Comments can be in the following form across the code base. However, it can be configured in the configuration file.

```php
 // KEYWORD some description about it
```

Here's a basic usage:


```bash
fixit scan --include /Path/to/your/code
```

By default, comments starting with keywords  `fixme`, `fix-me`, `todo`, `to-do` are collected by the collector. However, this can be configured in the configuration file (More on this below) or the command option `--keyword`:

```bash
fixit scan --include /Path/to/your/code --keyword fixme --keyword warning
```
As you can see, it is possible to specify several keywords at the same time.

The output could be something like:

```bash
 - File:/Path/to/CodeBase/src/Controller/AdminControllerProvider.php

Line  Type        Comment               
37    fixme       some thing wrong here 
450   warning     This part should be improved 

-File:/Path/to/CodeBase/src/Controller/UserControllerProvider.php

Line Type        Comment                   
59   fixme       This block should be refactored soon

187 file(s) scanned.
 3 issue(s) were found in 2 file(s).
```

> **Note:** If you choose one keyword for a certain issue (fixme, bug, bottleneck, etc), just stick to it to make sure no comment remain untracked.


## Output Types

By default, the output is rendered in a tabular format. However, you can specify the output by `output_type` option in the command line or the configuration file. Three types are supported out of the box: `table`, `json`, and `list`.

```bash
vendor/bin/fixit scan include /Path/to/your/codebase --output_type json
```

Which outputs:

```bash
[  
   {  
      "file":"/Path/to/Codebase/src/AdminControllerProvider.php",
      "items":[  
         {  
            "line":"37",
            "type":"warning",
            "comment":"some thing wrong here"
         }
      ]
   },
   {  
      "file":"/Path/to/Codebase/src/UserController.php",
      "items":[  
         {  
            "line":"59",
            "type":"warning",
            "comment":"another one here"
         }
      ]
   }
]

```
Or as a list:

```bash
vendor/bin/fixit scan --include /Path/to/your/codebase --output_type list
```

```bash
 - File:/Path/to/Codebase/src/Controller/AdminControllerProvider.php
 * 37 warning some thing wrong here
 * 59 warning This part should be improved
 
 - File:/Path/to/Codebase/src/FormApply/Controller/UserController.php
 * 59 warning another one here


187 file(s) scanned.
 2 issue(s) were found in 2 file(s).

```

## Include or Exclude Certain Directories or Files

It is possible to limit the collection to a limited number of directories or files (inside those directories). 

To do this, you can use `--include`, `--exclude`, `include_file`, and `--exclude_file` command options. 

```bash
fixit scan --include path/to/code  --exclude Controller --exclude Model
```
The above command will scan all the files and directories inside `path/to/code` directory except for `Controller` and `Model` directories.

We can also exclude a certain file:

```bash
fixit scan --include path/to/code  --exclude Controller --exclude_file Models/User.php
```

The above command will scan all files inside `path/to/code` directory except for `Controller` directory and `Model/User.php` file.

You can use these options as many time as required, to specify the desired directories and files to scan.

> By default `vendor` directory is ignored.

All these options can also bet set in the configuration file. 

## Configuration

All the options mentioned above can be set via a `YAML` configuration file, which is shipped with the package. 

The configuration file looks like this:

```yml
# Fixit configuration

keyword: ['fixme', 'fix-me', 'todo', 'to-do']

pattern: '\/\/\s*@?(%keyword%):?\s+(.+)'
titles:  ['Line', 'Type', 'Comment']

include: ~
exclude: ~
include_file: ~
exclude_file: ~

output_type: table
```

The option `keyword` is the list of keywords we need to fetch from the files while scanning. You can put any keyword based on your team's conventions.

The `pattern` and `titles` will be covered in the `Advanced Usage` section of this `README` file.

The next set of options is very straightforward as we've already used them on the command line. the options `include`, `exclude`, `include_dir`, `exclude_dir` all accept an array as value:

```yml
# ...

include: ['/Path/to/Code/Model', 'Path/to/Code/Controller']
include_file: ['User.php', 'UserController.php']

# ...
```

The option `output_type` specifies the render type which can be `table`, `json`, or `list`.

> **Note** All these settings are overridden by their command option counterpart

### Editing the Configuration File

To use your own configuration file you need to make a copy of your own.

To make a copy run the following command:

```bash
fixit config:publish

Please input the destination directory
```

You need to specify the path you want to keep the configuration file. It can be anywhere in your system. Finally, you can go to the specified directory to edit the settings as desired.

To use the configuration file just pass it to `fixit scan` command via `--configuration` option:

```bash
fixit scan --configuration path/to/config.yml
```

## Advanced Usage

As you probably remember from the configuration file, there are to more options that you can use if the current settings do not fulfill your requirements. For example, if you want to have more complex comment structures for tracking your "todo" comments, you can change the `pattern` and `titles` options accordingly.

`titles` specifies the column titles when showing the results. 

## If You Need Help

Please submit all issues and questions using GitHub issues and I will try to help you.


## License
Fixit is free software distributed under the terms of the MIT license.
