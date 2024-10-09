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
    
    const DEFAULT_PATH = '/usr/bin/clamdscan';

    const ERROR_MESSAGE_PERMISSIONS_DENIED = 'Misuse of shell builtins';
    

    public function __construct(
        private readonly string $path,
        private readonly string $env = 'prod')
    {
    }
    
    public function scan(string $filePath): ScannedFile
    {
        if (!is_file($filePath)) {
            throw new FileScanException($filePath, 'Not a file.');
        }
        $process = new Process([$this->path, '--fdpass',  '--no-summary', $filePath]);
        try {
            $process->mustRun();
            $response =  $process->getOutput();
        } catch (ProcessFailedException $exception) {

            if($exception->getProcess()->getExitCode() !== 1){

                if(self::ERROR_MESSAGE_PERMISSIONS_DENIED ===  $exception->getProcess()->getExitCodeText()){
                    throw new \Exception($exception->getProcess()->getErrorOutput());
                }

                $message = 'dev' === $this->env?$exception->getProcess()->getErrorOutput():$exception->getProcess()->getExitCodeText();

                throw new FileScanException($filePath, $message);
            }

            $response = $exception->getProcess()->getOutput();

        }

        return new ScannedFile($response);
    }

    public function version(): string
    {

        $process = new Process([$this->path, '--version']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = $process->getOutput();

        return trim($output);
    }

    public function ping(): bool
    {
        $process = new Process([$this->path, '--ping', '1']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = $process->getOutput();

        return 'PONG' === trim($output);
    }
}
