<?php

namespace Spatie\Browsershot\Test;

use AssistedMindfulness\Browsershot\ChromeFinder;
use AssistedMindfulness\Browsershot\Exceptions\CouldNotTakeBrowsershot;

class ChromeFinderTest extends TestCase
{
    /** @test */
    public function it_can_determine_the_location_of_chrome_automatically()
    {
        $this->skipIfNotRunningonMacOS();

        $this->assertStringContainsString('Chrome', ChromeFinder::forCurrentOperatingSystem());
    }

    /** @test */
    public function it_will_throw_an_exception_for_an_unsupported_os()
    {
        $this->expectException(CouldNotTakeBrowsershot::class);

        (new ChromeFinder())->getChromePathForOperatingSystem('Windows');
    }
}
