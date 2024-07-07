<?php

namespace PhpstanMoodle\Moodle;

use core_h5p\local\library\autoloader;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use stdClass;

/**
 * Loads the core component class from Moodle.
 *
 * Note that this will execute the actual Moodle code. This should be called only once per process, or it will throw an exception,
 * as the class can only be loaded once.
 *
 * This is quite hacky, but there are no better options that I can find.
 */
final class CoreComponentBridge
{
    /** @phpstan-ignore-next-line core_component is loaded from Moodle */
    private static ReflectionClass $componentReflection;

    /** @var array<string, string> */
    private static array $classmap;

    /** @var array<string, string> */
    private static array $classmaprenames;

    private static ReflectionMethod $psrClassloader;

    private static string $moodleRoot;

    /**
     * Load the core component class for the given Moodle.
     *
     * This is static because core_component can only be loaded once per process.
     *
     * @throws \ReflectionException
     */
    public static function loadCoreComponent(string $moodleRoot): void
    {
        global $CFG;

        $moodleRootReal = realpath($moodleRoot);
        if ($moodleRootReal === false) {
            throw new RuntimeException("Moodle root $moodleRoot not found");
        }

        // Only load once per process.
        if (isset(self::$moodleRoot)) {
            if (self::$moodleRoot !== $moodleRootReal) {
                throw new RuntimeException('Core component already loaded with different Moodle root');
            }
            if (!class_exists('\core_component', false)) {
                throw new RuntimeException(
                    'loadCoreComponent() has been called previously but core_component is not loaded'
                );
            }
            return;
        }

        self::$moodleRoot = $moodleRootReal;

        // It may have been loaded some other way.
        if (class_exists('\core_component', false)) {
            throw new RuntimeException('Core component already loaded');
        }

        defined('CACHE_DISABLE_ALL') || define('CACHE_DISABLE_ALL', true);
        defined('MOODLE_INTERNAL') || define('MOODLE_INTERNAL', true);

        $CFG = (object)[
            'dirroot' => self::$moodleRoot,
            'wwwroot' => 'https://localhost',
            'dataroot' => sys_get_temp_dir(),
            'libdir' => self::$moodleRoot . '/lib',
            'admin' => 'admin',
            'cachedir' => sys_get_temp_dir() . '/cache',
            'debug' => 0,
            'debugdeveloper' => false, // formslib checks this
        ];

        // Set include_path so that PEAR libraries are found.
        ini_set('include_path', $CFG->libdir . '/pear' . PATH_SEPARATOR . ini_get('include_path'));

        // A bunch of properties set by setup.php.
        define('SYSCONTEXTID', 1);

        // Make sure there is some database table prefix.
        if (!isset($CFG->prefix)) {
            $CFG->prefix = '';
        }

        // Allow overriding of tempdir but be backwards compatible
        if (!isset($CFG->tempdir)) {
            $CFG->tempdir = $CFG->dataroot . DIRECTORY_SEPARATOR . "temp";
        }

        // Allow overriding of backuptempdir but be backwards compatible
        if (!isset($CFG->backuptempdir)) {
            $CFG->backuptempdir = "$CFG->tempdir/backup";
        }

        // Allow overriding of cachedir but be backwards compatible
        if (!isset($CFG->cachedir)) {
            $CFG->cachedir = "$CFG->dataroot/cache";
        }

        // Allow overriding of localcachedir.
        if (!isset($CFG->localcachedir)) {
            $CFG->localcachedir = "$CFG->dataroot/localcache";
        }

        // Allow overriding of localrequestdir.
        if (!isset($CFG->localrequestdir)) {
            $CFG->localrequestdir = sys_get_temp_dir() . '/requestdir';
        }

        // Location of all languages except core English pack.
        if (!isset($CFG->langotherroot)) {
            $CFG->langotherroot = $CFG->dataroot . '/lang';
        }

        // Location of local lang pack customisations (dirs with _local suffix).
        if (!isset($CFG->langlocalroot)) {
            $CFG->langlocalroot = $CFG->dataroot . '/lang';
        }

        require_once self::$moodleRoot . '/lib/classes/component.php';

        /** @phpstan-ignore-next-line core_component is loaded from Moodle */
        self::$componentReflection = new \ReflectionClass('\core_component');
        self::$componentReflection->getMethod('init')->invoke(null);
        self::$classmap = self::toStringMap(self::$componentReflection->getStaticPropertyValue('classmap'));
        self::$classmaprenames = self::toStringMap(
            self::$componentReflection->getStaticPropertyValue('classmaprenames')
        );
        self::$psrClassloader = self::$componentReflection->getMethod('psr_classloader');
    }

    public static function registerClassloader(): void
    {
        /** @phpstan-ignore-next-line this is a callable */
        spl_autoload_register('core_component::classloader');
    }

    public static function unregisterClassloader(): void
    {
        /** @phpstan-ignore-next-line this is a callable */
        spl_autoload_unregister('core_component::classloader');
    }

