<?php

namespace Phinx\Console\Command;

use Phinx\Config\Config;
use Phinx\Config\ConfigInterface;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\Manager;
use Phinx\Util\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Z;

/**
 * Abstract command, contains bootstrapping info
 * @author Rob Morgan <robbym@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    /**
     * The location of the default migration template.
     */
    const DEFAULT_MIGRATION_TEMPLATE = '/../../Migration/Migration.template.php.dist';

    /**
     * The location of the default seed template.
     */
    const DEFAULT_SEED_TEMPLATE = '/../../Seed/Seed.template.php.dist';

    /**
     * @var \Phinx\Config\ConfigInterface
     */
    protected $config;

    /**
     * @var \Phinx\Db\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @var \Phinx\Migration\Manager
     */
    protected $manager;

    /**
     * Bootstrap Phinx.
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function bootstrap(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getConfig()) {
            $this->loadConfig($input, $output);
        }
        $this->loadManager($input, $output);
        // report the paths
        $paths = $this->getConfig()->getMigrationPaths();
        $output->writeln('<info>using migration paths</info> ');
        foreach (Util::globAll($paths) as $path) {
            $output->writeln('<info> - ' . realpath($path) . '</info>');
        }
        try {
            $paths = $this->getConfig()->getSeedPaths();
            $output->writeln('<info>using seed paths</info> ');
            foreach (Util::globAll($paths) as $path) {
                $output->writeln('<info> - ' . realpath($path) . '</info>');
            }
        } catch (\UnexpectedValueException $e) {
            // do nothing as seeds are optional
        }
    }

    /**
     * Gets the config.
     * @return \Phinx\Config\ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets the config.
     * @param  \Phinx\Config\ConfigInterface $config
     * @return \Phinx\Console\Command\AbstractCommand
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Parse the config file and load it into the config object
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function loadConfig(InputInterface $input, OutputInterface $output)
    {
        $cwd = getcwd();
        $configFilePath = z::realPath($input->getOption('configuration'));
        $config = Config::fromPhp($configFilePath);
        $this->setConfig($config);
    }

    /**
     * Load the migrations manager and inject the config
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function loadManager(InputInterface $input, OutputInterface $output)
    {
        if ($this->getManager() === null) {
            $manager = new Manager($this->getConfig(), $input, $output);
            $this->setManager($manager);
        } else {
            $manager = $this->getManager();
            $manager->setInput($input);
            $manager->setOutput($output);
        }
    }

    /**
     * Gets the migration manager.
     * @return \Phinx\Migration\Manager|null
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Sets the migration manager.
     * @param \Phinx\Migration\Manager $manager
     * @return \Phinx\Console\Command\AbstractCommand
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Gets the database adapter.
     * @return \Phinx\Db\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Sets the database adapter.
     * @param \Phinx\Db\Adapter\AdapterInterface $adapter
     * @return \Phinx\Console\Command\AbstractCommand
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption('--configuration', '-c', InputOption::VALUE_REQUIRED, 'The configuration file to load');
        $this->addOption('--parser', '-p', InputOption::VALUE_REQUIRED, 'Parser used to read the config file. Defaults to YAML');
    }

    /**
     * Verify that the migration directory exists and is writable.
     * @param string $path
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function verifyMigrationDirectory($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Migration directory "%s" does not exist',
                $path
            ));
        }
        if (!is_writable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Migration directory "%s" is not writable',
                $path
            ));
        }
    }

    /**
     * Verify that the seed directory exists and is writable.
     * @param string $path
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function verifySeedDirectory($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Seed directory "%s" does not exist',
                $path
            ));
        }
        if (!is_writable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Seed directory "%s" is not writable',
                $path
            ));
        }
    }

    /**
     * Returns the migration template filename.
     * @return string
     */
    protected function getMigrationTemplateFilename()
    {
        return __DIR__ . self::DEFAULT_MIGRATION_TEMPLATE;
    }

    /**
     * Returns the seed template filename.
     * @return string
     */
    protected function getSeedTemplateFilename()
    {
        return __DIR__ . self::DEFAULT_SEED_TEMPLATE;
    }
}
