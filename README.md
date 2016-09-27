
#Fixit

![Version](http://img.shields.io/packagist/v/lavary/fixit.svg?style=flat-square)

Track your "Fixme" comments real quick with just a command!

## Installation

```php
composer require lavary/fixit
```

After the package is installed, a command-line utility named `fixit` is copied to your `vendor/bin` directory.

To see the available arguments and options, you may run the following command:

```bash
vendor/bin/fixit --help
```

## Usage

Fixit tracks and collects all the to-do comments following the pattern below:

```php
 // @anyKeyword some description about it
```

`@keyword` should be an alphanumeric identifier. As an example, *fixme*, *todo*, *bug*, *warning* are valid keywords. 

Just keep in mind, if you choose one for a certain case, just stick to it to make sure no comment remain untracked.

For example to get all the *@warning* comments, we can use `--level` option:

```bash
vendor/bin/fixit scan --level warning /path/to/your/codebase
```

The output should look like this:

```bash
 - File:/Path/to/CodeBase/src/Controller/AdminControllerProvider.php

 # Line  Type        Comment               
 1 37    warning     some thing wrong here 
 2 450   warning     This part should be improved 

-File:/Path/to/CodeBase/src/Controller/UserControllerProvider.php

 # Line Type        Comment                   
 1 59   warning     This block will break soon                            
```

You can also specify multiple keywords:

```bash
vendor/bin/fixit scan --level=warning --level=todo --level=enhancement /Path/to/your/codebase
```

Output:

```bash
 - File:/Path/to/CodeBase/src/Controller/AdminControllerProvider.php

 # Line  Type        Comment               
 1 37    warning     some thing wrong here 
 2 450   warning     This part should be improved 

-File:/Path/to/CodeBase/src/Controller/UserControllerProvider.php

 # Line Type         Comment                   
 1 59   warning      This block will break soon  
 2 62   enhancement  This block can be improved                            
```

If you want to collect all the comments matching the pattern regardless of the level name, you can run the command without `-level` option:

```bash
vendor/bin/fixit scan /Path/to/your/codebase
```
Output:

```bash
 - File:/Path/to/CodeBase/src/Controller/AdminControllerProvider.php

 # Line  Type        Comment               
 1 37    warning     some thing wrong here 
 2 450   warning     This part should be improved 

-File:/Path/to/CodeBase/src/Controller/UserControllerProvider.php

 # Line Type         Comment                   
 1 59   warning      This block will break soon  
 2 62   enhancement  This logic should be moved to a service 
 3 80   fixme        This doesn't work with strings
```

## Output Types

By default, the output is rendered in a tabular format. However, you can have the output as a list or JSON:

```bash
vendor/bin/fixit scan /Path/to/your/codebase --as json
```

Which outputs:

```bash
[  
   {  
      "file":"/Path/to/Codebase/src/AdminControllerProvider.php",
      "items":[  
         {  
            "row":1,
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
            "row":1,
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
vendor/bin/fixit scan /Path/to/your/codebase --as list
```

```bash
187 file(s) scanned.
 2 issue(s) were found in 2 file(s).

 - File:/Path/to/Codebase/src/Controller/AdminControllerProvider.php
 * 1 37 warning some thing wrong here
 * 2 59 warning This part should be improved
 
 - File:/Path/to/Codebase/src/FormApply/Controller/UserController.php
 * 1 59 warning another one here
```

## Targeting specific File Types

You can target a specific file type instead of scanning all the files. To do this, you can use `--ext` option as below:


```bash
vendor/bin/fixit scan /Path/to/your/codebase --ext=js  --as list
```

Or:

```bash
vendor/bin/fixit scan /Path/to/your/codebase --ext=php  --as list
```

## If You Need Help

Please submit all issues and questions using GitHub issues and I will try to help you.


## License
Fixit is free software distributed under the terms of the MIT license.
