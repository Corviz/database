<?php

namespace Corviz\Database;

use ArrayAccess;
use ClanCats\Hydrahon\Query\Sql\Table;
use Exception;
use JsonSerializable;

class Model implements ArrayAccess, JsonSerializable
{
    /**
     * @var string|null
     */
    protected static ?string $connection = null;

    /**
     * @var string|array
     */
    protected static string|array $primaryKey = '';

    /**
     * @var string[]
     */
    protected static array $fields = [];

    /**
     * @var ?string
     */
    protected static ?string $table = null;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * Attempts store values in the current table.
     * Can insert multiple rows at once.
     *
     * @param array|Model $values
     *
     * @return void
     * @throws Exception
     */
    public static function create(array|Model $values): void
    {
        if ($values instanceof Model) {
            $values = $values->values();
        }

        static::query()->insert($values)->execute();
    }

    /**
     * Fetch a row from the current model table and return a new instance.
     * Returns null if not found.
     *
     * @param array|string|int $id
     * @return static|null
     * @throws Exception
     */
    public static function load(array|string|int $id): ?static
    {
        if(!is_array($id)) {
            $pks = (array) static::$primaryKey;

            if (count($pks) != 1) {
                throw new Exception('Invalid primary key provided');
            }

            $id = [$pks[0] => $id];
        }

        $query = static::query()->select();

        foreach ($id as $key => $value) {
            $query->where($key, $value);
        }

        $instance = $query->one();
        return $instance ?: null;
    }

    /**
     * @return Table
     *
     * @throws Exception
     */
    public static function query(): Table
    {
        /* @var $table Table */
        return  DB::connection(static::$connection)
            ->setSelectMutator(function(&$rows){
                foreach ($rows as &$row) {
                    $row = new static((array) $row);
                }
            })
            ->fetchBuilder()
            ->table(static::$table);
    }

    /**
     * Attempt to delete and returns the number of affected rows.
     * Throws an exception on error.
     *
     * @return int
     * @throws Exception
     */
    public function delete(): int
    {
        $deleteQuery = static::query()->delete();

        foreach ($this->keys() as $key => $value) {
            $deleteQuery->where($key, $value);
        }

        return $deleteQuery->execute();
    }

    /**
     * @param array $data
     * @param bool $processSetters
     *
     * @return void
     */
    public function fill(array $data, bool $processSetters = true): void
    {
        if (empty($data)) return;

        $filtered = array_intersect_key($data, array_flip(static::$fields));

        if ($processSetters) {
            foreach ($filtered as $field => $value) {
                $this->$field = $value;
            }
        } else {
            $this->data = array_replace($this->data, $filtered);
        }
    }

    /**
     * Checks if the current object exists in database. True when exists, false otherwise.
     *
     * @return bool
     * @throws Exception
     */
    public function exists(): bool
    {
        if (!$this->keysFilled()) {
            return false;
        }

        $query = static::query()->select(DB::raw(1))->limit(1);

        foreach ($this->keys() as $key => $value) {
            $query->where($key, $value);
        }

        return $query->exists();
    }

    /**
     * @return bool
     */
    public function keysFilled(): bool
    {
        $filled = true;
        $keys = (array) static::$primaryKey;

        foreach ($keys as $key) {
            if (!(isset($this->data[$key]) && !is_null($this->data[$key]))) {
                $filled = false;
                break;
            }
        }

        return $filled;
    }

    /**
     * Attempts to insert the new row. Throws an exception when it fails.
     *
     * @param array $data
     *
     * @return bool
     * @throws Exception
     */
    public function insert(array $data = []): bool
    {
        $this->fill($data);

        $pks = (array) static::$primaryKey;
        $id = static::query()->insert($this->values())->execute();

        if (count($pks) == 1) {
            $this->data[$pks[0]] = $id;

            return isset(((string) $id)[0]);
        }

        return $this->exists();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Get an associative array of primary keys and their respective values.
     *
     * @return array
     */
    public function keys(): array
    {
        $pks = (array) static::$primaryKey;
        $pkValues = [];

        foreach ($pks as $pk) {
            $pkValues[$pk] = $this->data[$pk] ?? null;
        }

        return $pkValues;
    }

    /**
     * Whether an offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->$offset);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->$offset;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value)
    {
        $this->$offset = $value;
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset(mixed $offset)
    {
        unset($this->$offset);
    }

    /**
     * Attempts to update. If not successful, attempts to insert.
     * Returns true if successful, otherwise false.
     *
     * @param array $data
     *
     * @return bool
     * @throws Exception
     */
    public function save(array $data = []): bool
    {
        $this->fill($data);

        if (!$this->update()) {
            return $this->insert();
        }

        return true;
    }

    /**
     * Grab all object data.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Attempt to update returns the number of affected rows.
     * Throws an exception on error.
     *
     * @param array $data
     *
     * @return int
     * @throws Exception
     */
    public function update(array $data = []): int
    {
        $this->fill($data);

        if (!$this->keysFilled()) {
            return 0;
        }

        $data = $this->values();
        $pkValues = $this->keys();

        foreach ($pkValues as $key => $value) {
            unset($data[$key]);
        }

        $updateQuery = static::query()->update($data);

        foreach ($pkValues as $key => $value) {
            $updateQuery->where($key, $value);
        }

        return $updateQuery->execute();
    }

    /**
     * Grabs field values
     *
     * @return array
     */
    public function values(): array
    {
        return array_intersect_key($this->toArray(), array_flip(static::$fields));
    }

    /**
     * Apply getters/setters for specific fields.
     *
     * @param string $fieldName
     * @param $value
     * @param $methodPrefix
     */
    protected function applyModifier(string $fieldName, &$value, $methodPrefix)
    {
        $methodName = $methodPrefix . str_replace('_', '', $fieldName);

        if (method_exists($this, $methodName)) {
            $value = $this->$methodName($value);
        }
    }

    /**
     * @param mixed $data
     */
    public function __construct(mixed $data = null)
    {
        if (!empty($data)) {
            $this->data = (array) $data;
        }
    }

    /**
     * @param $attribute
     *
     * @return mixed
     */
    public function &__get($attribute)
    {
        $value = $this->data[$attribute] ?? null;

        //Apply accessor
        $this->applyModifier($attribute, $value, 'get');

        return $value;
    }

    /**
     * @param $attribute
     *
     * @return bool
     */
    public function __isset($attribute)
    {
        return isset($this->data[$attribute]);
    }

    /**
     * @param $attribute
     * @param $value
     *
     * @throws Exception
     */
    public function __set($attribute, $value)
    {
        //Apply modifier
        $this->applyModifier($attribute, $value, 'set');

        $this->data[$attribute] = $value;
    }

    /**
     * @param $attribute
     */
    public function __unset($attribute)
    {
        unset($this->data[$attribute]);
    }
}
