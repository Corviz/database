<?php

namespace Corviz\Database\Connection;

use ClanCats\Hydrahon\Builder;
use ClanCats\Hydrahon\Query\Sql\FetchableInterface;
use ClanCats\Hydrahon\Query\Sql\Insert;
use Corviz\Database\Connection;
use Exception;
use PDO;

class PDOConnection extends Connection
{
    /**
     * @var Builder|null
     */
    private ?Builder $builder = null;

    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * @var mixed
     */
    private mixed $fetchMode;

    /**
     * @var string
     */
    private string $grammar;

    /**
     * @inheritdoc
     */
    public function execute(string $query, array $bindings = []): int
    {
        $statement = $this->pdo->prepare($query);

        if($statement->execute($bindings)){
            return $statement->rowCount();
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function select(string $query, array $bindings = []): array
    {
        $statement = $this->pdo->prepare($query);

        if($statement->execute($bindings)){
            return $statement->fetchAll($this->fetchMode) ?: [];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function createBuilder(): Builder
    {
        if (!$this->builder) {
            $connection = $this->pdo;

            // create a new mysql query builder
            $this->builder = new Builder($this->grammar, function($query, $queryString, $queryParameters) use ($connection) {
                $statement = $connection->prepare($queryString);
                $statement->execute($queryParameters);

                // when the query is fetchable return all results and let hydrahon do the rest
                // (there's no results to be fetched for an update-query for example)
                if ($query instanceof FetchableInterface)
                {
                    return $statement->fetchAll($this->fetchMode);
                }
                // when the query is a instance of a insert return the last inserted id
                elseif($query instanceof Insert)
                {
                    return $connection->lastInsertId();
                }
                // when the query is not a instance of insert or fetchable then
                // return the number os rows affected
                else
                {
                    return $statement->rowCount();
                }
            });
        }

        return $this->builder;
    }

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param mixed $fetchMode
     *
     * @param string $grammar
     */
    public function __construct(
        string $dsn,
        string $username,
        string $password,
        mixed $fetchMode = PDO::FETCH_ASSOC,
        string $grammar = 'mysql',
        array $options = []
    ) {
        $this->fetchMode = $fetchMode;
        $this->grammar = $grammar;
        $defaults = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => $fetchMode,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, $username, $password, array_replace($defaults, $options));
    }
}