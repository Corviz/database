<?php

namespace Corviz\Database;

use ClanCats\Hydrahon\Builder;
use Exception;

abstract class Connection
{
    /**
     * Execute query and return thr number of affected rows.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return int
     * @throws Exception
     */
    abstract public function execute(string $query, array $bindings = []): int;

    /**
     * Execute a query and return an array of the selected rows.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return array
     * @throws Exception
     */
    abstract public function select(string $query, array $bindings = []): array;

    /**
     * @return Builder
     */
    abstract public function createBuilder(): Builder;
}