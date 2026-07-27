<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Contracts\ConnectionInterface;
use Sif\Foundation\Contracts\ConnectionManagerInterface;
use Sif\Foundation\Exceptions\ConnectionAlreadyRegisteredException;
use Sif\Foundation\Exceptions\ConnectionNotFoundException;

final class ConnectionRegistry implements ConnectionManagerInterface
{
    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

    public function __construct(
        private ConnectionName $defaultConnection = new ConnectionName('default'),
    ) {
    }

    public function register(ConnectionInterface $connection): void
    {
        $name = $connection->name()->value();

        if (isset($this->connections[$name])) {
            throw new ConnectionAlreadyRegisteredException(
                sprintf('Connection "%s" is already registered.', $name),
            );
        }

        $this->connections[$name] = $connection;
    }

    public function connection(
        ?ConnectionName $name = null,
    ): ConnectionInterface {
        $resolved = ($name ?? $this->defaultConnection)->value();

        if (!isset($this->connections[$resolved])) {
            throw new ConnectionNotFoundException(
                sprintf('Connection "%s" is not registered.', $resolved),
            );
        }

        return $this->connections[$resolved];
    }

    public function has(ConnectionName $name): bool
    {
        return isset($this->connections[$name->value()]);
    }

    public function defaultConnectionName(): ConnectionName
    {
        return $this->defaultConnection;
    }

    public function useDefault(ConnectionName $name): void
    {
        if (!$this->has($name)) {
            throw new ConnectionNotFoundException(
                sprintf(
                    'Connection "%s" cannot become default because it is not registered.',
                    $name->value(),
                ),
            );
        }

        $this->defaultConnection = $name;
    }
}
