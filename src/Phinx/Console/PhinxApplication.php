<?php

namespace Phinx\Console;

use Phinx\Console\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Phinx console application.
 * @author Rob Morgan <robbym@gmail.com>
 */
class PhinxApplication extends Application
{
    /**
     * Class Constructor.
     * Initialize the Phinx console application.
     * @param string|null $version The Application Version, if null, use version out of composer.json file
     */
    public function __construct($version = null)
    {
        if ($version === null) {
            $composerConfig = json_decode(file_get_contents(__DIR__ . '/../../../composer.json'));
            $version = $composerConfig->version;
        }
        parent::__construct('ZlsPHP Phinx Package', $version);
        $this->addCommands([
            new Command\Init(),
            new Command\Create(),
            new Command\Migrate(),
            new Command\Rollback(),
            new Command\Status(),
            new Command\Breakpoint(),
            new Command\Test(),
            new Command\SeedCreate(),
            new Command\SeedRun(),
        ]);
    }

    /**
     * Runs the current application.
     * @param \Symfony\Component\Console\Input\InputInterface   $input  An Input instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output An Output instance
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // always show the version information except when the user invokes the help
        // command as that already does it
        if (($input->hasParameterOption(['--help', '-h']) !== false) || ($input->getFirstArgument() !== null && $input->getFirstArgument() !== 'list')) {
            $output->writeln($this->getLongVersion());
            $output->writeln('');
        }

        return parent::doRun($input, $output);
    }
}
