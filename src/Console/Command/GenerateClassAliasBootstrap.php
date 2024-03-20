<?php

namespace MoodlePhpstan\Console\Command;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use MoodleAnalysis\Codebase\MoodleCloneProvider;
use MoodleAnalysis\Console\Process\ProcessUtil;
use MoodlePhpstan\Console\Worker\GenerateClassAliasBootstrapWorker;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'generate:class-alias-bootstrap',
    description: 'Generate class alias bootstrap files',
)]
class GenerateClassAliasBootstrap extends Command
{
    #[\Override] protected function configure(): void
    {
        $this->addArgument('moodle-repo', InputArgument::OPTIONAL, 'The path to the Moodle repository')
            ->addArgument('output', InputArgument::OPTIONAL, 'The path to the output file')
            ->addOption('worker', 'w', InputOption::VALUE_NONE, 'Run as worker');
    }


    #[\Override] protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isWorker = (bool) $input->getOption('worker');
        if ($isWorker) {
            return $this->executeWorker($input, $output);
        }

        $output->writeln("Cloning Moodle...");
        $cloner = new MoodleCloneProvider();
        $clone = $cloner->cloneMoodle();

        $earliestTagOfInterest = 'v4.1.0';

        $fs = new Filesystem();

        $renamedClassesDirectory = __DIR__ . '/../../../resources/bootstrap-class-aliases';
        $fs->mkdir($renamedClassesDirectory);

        $tags = $clone->getTags();

        $filteredTags = array_filter($tags, fn($tag): bool => Comparator::greaterThanOrEqualTo($tag, $earliestTagOfInterest)
            && VersionParser::parseStability($tag) === 'stable');

        foreach ($filteredTags as $tag) {
            $output->writeln("Checking out $tag");
            $clone->clean();
            $clone->checkout($tag);

            // Spawn new processes to work with the checked out code.
            // This is necessary as we can only load core_component once per process.

            /** @var ProcessHelper $processHelper */
            $processHelper = $this->getHelper('process');

            $commandParts = [
                ...ProcessUtil::getPhpCommand(),
                ...$_SERVER['argv'],
                '--worker',
                $clone->getPath(),
                "$renamedClassesDirectory/$tag.php"
            ];

            $output->writeln("Running worker for $tag");
            $process = $processHelper->run($output, new Process($commandParts, timeout: null));
            $output->writeln($process->getErrorOutput());
            $output->writeln($process->getOutput());
        }

        return Command::SUCCESS;
    }

    private function executeWorker(InputInterface $input, OutputInterface $output): int
    {
        $repoLocation = $input->getArgument('moodle-repo');
        $outputFile = $input->getArgument('output');

        if (!is_dir($repoLocation)) {
            throw new \InvalidArgumentException('The Moodle repository does not exist');
        }

        if (!is_writable(dirname((string) $outputFile))) {
            throw new \InvalidArgumentException('The output file is not writable');
        }

        $worker = new GenerateClassAliasBootstrapWorker();
        return $worker->run($repoLocation, $outputFile, new ConsoleLogger($output));
    }


}