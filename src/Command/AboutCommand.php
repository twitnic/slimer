<?php

namespace Twitnic\Slimer\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twitnic\Slimer\Version;

class AboutCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('about')
            ->setDescription('Display package and project information.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->project()->configuration();
        $bootstrap = $configuration->bootstrap();

        if (is_string($bootstrap)) {
            $bootstrap = $bootstrap;
        } elseif (is_callable($bootstrap)) {
            $bootstrap = 'callable';
        } else {
            $bootstrap = 'auto-detect';
        }

        $table = new Table($output);
        $table->setHeaders(array('Key', 'Value'));
        $table->setRows(array(
            array('Package', 'twitnic/slimer'),
            array('Version', Version::VERSION),
            array('Working directory', $this->project()->workingDirectory()),
            array('Configuration', $configuration->exists() ? $configuration->path() : 'not found'),
            array('Bootstrap', $bootstrap),
            array('Custom commands', (string) count($configuration->commands())),
        ));
        $table->render();

        return 0;
    }
}
