<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

/**
 * Po loader.
 *
 * This is taken from the POFileLoader from Symfony translation.
 */
class PoFileLoader extends FileLoader
{
    protected function loadResource(string $resource):array
    {
        $stream = \fopen($resource, 'rb');

        static $defaults = [
            'ids' => [],
            'translated' => null,
        ];

        $messages = [];
        $item = $defaults;
        $flags = [];

        while ($line = \fgets($stream)) {
            $line = \trim($line);

            if ($line === '') {
                // Whitespace indicated current item is done
                if (!\in_array('fuzzy', $flags, true)) {
                    $this->addMessage($messages, $item);
                }
                $item = $defaults;
                $flags = array();
            } elseif (\substr($line, 0, 2) === '#,') {
                $flags = \array_map('\trim', \explode(',', \substr($line, 2)));
            } elseif (\substr($line, 0, 7) === 'msgid "') {
                // We start a new msg so save previous
                // TODO: this fails when comments or contexts are added
                $this->addMessage($messages, $item);
                $item = $defaults;
                $item['ids']['singular'] = \substr($line, 7, -1);
            } elseif (\substr($line, 0, 8) === 'msgstr "') {
                $item['translated'] = \substr($line, 8, -1);
            } elseif ($line[0] === '"') {
                $continues = isset($item['translated']) ? 'translated' : 'ids';

                if (\is_array($item[$continues])) {
                    \end($item[$continues]);
                    $item[$continues][\key($item[$continues])] .= \substr($line, 1, -1);
                } else {
                    $item[$continues] .= \substr($line, 1, -1);
                }
            } elseif (\substr($line, 0, 14) === 'msgid_plural "') {
                $item['ids']['plural'] = \substr($line, 14, -1);
            } elseif (\substr($line, 0, 7) === 'msgstr[') {
                $size = \strpos($line, ']');
                $item['translated'][(int)\substr($line, 7, 1)] = \substr($line, $size + 3, -1);
            }
        }
        // save last item
        if (!\in_array('fuzzy', $flags, true)) {
            $this->addMessage($messages, $item);
        }
        \fclose($stream);

        return $messages;
    }

    /**
     * Save a translation item to the messages.
     *
     * A .po file could contain by error missing plural indexes. We need to
     * fix these before saving them.
     *
     * @param array $messages
     * @param array $item
     */
    private function addMessage(array &$messages, array $item)
    {
        if (\is_array($item['translated'])) {
            $messages[\stripcslashes($item['ids']['singular'])] = \stripcslashes($item['translated'][0]);
            if (isset($item['ids']['plural'])) {
                $plurals = $item['translated'];
                // PO are by definition indexed so sort by index.
                \ksort($plurals);
                // Make sure every index is filled.
                \end($plurals);
                $count = \key($plurals);
                // Fill missing spots with '-'.
                $empties = \array_fill(0, $count + 1, '-');
                $plurals += $empties;
                \ksort($plurals);
                $plurals = \array_map(
                    function ($text) {
                        return \stripcslashes($text);
                    },
                    $plurals
                );
                $messages[\stripcslashes($item['ids']['plural'])] = $plurals;
            }
        } elseif (!empty($item['ids']['singular'])) {
            $messages[\stripcslashes($item['ids']['singular'])] = \stripcslashes($item['translated']);
        }
    }
}
