<?php
/**
 * 从文件读取类/接口限定名
 */

namespace Ece2\Common\Library;


class NamespaceCI
{

    /**
     * 扫描目录下的所有文件
     * @param string $dir 目录
     * @return array
     */
    public static function scan($dir)
    {
        $files = array();
        if (is_file($dir)) {
            return array($dir);
        }

        if (is_dir($dir) && $handle = opendir($dir)) {
            if (substr($dir, -1) != '/') {
                $dir .= '/';
            }

            while (false !== ($file = readdir($handle))) {
                if ($file == "." || $file == "..") {
                    continue;
                }

                if (is_dir($dir . $file . '/')) {
                    $files = array_merge($files, self::scan($dir . $file . '/'));
                } else {
                    $files[] = $dir . $file;
                }
            }

            closedir($handle);
        }

        return $files;
    }

    /**
     * @param string $file 文件/目录路径
     * @return array
     */
    public static function get_class($file)
    {

        $files = self::scan($file);
        $classes = array();
        foreach ($files as $_file) {
            $classes[] = self::parse_file($_file, T_CLASS);
        }

        return $classes;
    }

    /**
     * @param string $file 文件/目录路径
     * @return array
     */
    public static function get_interface($file)
    {

        $files = self::scan($file);
        $interfaces = array();
        foreach ($files as $_file) {
            $interfaces[] = self::parse_file($_file, T_INTERFACE);
        }

        return $interfaces;
    }

    /**
     * @param string $file 文件路径
     * @param int $token_name 常量
     * @return array
     */
    public static function parse_file($file, $token_name)
    {
        // Grab the contents of the file
        $contents = file_get_contents($file);
        // Start with a blank namespace and class
        $namespace = $class = '';
        // Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = $getting_ci = false;
        // Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {
            // If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }
            // If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == $token_name) {
                $getting_ci = true;
            }
            // While we're grabbing the namespace name...
            if ($getting_namespace === true) {
                // If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED])) {
                    // Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    // If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;
                }
            }

            // While we're grabbing the class name...
            if ($getting_ci === true) {
                // If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {
                    // Store the token's value as the class name
                    $class = $token[1];
                    // Got what we need, stope here
                    break;
                }
            }
        }

        // Build the fully-qualified class name and return it
        return [$namespace, $class];
    }

}
