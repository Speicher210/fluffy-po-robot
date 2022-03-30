<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\PoFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

use function addcslashes;
use function implode;
use function is_array;
use function sprintf;

class PoDumper extends PoFileDumper implements DumperInterface
{
    use DumperTrait;

    /**
     * @param mixed[] $options
     */
    public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string
    {
        $output   = [];
        $output[] = 'msgid ""';
        $output[] = 'msgstr ""';
        $output[] = '"Content-Type: text/plain; charset=UTF-8\n"';
        $output[] = '"Content-Transfer-Encoding: 8bit\n"';
        $output[] = '"Language: ' . $messages->getLocale() . '\n"';
        $output[] = '';

        foreach ($messages->all($domain) as $source => $target) {
            $output[] = sprintf('msgid "%s"', $this->escape($source));
            if (is_array($target)) {
                $output[] = sprintf('msgid_plural "%s"', $this->escape($source));
                $i        = 0;
                foreach ($target as $plural) {
                    $output[] = sprintf('msgstr[%d] "%s"', $i++, $this->escape($plural));
                }
            } else {
                $output[] = sprintf('msgstr "%s"', $this->escape($target));
            }
        }

        return implode("\n", $output);
    }

    private function escape(string $str): string
    {
        return addcslashes($str, "\0..\37\42\134");
    }

    public function getFileExtension(): string
    {
        return $this->getExtension();
    }
}
