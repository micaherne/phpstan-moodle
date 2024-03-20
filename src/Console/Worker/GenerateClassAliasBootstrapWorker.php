<?php

namespace MoodlePhpstan\Console\Worker;

use Exception;
use MoodleAnalysis\Component\CoreComponentBridge;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateClassAliasBootstrapWorker
{

    public function run(string $moodleRoot, string $outputFile, LoggerInterface $logger): int {
        CoreComponentBridge::loadCoreComponent($moodleRoot);
        CoreComponentBridge::registerClassloader();
        CoreComponentBridge::loadStandardLibraries();

        $classesFinder = new Finder();
        $classesFinder->in($moodleRoot)->name('*.php')->files()
            ->exclude(['node_modules', 'vendor'])->contains('class');

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $classNodeFinder = new FindingVisitor(fn(Node $node) => $node instanceof ClassLike);
        $traverser = new NodeTraverser(new NameResolver(), $classNodeFinder);

        $classMap = [];

        foreach ($classesFinder as $file) {
            try {
                $nodes = $parser->parse($file->getContents());
            } catch (\PhpParser\Error $e) {
               $logger->error("Unable to parse {$file->getRelativePathname()}: {$e->getMessage()}");
                continue;
            }

            $traverser->traverse($nodes);
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

                $logger->debug("Adding class $className from {$file->getRelativePathname()}");
                $classMap[$className] = $file->getRelativePathname();
            }
        }

        $includedByLegacyLoader = [];
        spl_autoload_register(function($classname) use ($classMap, $moodleRoot, &$includedByLegacyLoader) {
            // Do not remove - this is used by some requires.
            global $CFG;
            if (array_key_exists($classname, $classMap)) {
                $includedByLegacyLoader[$classMap[$classname]] = 1;
                require_once $moodleRoot . '/' . $classMap[$classname];
            }
        });

        $renames = CoreComponentBridge::getClassMapRenames();

        // TODO: Find other aliases from codebase.

        foreach ($renames as $alias => $original) {
            if (!class_exists($original) && !interface_exists($original) && !trait_exists($original) && !enum_exists($original)) {
                $logger->warning("Class not found $original");
                continue;
            }
            class_alias($original, $alias);
        }


        $out = fopen($outputFile, 'w');
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

        return Command::SUCCESS;
    }

}