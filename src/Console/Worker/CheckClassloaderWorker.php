<?php

namespace MoodlePhpstan\Console\Worker;

use Exception;
use MoodleAnalysis\Component\CoreComponentBridge;
use MoodlePhpstan\MoodleRootManager;
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
use Symfony\Component\Process\Process;

class CheckClassloaderWorker
{

    public function run(string $moodleRoot, LoggerInterface $logger): int {
        $composerInstallProcess = new Process(['composer', 'install'], $moodleRoot);
        $composerInstallProcess->mustRun();

        // This class was deprecated in 3.3 but is still there and conflicts with an alias
        // made in the persistent class file for core_competency.
        if (file_exists($moodleRoot . '/competency/classes/invalid_persistent_exception.php')) {
            unlink($moodleRoot . '/competency/classes/invalid_persistent_exception.php');
        }

        $rootManager = new MoodleRootManager($moodleRoot);
        $rootManager->initialise();

        foreach (CoreComponentBridge::getClassMap() as $class => $file) {
            if (!class_exists($class)) {
                $logger->debug("Class $class not found in the class map");
            }
        }

        foreach (CoreComponentBridge::getClassMapRenames() as $alias => $class) {
            if (!class_exists($class)) {
                $logger->debug("Aliased class $class not found in the class map");
            }
        }

        return Command::SUCCESS;
    }

}