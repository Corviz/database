<?php

namespace Corviz\Database\Connection;

use ClanCats\Hydrahon\Builder;
use Corviz\Database\Connection;
use Exception;

class PdoConnection extends Connection
{
    public function execute(string $query, array $bindings = []): int
    {
        // TODO: Implement execute() method.
    }

    public function select(string $query, array $bindings = []): array
    {
        // TODO: Implement select() method.
    }

    public function createBuilder(): Builder
    {
        // TODO: Implement createBuilder() method.
    }
}