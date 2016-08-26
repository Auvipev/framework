<?php

/**
 * Leviu.
 *
 * This work would be a little PHP framework, a learn exercice. 
 * 
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2015, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @version 0.1.0
 */
namespace Leviu;

/**
 * PSR-4 Autoloader.
 * 
 * An example of a general-purpose implementation that includes the optional
 * functionality of allowing multiple base directories for a single namespace
 * prefix.
 * 
 * Given a foo-bar package of classes in the file system at the following
 * paths ...
 * 
 *     /path/to/packages/foo-bar/
 *         src/
 *             Baz.php             # Foo\Bar\Baz
 *             Qux/
 *                 Quux.php        # Foo\Bar\Qux\Quux
 *         tests/
 *             BazTest.php         # Foo\Bar\BazTest
 *             Qux/
 *                 QuuxTest.php    # Foo\Bar\Qux\QuuxTest
 * 
 * ... add the path to the class files for the \Foo\Bar\ namespace prefix
 * as follows:
 * 
 *      <?php
 *      // instantiate the loader
 *      $loader = new \Example\Psr4AutoloaderClass;
 *      
 *      // register the autoloader
 *      $loader->register();
 *      
 *      // register the base directories for the namespace prefix
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/src');
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/tests');
 * 
 * or using addNamespaces() method
 * 
 *      <?php
 *      // instantiate the loader
 *      $loader = new \Example\Psr4AutoloaderClass;
 *      
 *      // register the autoloader
 *      $loader->register();
 *      
 *      $nm = [
 *          ['Foo\Bar', '/path/to/packages/foo-bar/src'],
 *          ['Foo\Bar', '/path/to/packages/foo-bar/tests']
 *      ];
 * 
 *      // register the base directories for the namespace prefix
 *      $loader->addNamespaces($nm);
 * 
 * 
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\Quux class from /path/to/packages/foo-bar/src/Qux/Quux.php:
 * 
 *      <?php
 *      new \Foo\Bar\Qux\Quux;
 * 
 * The following line would cause the autoloader to attempt to load the 
 * \Foo\Bar\Qux\QuuxTest class from /path/to/packages/foo-bar/tests/Qux/QuuxTest.php:
 * 
 *      <?php
 *      new \Foo\Bar\Qux\QuuxTest;
 */
class Autoloader
{
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected $prefixes = array();

    /**
     * Register loader with SPL autoloader stack.
     */
    public function register()
    {
        //$this->prefixes = array();
        spl_autoload_register(array($this, 'loadClass'));
        
        
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix   The namespace prefix.
     * @param string $base_dir A base directory for class files in the
     *                         namespace.
     * @param bool   $prepend  If true, prepend the base directory to the stack
     *                         instead of appending it; this causes it to be searched first rather
     *                         than last.
     */
    public function addNamespace($prefix, $base_dir, $prepend = false)
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\').'\\';

        // normalize the base directory with a trailing separator
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR).'/';

        // initialize the namespace prefix array
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }

        // retain the base directory for the namespace prefix
        if ($prepend === true) {
            array_unshift($this->prefixes[$prefix], $base_dir);
            return;
        }
        
        array_push($this->prefixes[$prefix], $base_dir);
        
    }

    /**
     * Adds a base directory for a namespace prefix, accept an array of namespaces
     * Utilize this for prevente multiple addNamespace() calls.
     * 
     * @param array $namespaces The namespace prefix array.
     */
    public function addNamespaces($namespaces)
    {
        foreach ($namespaces as $nsp) {
            $prefix = (string) $nsp[0];
            $baseDir = (string) $nsp[1];
            $prepend = (bool) (isset($nsp[2])) ? $nsp[2] : false;
            
            $this->addNamespace($prefix, $baseDir, $prepend);
            /*
            // normalize namespace prefix
            $prefix = trim($prefix, '\\').'\\';

            // normalize the base directory with a trailing separator
            $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR).'/';

            // initialize the namespace prefix array
            if (isset($this->prefixes[$prefix]) === false) {
                $this->prefixes[$prefix] = array();
            }

            // retain the base directory for the namespace prefix
            if ($prepend) {
                array_unshift($this->prefixes[$prefix], $base_dir);
            } else {
                array_push($this->prefixes[$prefix], $base_dir);
            }*/
        }
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     *
     * @return mixed The mapped file name on success, or boolean false on
     *               failure.
     */
    public function loadClass($class)
    {
        // the current namespace prefix
        $prefix = $class;

        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($prefix, '\\')) {

            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // the rest is the relative class name
            $relativeClass = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);

            //echo $mapped_file.'<br/>';

            if ($mappedFile) {
                return $mappedFile;
            }

            // remove the trailing namespace separator for the next iteration
            // of strrpos()
            $prefix = rtrim($prefix, '\\');
        }

        // never found a mapped file
        return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     * 
     * @param string $prefix         The namespace prefix.
     * @param string $relativeClass The relative class name.
     *
     * @return mixed Boolean false if no mapped file can be loaded, or the
     *               name of the mapped file that was loaded.
     */
    protected function loadMappedFile($prefix, $relativeClass)
    {
        // are there any base directories for this namespace prefix?
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        // look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $baseDir) {

            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $baseDir
                    .str_replace('\\', '/', $relativeClass)
                    .'.php';

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {

                //var_dump($file);
                // yes, we're done
                return $file;
            }
        }

        // never found it
        return false;
    }

    /**
     * If a file exists, require it from the file system.
     * 
     * @param string $file The file to require.
     *
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile($file)
    {
        //var_dump($file);

        if (file_exists($file)) {
            require $file;

            return true;
        }

        return false;
    }
}