    public static function canAutoloadSymbol(string $symbol): bool
    {
        // TODO: Add support for autoloader use, e.g. CAS, Google, etc.
        return array_key_exists($symbol, self::$classmap) || array_key_exists(
                $symbol,
                self::$classmaprenames
            ) || (self::$psrClassloader->invoke(null, $symbol) !== false);
    }

    public static function loadStandardLibraries(): void
    {
        global $CFG;

        require_once($CFG->libdir . '/setuplib.php');

        // Rest taken from setup.php

        // Load up standard libraries.
        require_once($CFG->libdir . '/filterlib.php');       // Functions for filtering test as it is output
        require_once($CFG->libdir . '/ajax/ajaxlib.php');    // Functions for managing our use of JavaScript and YUI
        require_once($CFG->libdir . '/weblib.php');          // Functions relating to HTTP and content
        require_once($CFG->libdir . '/outputlib.php');       // Functions for generating output
        require_once($CFG->libdir . '/navigationlib.php');   // Class for generating Navigation structure
        require_once($CFG->libdir . '/dmllib.php');          // Database access
        require_once($CFG->libdir . '/datalib.php');         // Legacy lib with a big-mix of functions.
        require_once($CFG->libdir . '/accesslib.php');       // Access control functions
        require_once($CFG->libdir . '/deprecatedlib.php');   // Deprecated functions included for backward compatibility
        require_once($CFG->libdir . '/moodlelib.php');       // Other general-purpose functions
        require_once($CFG->libdir . '/enrollib.php');        // Enrolment related functions
        require_once($CFG->libdir . '/pagelib.php');         // Library that defines the moodle_page class, used for $PAGE
        require_once($CFG->libdir . '/blocklib.php');        // Library for controlling blocks
        require_once($CFG->libdir . '/grouplib.php');        // Groups functions
        require_once($CFG->libdir . '/sessionlib.php');      // All session and cookie related stuff
        require_once($CFG->libdir . '/editorlib.php');       // All text editor related functions and classes
        require_once($CFG->libdir . '/messagelib.php');      // Messagelib functions
        require_once($CFG->libdir . '/modinfolib.php');      // Cached information on course-module instances
        require_once($CFG->dirroot . '/cache/lib.php');       // Cache API

    }

    /**
     * Insert the Moodle vendor autoloader into the autoloader stack.
     *
     * This is essential for loading base_testcase.php and advanced_testcase.php.
     *
     * It is inserted at the top of the stack, so that it is the first autoloader to be called. Otherwise,
     * the version of PHPUnit may not be compatible with advanced_testcase etc. for example if you have
     * run composer in this project without the --no-dev flag.
     */
    public static function insertMoodleAutoloader(): void
    {
        global $CFG;

        $autoloaders = spl_autoload_functions();
        spl_autoload_unregister($autoloaders[0]);
        require_once $CFG->dirroot . '/vendor/autoload.php';

        foreach ($autoloaders as $autoloader) {
            spl_autoload_register($autoloader);
        }
    }

    /**
     * The Moodle classloader as-is will crash out on attempting to load certain classes,
     * for example where they extend a class that is not autoloaded. (This is not a problem
     * in Moodle itself, where the relevant includes have been done before the class is
     * loaded.)
     */
    public static function fixClassloader(): void
    {
        global $CFG;

        // Other autoloaders required.
        if (class_exists(autoloader::class)) {
            autoloader::register();
        }

        self::insertMoodleAutoloader();

        foreach (
            [
                '/lib/adminlib.php',
                '/lib/phpunit/classes/base_testcase.php',
                '/lib/phpunit/classes/advanced_testcase.php',
                '/enrol/locallib.php',
                // Required for 4.1 but not 4.2 onwards.
                '/lib/editor/tinymce/plugins/spellchecker/classes/SpellChecker.php',
                // Separate autoloader for pdf library.
                '/mod/assign/feedback/editpdf/fpdi/autoload.php'
            ] as $file
        ) {
            if (file_exists($CFG->dirroot . $file)) {
                require_once $CFG->dirroot . $file;
            }
        }

    }

    /**
     * Get the Moodle config object.
     *
     * This is to avoid calling code having to use globals, and prevent
     * issues with static analysis.
     *
     * @return stdClass the Moodle $CFG object
     */
    public static function getConfig(): stdClass {
        global $CFG;
        return $CFG;
    }

    /**
     * Explicitly convert a map to a string map.
     *
     * This is specifically for use with the getStaticPropertyValue() method of ReflectionClass.
     *
     * @return array<string, string>
     */
    private static function toStringMap(mixed $map): array
    {
        if (!is_array($map)) {
            throw new RuntimeException('Map is not an array');
        }
        $result = [];
        foreach ($map as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                throw new RuntimeException('Map key or value is not a string');
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * @return array<string, string>
     */
    public static function getClassMap(): array
    {
        return self::$classmap;
    }

    /**
     * @return array<string, string>
     */
    public static function getClassMapRenames(): array
    {
        return self::$classmaprenames;
    }


}