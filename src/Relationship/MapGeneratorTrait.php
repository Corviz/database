<?php

namespace Corviz\Database\Relationship;

use ClanCats\Hydrahon\Query\Sql\Select;

trait MapGeneratorTrait
{
    /**
     * @return Select
     */
    protected function generateQuery(): Select
    {
        $fn = [$this->relatedModelName, 'query'];
        /* @var $select Select */
        $select = $fn()->select();

        foreach($this->columnsMap as $thisColumn => $otherColumn) {
            $select->where($otherColumn, $this->model->$thisColumn);
        }

        return $select;
    }
}