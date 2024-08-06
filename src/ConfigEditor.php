<?php

namespace ClarionApp\Backend;

class ConfigEditor
{
    public static function update($settingString, $value)
    {
        $parts = explode('.', $settingString);
        $file = array_shift($parts);
        $filename = base_path("config/{$file}.php");

        if (!file_exists($filename)) {
            throw new \Exception("Configuration file does not exist.");
        }

        $config = include($filename);
        $temp = &$config;
        foreach ($parts as $key) {
            if (!isset($temp[$key])) {
                throw new \Exception("Configuration key does not exist.");
            }
            $temp = &$temp[$key];
        }

        // Update the target value
        $temp = $value;

        // Save the modified array back to the file
        $newContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        file_put_contents($filename, $newContent);

        config($settingString, $value);
    }
}
