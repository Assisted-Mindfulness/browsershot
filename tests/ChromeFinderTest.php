<?php

namespace AssistedMindfulness\Browsershot\Test;

use AssistedMindfulness\Browsershot\ChromeFinder;
use AssistedMindfulness\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use PHPUnit\Framework\Attributes\Test;

class ChromeFinderTest extends TestCase
{
    #[Test]
    public function itCanDetermineTheLocationOfChromeAutomatically(): void
    {
        $this->skipIfNotRunningOnMacOS();

        $this->assertStringContainsString('Chrome', ChromeFinder::forCurrentOperatingSystem());
    }

    #[Test]
    public function itWillThrowAnExceptionForAnUnsupportedOs(): void
    {
        $this->expectException(CouldNotTakeBrowsershot::class);

        (new ChromeFinder())->getChromePathForOperatingSystem('Windows');
    }
}
