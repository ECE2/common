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
     * 获取指定异常
     * @param string $file 文件路径
     * @param string $exception_name 异常名称
     * @return array
     */
    public static function get_exception($file, $exception_name = 'AppException')
    {

        $files = self::scan($file);
        $exceptions = array();
        foreach ($files as $_file) {
            $_ex_list = self::parse_exception($_file, $exception_name);
            if (! empty($_ex_list)) {
                $exceptions = array_merge($exceptions, $_ex_list);
            }
        }

        return $exceptions;
    }

    /**
     * @param string $file 文件路径
     * @param string $exception_name 异常类名称
     * @return array
     */
    public static function parse_exception($file, $exception_name)
    {
        $contents = file_get_contents($file);
        $ex_list = [];
        $exception = '';
        $getting_exception = false;
        foreach (token_get_all($contents) as $token) {
            if (is_array($token) && $token[0] == T_STRING && preg_match('/' . $exception_name . '$/i', $token[1])) {
                $getting_exception = true;
                continue;
            }

            if ($getting_exception === true) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_PAAMAYIM_NEKUDOTAYIM])) {
                    $exception .= $token[1];
                } elseif ($token === ';') {
                    $ex_list[] = $exception;
                    $exception = '';
                    $getting_exception = false;
                }
            }
        }

        return $ex_list;
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
            [$_ns, $_if] = self::parse_ci($_file, T_CLASS);
            if (! empty($_if)) {
                $classes[] = [$_ns, $_if];
            }
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
            [$_ns, $_if] = self::parse_ci($_file, T_INTERFACE);
            if (! empty($_if)) {
                $interfaces[] = [$_ns, $_if];
            }
        }

        return $interfaces;
    }

    /**
     * @param string $file 文件路径
     * @param int $token_name 常量
     * @return array
     */
    public static function parse_ci($file, $token_name)
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
