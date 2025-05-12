<?php

namespace AssistedMindfulness\Browsershot;

use AssistedMindfulness\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/** @mixin \Spatie\Image\Image */
class Browsershot
{
    protected $html = '';

    protected $pathToChrome = '';

    protected $timeout = 60;

    protected $windowWidth = 0;

    protected $windowHeight = 0;

    protected $disableGpu = true;

    protected $hideScrollbars = true;

    protected $userAgent = '';

    protected $deviceScaleFactor = 1;

    protected $temporaryHtmlDirectory;

    public static function url(string $url): static
    {
        return (new static)->setUrl($url);
    }

    public static function html(string $html): static
    {
        return (new static)->setHtml($html);
    }

    public function __construct(protected string $url = ''){}

    public function setUrl(string $url): static
    {
        $this->url = $url;
        $this->html = '';

        return $this;
    }

    public function setHtml(string $html): static
    {
        $this->html = $html;
        $this->url = '';

        return $this;
    }

    public function setChromePath(string $pathToChrome): static
    {
        $this->pathToChrome = $pathToChrome;

        return $this;
    }

    public function enableGpu(): static
    {
        $this->disableGpu = false;

        return $this;
    }

    public function disableGpu(): static
    {
        $this->disableGpu = true;

        return $this;
    }

    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function userAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function showScrollbars(): static
    {
        $this->hideScrollbars = false;

        return $this;
    }

    public function hideScrollbars(): static
    {
        $this->hideScrollbars = true;

        return $this;
    }

    public function windowSize(int $width, int $height): static
    {
        $this->windowWidth = $width;
        $this->windowHeight = $height;

        return $this;
    }

    public function deviceScaleFactor(int $deviceScaleFactor): static
    {
        // Google Chrome currently supports values of 1, 2, and 3.
        $this->deviceScaleFactor = max(1, min(3, $deviceScaleFactor));

        return $this;
    }

    public function save(string $targetPath)
    {
        if (strtolower(pathinfo($targetPath, PATHINFO_EXTENSION)) === 'pdf') {
            return $this->savePdf($targetPath);
        }

        $temporaryDirectory = (new TemporaryDirectory())->create();

        try {
            $command = $this->createScreenshotCommand($temporaryDirectory->path());

            $process = Process::fromShellCommandline($command)->setTimeout($this->timeout);

            $process->run();

            $this->cleanupTemporaryHtmlFile();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $screenShotPath = $temporaryDirectory->path('screenshot.png');

            if (! file_exists($screenShotPath)) {
                throw CouldNotTakeBrowsershot::chromeOutputEmpty($screenShotPath, $process);
            }

            rename($screenShotPath, $targetPath);
        } finally {
            $temporaryDirectory->delete();
        }

        return null;
    }

    public function bodyHtml(): string
    {
        $command = $this->createBodyHtmlCommand();

        $process = Process::fromShellCommandline($command)->setTimeout($this->timeout);

        $process->run();

        return $process->getOutput();
    }

    public function savePdf(string $targetPath): void
    {
        $command = $this->createPdfCommand($targetPath);

        $process = Process::fromShellCommandline($command)->setTimeout($this->timeout);

        $process->run();

        $this->cleanupTemporaryHtmlFile();
    }

    public function createBodyHtmlCommand(): string
    {
        $url = $this->html
            ? $this->createTemporaryHtmlFile()
            : $this->url;

        $command =
            escapeshellarg($this->findChrome())
            .' --headless --dump-dom';

        if ($this->disableGpu) {
            $command .= ' --disable-gpu';
        }

        if ($this->hideScrollbars) {
            $command .= ' --hide-scrollbars';
        }

        if (! empty($this->userAgent)) {
            $command .= ' --user-agent='.escapeshellarg($this->userAgent);
        }

        return $command.(' '.escapeshellarg($url));
    }

    public function createScreenshotCommand(string $workingDirectory): string
    {
        $url = $this->html ? $this->createTemporaryHtmlFile() : $this->url;

        $command = escapeshellarg($this->findChrome())
            .' --headless --screenshot='.$workingDirectory.'/screenshot.png '
            .escapeshellarg($url);

        if ($this->disableGpu) {
            $command .= ' --disable-gpu';
        }

        if ($this->windowWidth > 0) {
            $command .= ' --window-size='
                .escapeshellarg($this->windowWidth)
                .','
                .escapeshellarg($this->windowHeight);
        }

        if ($this->hideScrollbars) {
            $command .= ' --hide-scrollbars';
        }

        if (! empty($this->userAgent)) {
            $command .= ' --user-agent='.escapeshellarg($this->userAgent);
        }

        if ($this->deviceScaleFactor > 1) {
            $command .= ' --force-device-scale-factor='.escapeshellarg($this->deviceScaleFactor);
        }

        return $command;
    }

    protected function createPdfCommand(string $targetPath): string
    {
        $url = $this->html ? $this->createTemporaryHtmlFile() : $this->url;

        $command =
              escapeshellarg($this->findChrome())
            .(' --headless --print-to-pdf='.$targetPath);

        if ($this->disableGpu) {
            $command .= ' --disable-gpu';
        }

        if ($this->hideScrollbars) {
            $command .= ' --hide-scrollbars';
        }

        if (! empty($this->userAgent)) {
            $command .= ' --user-agent='.escapeshellarg($this->userAgent);
        }

        return $command.(' '.escapeshellarg($url));
    }

    protected function createTemporaryHtmlFile(): string
    {
        $this->temporaryHtmlDirectory = (new TemporaryDirectory())->create();

        file_put_contents($temporaryHtmlFile = $this->temporaryHtmlDirectory->path('index.html'), $this->html);

        return 'file://'.$temporaryHtmlFile;
    }

    protected function cleanupTemporaryHtmlFile()
    {
        if ($this->temporaryHtmlDirectory) {
            $this->temporaryHtmlDirectory->delete();
        }
    }

    protected function findChrome(): string
    {
        if (! empty($this->pathToChrome)) {
            return $this->pathToChrome;
        }

        return ChromeFinder::forCurrentOperatingSystem();
    }
}
