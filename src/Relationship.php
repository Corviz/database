<?php

namespace Corviz\Database;

use ClanCats\Hydrahon\Query\Sql\Select;

/**
 * @method where($column, $param1 = null, $param2 = null, $type = 'and')
 */
abstract class Relationship
{
    private ?Select $query = null;

    /**
     * @return Model|Model[]|null
     */
    abstract public function fetchRelated(): Model|array|null;

    /**
     * @return Select
     */
    public function query(): Select
    {
        if (!$this->query) {
            $this->query = $this->generateQuery();
        }

        return $this->query;
    }

    /**
     * @return Select
     */
    abstract protected function generateQuery(): Select;

    /**
     * @param string $modelName
     * @param string $relatedModelName
     * @param array $columnsMap
     */
    public function __construct(
        protected Model $model,
        protected string $relatedModelName,
        protected array $columnsMap
    ){
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->query()->$name(...$arguments);
    }
}
