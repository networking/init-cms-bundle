<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Lib;

use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdNetwork;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ScannerFactory
{
    public function __construct(#[Autowire(env: 'APP_ENV')] private string $env)
    {
    }

    public function createScanner(array $options)
    {
        switch ($options['strategy']) {
            case 'clamd_cli':
                $scanStrategy = new ScanStrategyClamdCommandLine(
                    $options['socket'] ?? ScanStrategyClamdCommandLine::DEFAULT_PATH,
                    $this->env
                );
                break;
            case 'clamd_unix':
                $scanStrategy = new ScanStrategyClamdUnix(
                    $options['socket'] ?? ScanStrategyClamdUnix::DEFAULT_SOCKET
                );
                break;
            case 'clamd_network':
                $scanStrategy = new ScanStrategyClamdNetwork(
                    $options['host'] ?? ScanStrategyClamdNetwork::DEFAULT_HOST,
                    $options['port'] ?? ScanStrategyClamdNetwork::DEFAULT_PORT
                );
                break;
            default:
                throw new \Exception(sprintf('Unsupported scan strategy "%s" configured', $options['strategy']));
        }

        return new Scanner($scanStrategy);
    }
}
