<?php

namespace MoodlePhpstan\Console\Command;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use MoodleAnalysis\Codebase\MoodleClone;
use MoodleAnalysis\Codebase\MoodleCloneProvider;
use MoodleAnalysis\Console\Process\ProcessUtil;
use MoodlePhpstan\Console\Worker\GenerateClassloaderBootstrapWorker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'generate:classloader-bootstrap',
    description: 'Generate class loader bootstrap files',
)]
class GenerateClassloaderBootstrap extends Command
{
    #[\Override] protected function configure(): void
    {
        $this->addArgument('moodle-repo', InputArgument::OPTIONAL, 'The path to the Moodle repository')
            ->addArgument('output', InputArgument::OPTIONAL, 'The path to the output file')
            ->addOption('worker', 'w', InputOption::VALUE_NONE, 'Run as worker');
    }


    #[\Override] protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $isWorker = (bool) $input->getOption('worker');
        if ($isWorker) {
            return $this->executeWorker($input, $logger);
        }

        // Ensure git and composer are installed in the path.
        /*if (exec('git --version') !== 0 || exec('composer --version') !== 0) {
            $output->writeln("Please ensure git and composer are installed and in the path.");
            return Command::FAILURE;
        }*/

        $logger->info("Cloning Moodle...");
        $cloner = new MoodleCloneProvider();
        $clone = $cloner->cloneMoodle();

        $earliestTagOfInterest = 'v4.1.0';

        $fs = new Filesystem();

        $classloaderBootstrapDirectory = __DIR__ . '/../../../resources/bootstrap-classloader';
        $fs->mkdir($classloaderBootstrapDirectory);

        $tags = $clone->getTags();

        $filteredTags = array_filter($tags, fn($tag): bool => Comparator::greaterThanOrEqualTo($tag, $earliestTagOfInterest)
            && VersionParser::parseStability($tag) === 'stable');

        foreach ($filteredTags as $tag) {
            $logger->info("Checking out $tag");
            $clone->clean();
            $clone->checkout($tag);

            $composerProcess = new Process(['composer', 'install', '--no-interaction'], $clone->getPath());
            $composerProcess->mustRun();

            // Spawn new processes to work with the checked out code.
            // This is necessary as we can only load core_component once per process.

            /** @var ProcessHelper $processHelper */
            $processHelper = $this->getHelper('process');

            $commandParts = [
                ...ProcessUtil::getPhpCommand(),
                ...$_SERVER['argv'],
                '--worker',
                $clone->getPath(),
                "$classloaderBootstrapDirectory/$tag.php"
            ];

            $logger->debug("Running worker for $tag");
            $process = $processHelper->run($output, new Process($commandParts, timeout: null));
            $output->writeln($process->getErrorOutput());
            $output->writeln($process->getOutput());
        }

        $clone->delete();

        return Command::SUCCESS;
    }

    private function executeWorker(InputInterface $input, LoggerInterface $logger): int
    {
        $repoLocation = $input->getArgument('moodle-repo');
        $outputFile = $input->getArgument('output');

        if (!is_dir($repoLocation)) {
            throw new \InvalidArgumentException('The Moodle repository does not exist');
        }

        if (!is_writable(dirname((string) $outputFile))) {
            throw new \InvalidArgumentException('The output file is not writable');
        }

        $worker = new GenerateClassloaderBootstrapWorker();
        return $worker->run($repoLocation, $outputFile, $logger);
    }


}