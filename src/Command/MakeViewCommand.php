<?php

namespace Twitnic\Slimer\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeViewCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('make:view')
            ->setDescription('Generate a PHP view template.')
            ->addArgument('name', InputArgument::REQUIRED, 'The view name, for example admin/dashboard.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite an existing file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = trim(str_replace('\\', '/', $input->getArgument('name')), '/');

        if ($name === '') {
            throw new RuntimeException('A view name is required.');
        }

        $viewsPath = $this->project()->configuration()->generator('views_path', 'app/views');
        $path = $this->project()->resolvePath($viewsPath . '/' . $name . '.php');
        $segments = explode('/', $name);
        $title = ucfirst(str_replace(array('-', '_'), ' ', end($segments)));

        $path = $this->generator()->generate(
            $path,
            'view.stub',
            array(
                'DummyTitle' => $title,
                'DummyViewName' => str_replace('/', '-', $name),
            ),
            $input->getOption('force')
        );

        $output->writeln('<info>View created:</info> ' . $path);

        return 0;
    }
}
