<?php

namespace Corviz\Database\Translator;

use ClanCats\Hydrahon\Translator\Mysql;

class Pgsql extends Mysql
{
    /**
     * @inheritdoc
     */
    public function escapeIdentifier(mixed $identifier): mixed
    {
        return $identifier;
    }

    /**
     * @inheritdoc
     */
    protected function translateLimitWithOffset(): mixed
    {
        return ' offset ' . ((int) ($this->attr('offset')))
            . ' limit ' . ((int) ($this->attr('limit')));
    }
}