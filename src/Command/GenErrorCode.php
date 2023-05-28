<?php

declare(strict_types=1);

namespace Ece2\Common\Command;

use Ece2\Common\Library\NamespaceCI;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenErrorCode
 */
#[Command]
class GenErrorCode extends HyperfCommand
{
    protected ?string $name = 'ece2:error-code-gen';

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php ece2:error-code-gen <--code | -C <code>> [--lang | -L [lang]]"');
        $this->setDescription('Generate error code to src/Constants/ErrorCode.php');
    }

    public function handle()
    {
        // 获取 code
        $code = $this->input->getOption('code');
        if (empty($code)) {
            $code = 'ErrorCode';
        }
        // 异常常量文件
        $code = ucfirst(trim($code));
        $code_file = BASE_PATH . '/app/Constants/' . $code . '.php';
        if (! file_exists($code_file)) {
            $this->line($code_file . ' file not exists!', 'error');
        }

        // 解析文件
        $classes = NamespaceCI::get_class($code_file);
        if (empty($classes)) {
            $this->line($code_file . ' file not class!', 'error');
        }
        // 获取类的命名空间和类名
        [$namespace, $class] = current($classes);
        // 利用反射获取所有常量
        $ref = new \ReflectionClass($namespace ? $namespace . '\\' . $class : $class);
        $constants = $ref->getConstants();
        // 获取最大的异常编号
        $code_max = 100000;
        foreach ($constants as $_code) {
            $_index = substr((string) $_code, 3);
            if ($_index > $code_max) {
                $code_max = $_index;
            }
        }

        // 读取语言配置
        $translate_file = BASE_PATH . '/config/autoload/translation.php';
        if (! file_exists($translate_file)) {
            $this->line($translate_file . ' file not exists!', 'error');
        }
        $translateCfg = include($translate_file);
        // 获取 lang
        $lang = $this->input->getOption('lang');
        if (empty($lang)) {
            $lang = $translateCfg['locale'];
        }
        $lang = lcfirst(trim($lang));
        // 从语言文件中读取语言配置
        $lang_file = $translateCfg['path'] . '/' . $lang . '/messages.php';
        if (! file_exists($lang_file)) {
            $this->line($lang_file . ' file not exists!', 'error');
        }
        $langCfg = include($lang_file);

        // 根据异常生成相关常量
        $exceptions = NamespaceCI::get_exception(BASE_PATH . '/app/');
        $error_code_stub = file_get_contents(__DIR__ . '/ErrorCode.stub');
        $const_new = [];
        foreach ($exceptions as $_ex) {
            [$_class_name, $_const_name] = explode('::', $_ex);
            if (isset($constants[$_const_name])) {
                continue;
            }

            $code_max++;
            [$_lang_key, $_code_index] = $this->_gen_lang_code($_const_name, $code_max);
            $const_new[] = sprintf($error_code_stub, $_lang_key, $_const_name, $_code_index);
            $constants[$_const_name] = $_code_index;
            $this->line($_const_name . ' Added');
        }

        // 如果有新的异常常量
        if (! empty($const_new)) {
            $content = file_get_contents($code_file);
            $content = trim($content, " \r\n\}") . "\n" . implode($const_new) . "}\n";
            file_put_contents($code_file, $content);
        }

        // 根据常量生成语言
        $lang_stub = file_get_contents(__DIR__ . '/messages.stub');
        $lang_new = [];
        foreach ($constants as $_const_name => $_code) {
            [$_lang_key,] = $this->_gen_lang_code($_const_name, $code_max);
            if (empty($langCfg[$_lang_key])) {
                $lang_new[] = sprintf($lang_stub, $_lang_key, $_lang_key);
                $this->line($_lang_key . ' Added');
            }
        }

        // 如果需要新增语言
        if (! empty($lang_new)) {
            $content = file_get_contents($lang_file);
            $content = trim($content, " \n\r\]\),;") . ",\n" . implode($lang_new) . "];\n";
            file_put_contents($lang_file, $content);
        }

        $this->info('error code generate successfully.');
    }

    protected function _gen_lang_code($const_name, $code)
    {
        $ar = explode('_', $const_name);
        $code_index = '500' . $code;
        $lang_key = strtolower($const_name);
        if (count($ar) > 1 && is_numeric($ar[1])) {
            $code_index = $ar[1] . $code;
            $lang_key = strtolower(implode('_', array_slice($ar, 2)));
        }

        return [$lang_key, $code_index];
    }

    protected function getOptions(): array
    {
        return [
            ['code', '-C', InputOption::VALUE_REQUIRED, 'Please enter the error code file'],
            ['lang', '-L', InputOption::VALUE_OPTIONAL, 'Please enter the lang file with the ErrorCode.']
        ];
    }
}
