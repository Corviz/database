<?php

namespace Corviz\Database;

use Exception;
use ClanCats\Hydrahon\Query\Sql\Table;

/**
 * @method static execute(string $query, array $bindings = []): int
 * @method static select(string $query, array $bindings = []): array
 */
abstract class DB
{
    /**
     * @var string|null
     */
    private static ?string $defaultConnection = null;

    /**
     * @var Connection[]
     */
    private static array $connections = [];

    /**
     * Fetch a connection
     *
     * @param string|null $connectionName
     *
     * @return mixed
     * @throws Exception
     */
    public static function connection(string $connectionName = null): Connection
    {
        if (!$connectionName) {
            $connectionName = self::$defaultConnection;

            if (!$connectionName) {
                throw new Exception('Default connection is not set');
            }
        }

        if (!isset(self::$connections[$connectionName])) {
            throw new Exception("Connection not found: $connectionName");
        }

        return self::$connections[$connectionName];
    }

    /**
     * @param string $connectionName
     * @param Connection $connection
     *
     * @return void
     */
    public static function addConnection(string $connectionName, Connection $connection)
    {
        self::$connections[$connectionName] = $connection;

        if (is_null(self::$defaultConnection)) {
            self::$defaultConnection = $connectionName;
        }
    }

    /**
     * @param string $tableName
     *
     * @return Table
     * @throws Exception
     */
    public static function table(string $tableName): Table
    {
        return self::connection()->createBuilder()->table($tableName);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $connection = self::connection();

        if (method_exists($connection, $name)) {
            return $connection->$name(...$arguments);
        }
    }
}
