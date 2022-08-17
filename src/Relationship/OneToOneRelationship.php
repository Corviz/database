<?php

namespace Corviz\Database\Relationship;

use Corviz\Database\Model;
use Corviz\Database\Relationship;

class OneToOneRelationship extends Relationship
{
    use MapGeneratorTrait;

    /**
     * @return Model|Model[]|null
     */
    public function fetchRelated(): Model|array|null
    {
        return $this->query()->one();
    }
}
