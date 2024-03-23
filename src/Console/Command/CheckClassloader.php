<?php

namespace MoodlePhpstan\Console\Command;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use MoodleAnalysis\Codebase\MoodleCloneProvider;
use MoodleAnalysis\Console\Process\ProcessUtil;
use MoodlePhpstan\Console\Worker\CheckClassloaderWorker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'check:classloader',
    description: 'Check classloader will load all classes and aliased classes',
)]
class CheckClassloader extends Command
{
    #[\Override] protected function configure(): void
    {
        $this->addArgument('moodle-repo', InputArgument::OPTIONAL, 'The path to the Moodle repository')
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
                $clone->getPath()
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

        if (!is_dir($repoLocation)) {
            throw new \InvalidArgumentException('The Moodle repository does not exist');
        }

        $worker = new CheckClassloaderWorker();
        return $worker->run($repoLocation, $logger);
    }


}