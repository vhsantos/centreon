<?php

/*
 * Copyright 2005-2022 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

require_once 'abstract/JsonWriter.class.php';

/**
 * This class is designed to export commands from pluginpacks.
 */
class Plugins extends JsonWriter
{
    /**
     * @var array<string, string>
     */
    private array $contentToExport = [];

    private Command $command;

    /**
     * @var array<string, array{package: string, command: string, version: string}>
     */
    private array $commandsCache = [];

    private Backend $backend;

    private CentreonDB $db;

    private bool $isCacheLoaded = false;

    /**
     * @param Command $command
     * @param Backend $backend
     * @param CentreonDB $centreonDb
     */
    public function __construct(Command $command, Backend $backend, CentreonDB $centreonDb)
    {
        $this->command = $command;
        $this->backend = $backend;
        $this->db = $centreonDb;
    }

    /**
     * Generate the export file for the plugin commands.
     *
     * @throws Exception
     */
    public function generate(): void
    {
        $this->setFilePath($this->backend->getPath() . DIRECTORY_SEPARATOR . 'plugins.json');
        if (! $this->isCacheLoaded) {
            $this->createPluginCommandsCache();
        }
        $commandIds = array_keys($this->command->getExported());
        foreach ($commandIds as $commandId) {
            $commandToBeExported = $this->command->findCommandById((int) $commandId);
            if ($commandToBeExported !== null) {
                $commandLine = $commandToBeExported['command_line_base'];
                foreach ($this->commandsCache as $pluginCommandLine => $commandsDetail) {
                    if (str_contains($commandLine, $pluginCommandLine)) {
                        $this->exportCommand($commandsDetail);
                        break;
                    }
                }
            }
        }
        $this->flushContent();
    }

    /**
     * Create the cache containing all plugin commands.
     */
    private function createPluginCommandsCache(): void
    {
        $statement = $this->db->query('SELECT command, package_name, version from mod_ppm_plugins_installation');
        if ($statement !== null) {
            $this->isCacheLoaded = true;
            while ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
                /**
                 * @var array{package_name: string, command: string, version: string} $result
                 */
                $this->commandsCache[(string) $result['command']] = [
                    'package' => $result['package_name'],
                    'command' => $result['command'],
                    'version' => $result['version'],
                ];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function flushContent(): int|bool
    {
        $this->setContent($this->contentToExport);
        return parent::flushContent();
    }

    /**
     * Export the plugin command.
     *
     * @param array{package: string, command: string, version: string} $pluginCommand
     */
    private function exportCommand(array $pluginCommand): void
    {
        $this->contentToExport[$pluginCommand['package']] = $pluginCommand['version'];
    }
}
