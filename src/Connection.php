<?php

namespace Corviz\Database;

use ClanCats\Hydrahon\Builder;
use Exception;

abstract class Connection
{
    /**
     * @var callable|null
     */
    protected mixed $selectMutator = null;

    /**
     * Starts a database transaction.
     *
     * @return bool
     */
    abstract public function beginTransaction(): bool;

    /**
     * Commit changes (after beginTransaction).
     * @return bool
     */
    abstract public function commit(): bool;

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
     * Discard changes (after beginTransaction).
     *
     * @return bool
     */
    abstract public function rollback(): bool;

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
     * @param callable $mutator
     * @return $this
     */
    public function setSelectMutator(callable $mutator): static
    {
        $this->selectMutator = $mutator;

        return $this;
    }

    /**
     * @return Builder
     */
    abstract public function fetchBuilder(): Builder;
}