DrupalModuleUpgrader
====================

It's a thing.

How?
----

1. Grab this repo.
2. Enter this repo's directory. `cd drupalmoduleupgrader`
3. Install Composer. `curl -sS https://getcomposer.org/installer | php`
4. Install dependencies. `./composer.phar install` Note that if you do this more than a couple times, GitHub will ask for your credentials. This is normal, if annoying.
5. chmod u+x upgrade
6. Do this: `./upgrade drupal:upgrade [path to files to upgrade]`

You will see a code review of changes you could make to upgrade.


drupal7to8-codesniffer
======================

1. Install the "phpcs-fixer" branch of PHP_CodeSniffer: https://github.com/squizlabs/PHP_CodeSniffer/tree/phpcs-fixer
2. Run it with `phpcs --standard=Drupal7to8 .` in your Drupal 7 module directory
3. ???
4. Profit!

Writing an upgrade sniff
========================

1. Check https://drupal.org/list-changes for something that sounds tasty. Try and prioritize the most frequently used hooks (https://gist.github.com/webchickenator/4409685) over others.
2. If there isn't one already, create a new folder under the Drupal7to8/Sniffs directory for the general "topic" area of the change notice you're coding up. For example, "InfoFiles".
3. Create a new PHP class file in the topic directory called "DescriptiveNameOfChangeSniff.php" (Sniff.php on the end is required) for example, "InfoToYamlSniff.php".
4. Define the PHP class name using the following convention: Drupal7to8_Sniffs_**Topic**_**FilenameWithoutDotPhp**. For example, "class Drupal7to8_Sniffs_InfoFiles_InfoToYamlSniff"
5. Also make sure that it implements the PHP_CodeSniffer_Sniff interface, so: "class Drupal7to8_Sniffs_InfoFiles_InfoToYamlSniff implements PHP_CodeSniffer_Sniff"
6. That interface requires you define two methods: register() and process(). Register() indicates what kind of "tokens" you want this rule to fire on, such as T_COMMENT (for any kind of comments), T_WHITESPACE (for any kind of whitespace), and so on. See the "References" section below for more info.
7. Process() is where you do your logic to identify problems with the code. This function is executed on every line of code that matches the conditions in register(). You get the file that's being processed (and any other info needed like the file name, line of code it's reading, etc.) as well as a pointer to which line in the file is there.
8. Call $phpcsFile->addError() when you want to trigger an error for folks. Make sure to include a reference to the change notice where people can solve their problems!
9. If you want to be *extra* fancy, call $phpcsFile->addFixablError(), and then insert some logic to actually make the code changes to the file. See a write-up at https://drupal.org/node/2099351#comment-8417099.
10. Keep each Sniff to one problem space/change notice to keep the code easy to read/write. (TODO: Not actually sure if that's true... we may want to bundle changes into fewer classes/process functions instead, but let's see how this goes first :))

Resources
=========

**General**

* PHP_CodeSniffer Home page: http://pear.php.net/package/PHP_CodeSniffer/ 
* Manual: http://pear.php.net/manual/en/package.php.php-codesniffer.intro.php
* Drupal Sniffs: http://drupalcode.org/project/coder.git/tree/refs/heads/7.x-2.x:/coder_sniffer/Drupal/Sniffs

**Writing your own**
* "Official" coding standard tutorial: http://pear.php.net/manual/en/package.php.php-codesniffer.coding-standard-tutorial.php
* Another tutorial: http://techportal.inviqa.com/2009/10/12/usphp_code_sniffer/
* Another one: http://www.kingkludge.net/2009/02/codesniffer-part-3-writing-an-example-codesniffer-standard/

**Reference**
* PHP Tokens: http://www.php.net/manual/en/tokens.php
* PHPCS Tokens: https://github.com/squizlabs/PHP_CodeSniffer/blob/master/CodeSniffer/Tokens.php
* Lots of examples of Sniffs you can copy/paste bits from: https://github.com/search?q=implements+PHP_CodeSniffer_Sniff&ref=cmdform&type=Code
