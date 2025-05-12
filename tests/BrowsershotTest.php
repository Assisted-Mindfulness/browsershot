<?php

namespace AssistedMindfulness\Browsershot\Test;

use AssistedMindfulness\Browsershot\Browsershot;
use PHPUnit\Framework\Attributes\Test;

class BrowsershotTest extends TestCase
{
    protected function setUp(): void
    {
        $this->emptyTempDirectory();
    }

    #[Test]
    public function itCanGetTheBodyHtml(): void
    {
        $html = $this
            ->getBrowsershotForCurrentEnvironment()
            ->bodyHtml();

        $this->assertStringContainsString('<h1>Example Domain</h1>', $html);
    }

    #[Test]
    public function itCanTakeAScreenshot(): void
    {
        $targetPath = __DIR__.'/temp/testScreenshot.png';

        $this
            ->getBrowsershotForCurrentEnvironment()
            ->save($targetPath);

        $this->assertFileExists($targetPath);
    }

    #[Test]
    public function itCanTakeAScreenshotOfArbitraryHtml(): void
    {
        $targetPath = __DIR__.'/temp/testScreenshot.png';

        $this->configureForCurrentEnvironment(Browsershot::html('<h1>Hello world!!</h1>'))
            ->save($targetPath);

        $this->assertFileExists($targetPath);
    }

    #[Test]
    public function itCanTakeAHighDensityScreenshot(): void
    {
        $targetPath = __DIR__.'/temp/testScreenshot.png';

        $this
            ->getBrowsershotForCurrentEnvironment()
            ->deviceScaleFactor(2)
            ->save($targetPath);

        $this->assertFileExists($targetPath);
    }

    #[Test]
    public function itCanSaveAPdfByUsingThePdfExtension(): void
    {
        $targetPath = __DIR__.'/temp/testPdf.pdf';

        $this
            ->getBrowsershotForCurrentEnvironment()
            ->save($targetPath);

        $this->assertFileExists($targetPath);

        $this->assertMimeType('application/pdf', $targetPath);
    }

    #[Test]
    public function itCanSaveAPngByUsingThePngExtension(): void
    {
        $targetPath = __DIR__.'/temp/testScreenshot.png';

        $this
            ->getBrowsershotForCurrentEnvironment()
            ->save($targetPath);

        $this->assertFileExists($targetPath);

        $this->assertMimeType('image/png', $targetPath);
    }

    #[Test]
    public function itCanCreateACommandToGenerateAScreenshot(): void
    {
        $command = Browsershot::url('https://example.com')
            ->setChromePath('chrome')
            ->createScreenshotCommand('workingDir');

        $this->assertSame("'chrome' --headless --screenshot=workingDir/screenshot.png 'https://example.com' --disable-gpu --hide-scrollbars", $command);
    }

    #[Test]
    public function itCanEnableTheUsageOfTheGpu(): void
    {
        $command = Browsershot::url('https://example.com')
            ->setChromePath('chrome')
            ->enableGpu()
            ->createScreenshotCommand('workingDir');

        $this->assertSame("'chrome' --headless --screenshot=workingDir/screenshot.png 'https://example.com' --hide-scrollbars", $command);
    }

    #[Test]
    public function itCanShowScrollbars(): void
    {
        $command = Browsershot::url('https://example.com')
            ->setChromePath('chrome')
            ->showScrollbars()
            ->createScreenshotCommand('workingDir');

        $this->assertSame("'chrome' --headless --screenshot=workingDir/screenshot.png 'https://example.com' --disable-gpu", $command);
    }

    #[Test]
    public function itCanUseGivenUserAgent(): void
    {
        $command = Browsershot::url('https://example.com')
            ->setChromePath('chrome')
            ->userAgent('my_special_snowflake')
            ->createScreenshotCommand('workingDir');

        $this->assertSame("'chrome' --headless --screenshot=workingDir/screenshot.png 'https://example.com' --disable-gpu --hide-scrollbars --user-agent='my_special_snowflake'", $command);
    }

    protected function getBrowsershotForCurrentEnvironment(string $url = 'https://example.com'): Browsershot
    {
        return $this->configureForCurrentEnvironment(Browsershot::url($url));
    }

    protected function configureForCurrentEnvironment(Browsershot $browsershot): Browsershot
    {
        if (getenv('TRAVIS')) {
            $browsershot->setChromePath('google-chrome-stable');
        }

        return $browsershot;
    }
}
