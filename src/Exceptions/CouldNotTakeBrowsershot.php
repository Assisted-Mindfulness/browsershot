<?php

namespace AssistedMindfulness\Browsershot\Exceptions;

use RuntimeException;
use Symfony\Component\Process\Process;

class CouldNotTakeBrowsershot extends RuntimeException
{
    public static function operatingSystemNotSupported(string $operatingSystem): static
    {
        return new static(sprintf('The current operating system `%s` is not supported', $operatingSystem));
    }

    /**
     * @param array|string $locations
     */
    public static function chromeNotFound($locations): static
    {
        if (! is_array($locations)) {
            $locations = [$locations];
        }

        $locations = implode(', ', $locations);

        return new static('Did not find Chrome at: '.$locations);
    }

    public static function chromeOutputEmpty(string $screenShotPath, Process $process): static
    {
        $errorOutput = $process->getErrorOutput();

        return new static(sprintf('For some reason Chrome did not write a file at `%s`. Error output: `%s`', $screenShotPath, $errorOutput));
    }
}
