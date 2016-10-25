<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\PoFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * PO dumper.
 */
class PoDumper extends PoFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = array())
    {
        $output = 'msgid ""' . "\n";
        $output .= 'msgstr ""' . "\n";
        $output .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
        $output .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
        $output .= '"Language: ' . $messages->getLocale() . '\n"' . "\n";
        $output .= "\n";

        $newLine = false;
        foreach ($messages->all($domain) as $source => $target) {
            if ($newLine) {
                $output .= "\n";
            } else {
                $newLine = true;
            }

            $output .= sprintf('msgid "%s"' . "\n", $this->escape($source));
            if (is_array($target)) {
                $output .= sprintf('msgid_plural "%s"' . "\n", $this->escape($source));
                $length = count($target) - 1;
                foreach ($target as $key => $plural) {
                    $output .= sprintf('msgstr[%d] "%s"', $key, $this->escape($plural));
                    if ($key < $length) {
                        $output .= "\n";
                    }
                }
            } else {
                $output .= sprintf('msgstr "%s"', $this->escape($target));
            }
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }

    private function escape($str)
    {
        return addcslashes($str, "\0..\37\42\134");
    }
}
