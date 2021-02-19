<?php

namespace Spatie\Ray\Settings;

class SettingsFactory
{
    public static $cache = [];

    public static function createFromConfigFile(string $configDirectory = null): Settings
    {
        $settingValues = (new static())->getSettingsFromConfigFile($configDirectory);

        $defaultValues = array_merge(Settings::getDefaults(), $settingValues);

        return new Settings($defaultValues);
    }

    public static function createFromArray(array $defaults): Settings
    {
        $settings = new Settings([]);
        $settings->setDefaultSettings($defaults);

        return $settings;
    }

    public static function getSettingsFromConfig(string $configDirectory = null): array
    {
        return (new static())->getSettingsFromConfigFile($configDirectory);
    }

    public function getSettingsFromConfigFile(string $configDirectory = null): array
    {
        $configFilePath = $this->searchConfigFiles($configDirectory);

        if (! file_exists($configFilePath)) {
            return [];
        }

        $options = include $configFilePath;

        return $options ?? [];
    }

    protected function searchConfigFiles(string $configDirectory = null): string
    {
        if (! isset(self::$cache[$configDirectory])) {
            self::$cache[$configDirectory] = $this->searchConfigFilesOnDisk($configDirectory);
        }

        return self::$cache[$configDirectory];
    }

    protected function searchConfigFilesOnDisk(string $configDirectory = null): string
    {
        $configNames = [
            'ray.php',
        ];

        $configDirectory = $configDirectory ?? getcwd();

        while (@is_dir($configDirectory)) {
            foreach ($configNames as $configName) {
                $configFullPath = $configDirectory.DIRECTORY_SEPARATOR.$configName;
                if (file_exists($configFullPath)) {
                    return $configFullPath;
                }
            }

            $parentDirectory = dirname($configDirectory);

            // We do a direct comparison here since there's a difference between
            // the root directories on windows / *nix systems which does not
            // let us compare it against the DIRECTORY_SEPARATOR directly
            if ($parentDirectory === $configDirectory) {
                return '';
            }

            $configDirectory = $parentDirectory;
        }

        return '';
    }
}
