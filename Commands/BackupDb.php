<?php

/**
 * ExtraTools
 *
 * @link https://github.com/digitalist-se/extratools
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExtraTools\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Config;
use Piwik\Plugins\ExtraTools\Lib\Backup;

/**
 * This class lets you define a new command. To read more about commands have a look at our Piwik Console guide on
 * http://developer.piwik.org/guides/piwik-on-the-command-line
 *
 * As Piwik Console is based on the Symfony Console you might also want to have a look at
 * http://symfony.com/doc/current/components/console/index.html
 */
class BackupDb extends ConsoleCommand
{
    /**
     * This methods allows you to configure your command. Here you can define the name and description of your command
     * as well as all options and arguments you expect when executing it.
     */
    protected function configure()
    {

        $HelpText = 'The <info>%command.name%</info> command will backup your db.
<comment>Samples:</comment>
To run:
<info>%command.name%</info>
You could use options to override config or environment variables:
<info>%command.name% --db-backup-path=/tmp/foo</info>';
        $this->setHelp($HelpText);
        $this->setName('database:backup');
        $this->setDescription('Backup database');
        $this->addOptionalValueOption(
            'backup-path',
            'b',
            'backup path',
            null
        );
        $this->addOptionalValueOption(
            'backup-prefix',
            'p',
            'prefix for backup name',
            'backup'
        );
        $this->addOptionalValueOption(
            'timeout',
            't',
            'timeout for the process',
            '60'
        );
    }

    /**
     * Execute the command like: ./console backup:db"
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $backup_folder = $input->getOption('backup-path');
        $backup_prefix = $input->getOption('backup-prefix');
        $timeout = $input->getOption('timeout');
        // check if we have db backup path in config
        $configs = Config::getInstance();
        $matomo_tools_config = $configs->getFromLocalConfig('ExtraTools');
        if (!isset($backup_folder)) {
            if (isset($matomo_tools_config['db_backup_path'])) {
                $backup_folder = $matomo_tools_config['db_backup_path'];
            }
        }

        if ($backup_folder == null) {
            $output->writeln("<error>Value for backup-path is required</error>");
            return self::FAILURE;
        }
        $configs = Config::getInstance();
        // Only supporting local config.
        $db_configs = $configs->getFromLocalConfig('database');
        // port is not always set.
        if (!isset($db_configs['port'])) {
            $db_configs['port'] = '3306';
        }
        $config = [
            'db_host' =>  $db_configs['host'],
            'db_port' =>  $db_configs['port'],
            'db_user' => $db_configs['username'],
            'db_pass' => $db_configs['password'],
            'db_name' =>  $db_configs['dbname'],
            'db_backup_folder' => $backup_folder,
            'db_backup_prefix' => $backup_prefix,
            'timeout' => $timeout,
        ];


        $backup = new Backup($config, $output);
        $output->writeln('<info>Starting backup job:</info>');
        $backup->execute();
        return self::SUCCESS;
    }
}
