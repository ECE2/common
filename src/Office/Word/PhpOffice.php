<?php

namespace Ece2\Common\Office\Word;

use Ece2\Common\Office\Word;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

class PhpOffice extends Word
{
    /**
     * 导出 word 文件.
     * @param string $filename
     * @param string $templateFile
     * @param array $values
     * @param int $clones 克隆次数, 0: 不克隆
     * @param string $cloneBlock
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function export(string $filename, string $templateFile, array $values, $clones = 0, $cloneBlock = 'block')
    {
        $template = new TemplateProcessor($templateFile);
        $template->setValues($values);
        // 克隆
        if ($clones > 0) {
            $template->cloneBlock($cloneBlock, $clones, true);
        }

        ob_start();
        $template->saveAs('php://output');
        $res = $this->downloadWord($filename, ob_get_contents());
        ob_end_clean();

        return $res;
    }
}