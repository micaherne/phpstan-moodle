<?php

use MoodleAnalysis\Component\CoreComponentBridge;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeAbstract;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/*
 * Create a function to create class aliases for all those found in Moodle.
 *
 * The process is:
 *
 * * load the Moodle classloader
 * * generate a crappy classloader for all other classes we find that aren't autoloaded
 * * create the aliases required and gather all the files that were included
 * * write a function that includes those files and creates the aliases
 *
 */

require_once __DIR__ . '/../vendor/autoload.php';

$moodleRoot = realpath(__DIR__ . '/../../moodle');

CoreComponentBridge::loadCoreComponent($moodleRoot);
CoreComponentBridge::registerClassloader();
CoreComponentBridge::loadStandardLibraries();

$classesFinder = new Symfony\Component\Finder\Finder();
$classesFinder->in($moodleRoot)->name('*.php')->files()
    ->exclude(['node_modules', 'vendor'])->contains('class');

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$classNodeFinder = new FindingVisitor(fn(Node $node) => $node instanceof ClassLike);
$traverser = new NodeTraverser(new NameResolver(), $classNodeFinder);
$nodeFinder = new NodeFinder();

$classMap = [];

foreach ($classesFinder as $file) {
    try {
        $nodes = $parser->parse($file->getContents());
    } catch (\PhpParser\Error $e) {
        echo "Unable to parse {$file->getRelativePathname()}: {$e->getMessage()}\n";
        continue;
    }

    $nodes = $traverser->traverse($nodes);
    $classes = $classNodeFinder->getFoundNodes();
    if ($classes === []) {
        continue;
    }

    foreach ($classes as $class) {

        if (!property_exists($class, 'namespacedName')) {
            throw new Exception("Namespaced name not found");
        }

        if (!$class->namespacedName instanceof Node\Name) {
            continue;
        }

        $className = $class->namespacedName->name;

        if (CoreComponentBridge::canAutoloadSymbol($className)) {
            continue;
        }

        echo "Adding class $className from {$file->getRelativePathname()}\n";
        $classMap[$className] = $file->getRelativePathname();
    }
}

file_put_contents(__DIR__ . '/legacy-class-locations.php', '<?php return ' . var_export($classMap, true) . ';');

$includedByLegacyLoader = [];
spl_autoload_register(function($classname) use ($classMap, $moodleRoot, &$includedByLegacyLoader) {
    global $CFG;
    if (array_key_exists($classname, $classMap)) {
        $includedByLegacyLoader[$classMap[$classname]] = 1;
        require_once $moodleRoot . '/' . $classMap[$classname];
    }
});

$renames = CoreComponentBridge::getClassMapRenames();

// TODO: Find other aliases from codebase.

$originalIncludes = get_included_files();

foreach ($renames as $alias => $original) {
    if (!class_exists($original) && !interface_exists($original) && !trait_exists($original) && !enum_exists($original)) {
        echo "Class not found $original\n";
        continue;
    }
    class_alias($original, $alias);
}

$includes = array_values(array_diff(get_included_files(), $originalIncludes));

$relativeIncludes = [];
foreach ($includes as $include) {
    if (!str_starts_with($include, $moodleRoot . '/')) {
        echo "Doesn't start with $moodleRoot: $include\n";
        continue;
    }
    $relativeIncludes[] = substr($include, strlen($moodleRoot . '/'));
}

$builder = new BuilderFactory();

$includeNodes = [];

$out = fopen(__DIR__ . '/create-class-aliases.php', 'w');
fputs($out, "<?php \nglobal \$CFG;\n");
foreach (array_keys($includedByLegacyLoader) as $include) {
    fputs($out, "require_once \$CFG->dirroot . \"/" . $include . "\";\n");
}
foreach ($renames as $alias => $original) {
    if (!class_exists($original) && !interface_exists($original) && !trait_exists($original) && !enum_exists($original)) {
        continue;
    }
    fputs($out, "class_alias($original::class, '$alias');\n");
}