<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Lib;

use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ScanStrategyClamdCommandLine implements ScanStrategyInterface
{
    public function scan(string $filePath): ScannedFile
    {

        if (!is_file($filePath)) {
            throw new FileScanException($filePath, 'Not a file.');
        }

        $process = new Process(['clamdscan', '--fdpass',  '--no-summary', $filePath]);
        $process->run();


        $response = $process->getOutput();

        return new ScannedFile($response);
    }

    public function version(): string
    {

        $process = new Process(['clamdscan', '--version']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = $process->getOutput();

        return 'PONG' === trim($output);
    }

    public function ping(): bool
    {
        $process = new Process(['clamdscan', '--ping', '1']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = $process->getOutput();

        return 'PONG' === trim($output);
    }
}
