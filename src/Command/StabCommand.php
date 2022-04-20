<?php

namespace App\Command;

use App\Service\StabService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class StabCommand extends Command
{
    private StabService $stabService;

    private int $numberOfIterations = 0;

    private float $startTime;

    public function __construct(
        StabService $stabService
    ) {
        $this->startTime   = microtime(true);
        $this->stabService = $stabService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:stab');
        $this->setDescription('This command is Stab for database.');
        $this->setHelp('This command allows you to create user, post with comments and many other by one touch.');
        $this->addArgument(
            'number', 
            InputArgument::OPTIONAL, 
            'It is a number of loop iterations', 
            $this->numberOfIterations
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        if ((int)$input->getArgument('number') > 0) {
            $this->numberOfIterations = $input->getArgument('number');
        } else {
            $output->writeln([
                'Cannot run a cycle with number of iterations = ' . $input->getArgument('number'),
                'Please enter a valid argument (integer > 0) after "app:stab"'
            ]);

            return Command::INVALID;
        }

        $section = $output->section();
        $section->write(
            sprintf('Cycle with the number of iterations %s started', $this->numberOfIterations)
        );
        $this
            ->stabService
            ->toStabDb($this->numberOfIterations);
        $section->overwrite(sprintf(
            'Cycle with the number of iterations = %s completed in %s', 
            $this->numberOfIterations, 
            microtime(true) - $this->startTime
        ));

        $errors = $this->stabService->getErrors();

        if (! empty($errors)) {
            $section->overwrite(sprintf(
                'Cycle (%s) completed in %s with errors, see below', 
                $this->numberOfIterations, 
                microtime(true) - $this->startTime
            ));

            foreach ($errors as $error) {
                $output->writeln($error);
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}