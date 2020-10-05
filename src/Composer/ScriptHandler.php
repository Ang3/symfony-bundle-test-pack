<?php

namespace Ang3\Bundle\Test\Composer;

use Composer\Script\Event;

class ScriptHandler
{
    /**
     * @param Event $event
     */
    public static function copy(Event $event)
    {
        $composer = $event->getComposer();
        $extras = $composer->getPackage()->getExtra();
        $files = $extras['copy-file'];
        $io = $event->getIO();
        $io->write('test');

        if (!$files) {
            $io->write("No dirs or files are configured through the composer extra section.");

            return;
        }

        $composerConfig = $composer->getConfig();
        $vendorDir = $composerConfig->get('vendor-dir');

        var_dump($vendorDir); die;
    }
}