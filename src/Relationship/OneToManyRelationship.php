<?php

namespace Corviz\Database\Relationship;

use ClanCats\Hydrahon\Query\Sql\Exception;
use Corviz\Database\Model;
use Corviz\Database\Relationship;

class OneToManyRelationship extends Relationship
{
    use MapGeneratorTrait;

    /**
     * @return Model|array|Model[]|null
     * @throws Exception
     */
    public function fetchRelated(): Model|array|null
    {
        return $this->query()->get();
    }
}