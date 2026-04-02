<?php

namespace Twitnic\Slimer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Create a .slimer.php configuration file in the current project.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite the configuration if it already exists.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->project()->hasConfiguration()
            ? $this->project()->configuration()->path()
            : $this->project()->resolvePath('.slimer.php');

        $bootstrap = $this->detectBootstrapTarget();

        $this->generator()->generate(
            $path,
            'slimer-config.stub',
            array(
                'DummyBootstrapPath' => $this->exportPath($bootstrap),
            ),
            $input->getOption('force')
        );

        $output->writeln('<info>Slimer configuration created:</info> ' . $path);

        return 0;
    }

    protected function detectBootstrapTarget()
    {
        $candidates = array(
            'public/index.php',
            'index.php',
            'app/bootstrap.php',
            'bootstrap/app.php',
        );

        foreach ($candidates as $candidate) {
            if ($this->project()->filesystem()->exists($this->project()->resolvePath($candidate))) {
                return $candidate;
            }
        }

        return 'public/index.php';
    }
}
