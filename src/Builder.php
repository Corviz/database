<?php

namespace Corviz\Database;

use ClanCats\Hydrahon\Builder as HydrahonBuilder;

class Builder extends HydrahonBuilder
{
    /**
     * @param mixed $gramarKey
     * @return bool
     */
    public static function hasGrammar(mixed $gramarKey): bool
    {
        return isset(static::$grammar[$gramarKey]);
    }
}
