<?php

namespace AssistedMindfulness\Browsershot\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function emptyTempDirectory()
    {
        $tempDirPath = __DIR__.'/temp';

        $files = scandir($tempDirPath);

        foreach ($files as $file) {
            if (! in_array($file, ['.', '..', '.gitignore'])) {
                unlink(sprintf('%s/%s', $tempDirPath, $file));
            }
        }
    }

    public function assertMimeType($expectedMimeType, $path): void
    {
        $actualMimeType = mime_content_type($path);

        $this->assertEquals($expectedMimeType, $actualMimeType, 'MimeType did not match');
    }

    public function skipIfNotRunningOnMacOS(): void
    {
        if (PHP_OS !== 'Darwin') {
            $this->markTestSkipped('Skipping because not running MacOS');
        }
    }
}
