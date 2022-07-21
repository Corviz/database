<?php

use Corviz\Database\Builder;
use ClanCats\Hydrahon\Query\Sql;
use Corviz\Database\Translator\Pgsql;

//pgsql
if (!Builder::hasGrammar('pgsql')) {
    Builder::extend('pgsql', Sql::class, Pgsql::class);
}
