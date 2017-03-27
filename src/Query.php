<?php

namespace TomWright\Database\ExtendedPDO;

class Query
{

    public static $RAW_SQL_IDENTIFIER = '_raw_sql_';

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var array
     */
    protected $binds;

    /**
     * @var array
     */
    protected $runtimeBinds;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array|string[]
     */
    protected $fields;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var array
     */
    protected $onDupeValues;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array|Join[]
     */
    protected $joins;

    /**
     * @var array|string[]
     */
    protected $wheres;

    /**
     * @var array|string[]
     */
    protected $orderBys;

    /**
     * @var array|string[]
     */
    protected $groupBys;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int|null
     */
    protected $offset;

    /**
     * @var bool
     */
    protected $building;

    /**
     * @var bool
     */
    protected $convertBoolToInt;

    /**
     * @var int
     */
    protected $queryId;


    /**
     * @param Query|null $query
     * @return Query
     */
    public static function get(Query $query = null)
    {
        if ($query === null) {
            $query = new static();
        }
        return $query;
    }


    /**
     * Query constructor.
     * @param string|null $type
     */
    public function __construct($type = null)
    {
        if ($type !== null) {
            $this->setType($type);
        }
        $this->setFields(['*']);
        $this->setValues([]);
        $this->setOnDupeValues([]);
        $this->setRuntimeBinds([]);
        $this->setBinds([]);
        $this->setBuilding(false);
        $this->setJoins([]);
        $this->setGroupBys([]);
        $this->setOrderBys([]);
        $this->setWheres([]);
        $this->setConvertBoolToInt(true);
    }


    /**
     * @return int
     */
    public function getQueryId()
    {
        if ($this->queryId === null) {
            $this->queryId = rand();
        }
        return $this->queryId;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = strtoupper($type);
        return $this;
    }


    /**
     * @return array|\string[]
     */
    public function getFields()
    {
        return $this->fields;
    }


    /**
     * @param array|\string[] $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }


    /**
     * @param string $field
     * @return $this
     */
    public function addField($field)
    {
        if ($this->fields === null) {
            $this->fields = [];
        }
        if (! in_array($field, $this->fields)) {
            $this->fields[] = $field;
        }
        return $this;
    }


    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }


    /**
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }


    /**
     * @return array|Join[]
     */
    public function getJoins()
    {
        return $this->joins;
    }


    /**
     * @param array|Join[] $joins
     * @return $this
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;
        return $this;
    }


    /**
     * @param Join $join
     * @return $this
     */
    public function addJoin(Join $join)
    {
        if (! in_array($join, $this->joins)) {
            $this->joins[] = $join;
        }
        return $this;
    }


    /**
     * @return array|\string[]
     */
    public function getWheres()
    {
        return $this->wheres;
    }


    /**
     * @param array|\string[] $wheres
     * @return $this
     */
    public function setWheres($wheres)
    {
        $this->wheres = $wheres;
        return $this;
    }


    /**
     * @param string $column
     * @param mixed $value
     * @param bool $raw
     * @return $this
     */
    public function addWhere($column, $value, $raw = false)
    {
        if ($this->wheres === null) {
            $this->wheres = [];
        }
        if ($raw) {
            $column = static::$RAW_SQL_IDENTIFIER . $column;
        }
        $this->wheres[$column] = $value;
        return $this;
    }


    /**
     * @param string $sql
     * @param string $reference
     * @return $this
     */
    public function addRawWhere($sql, $reference = null)
    {
        if ($reference === null) {
            $reference = rand();
        }
        $this->addWhere($reference, $sql, true);
        return $this;
    }


    /**
     * @return array|\string[]
     */
    public function getOrderBys()
    {
        return $this->orderBys;
    }


    /**
     * @param array|\string[] $orderBys
     * @return $this
     */
    public function setOrderBys($orderBys)
    {
        $this->orderBys = $orderBys;
        return $this;
    }


    /**
     * @param string $orderBy
     * @return $this
     */
    public function addOrderBy($orderBy)
    {
        if (! in_array($orderBy, $this->orderBys)) {
            $this->orderBys[] = $orderBy;
        }
        return $this;
    }


    /**
     * @return array|\string[]
     */
    public function getGroupBys()
    {
        return $this->groupBys;
    }


    /**
     * @param array|\string[] $groupBys
     * @return $this
     */
    public function setGroupBys($groupBys)
    {
        $this->groupBys = $groupBys;
        return $this;
    }


    /**
     * @param string $groupBy
     * @return $this
     */
    public function addGroupBy($groupBy)
    {
        if (! in_array($this->groupBys)) {
            $this->groupBys[] = $groupBy;
        }
        return $this;
    }


    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }


    /**
     * @param int|null $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }


    /**
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }


    /**
     * @param int|null $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryType()
    {
        switch ($this->getType()) {
            case 'SELECT':
                $this->sql .= "{$this->getType()}";
                $this->buildQueryFields();
                break;
            case 'UPDATE':
            case 'DELETE':
                $this->sql .= "{$this->getType()}";
                break;
            case 'INSERT':
                $this->sql .= "{$this->getType()} INTO";
                break;
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryTable()
    {
        switch ($this->getType()) {
            case 'SELECT':
            case 'DELETE':
                $this->sql .= " FROM {$this->getTable()}";
                break;
            case 'UPDATE':
            case 'INSERT':
                $this->sql .= " {$this->getTable()}";
                $this->buildQueryFields();
                break;
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryFields()
    {
        $fieldsString = '';

        switch ($this->getType()) {
            case 'SELECT':
                $fields = $this->getFields();
                $fieldsString = implode(', ', $fields);
                break;
            case 'UPDATE':
            case 'INSERT':
                $fieldsString = 'SET ';
                $values = $this->getValues();
                foreach ($values as $name => $val) {
                    if (strpos($name, static::$RAW_SQL_IDENTIFIER) === 0) {
                        $name = substr($name, strlen(static::$RAW_SQL_IDENTIFIER));
                        $fieldsString .= "{$name} = {$val}, ";
                    } else {
                        $bindParamName = ":_{$this->getQueryId()}_update_bind_{$name}";
                        $fieldsString .= "{$name} = {$bindParamName}, ";
                        if ($this->shouldConvertBoolToInt() && is_bool($val)) {
                            $val = $val ? 1 : 0;
                        }
                        $this->addBind($bindParamName, $val);
                    }
                }
                $fieldsString = rtrim($fieldsString, ', ');
                break;
        }

        if (strlen($fieldsString) > 0) {
            $this->sql .= ' ' . $fieldsString;
        }

        return $this;
    }


    /**
     * @return $this
     */
    protected function buildOnDupeValues()
    {
        if ($this->getType() !== 'INSERT') {
            return;
        }

        $values = $this->getOnDupeValues();
        if (! (is_array($values) && count($values) > 0)) {
            return;
        }

        $fieldsString = 'ON DUPLICATE KEY UPDATE ';
        foreach ($values as $name => $val) {
            if (strpos($name, static::$RAW_SQL_IDENTIFIER) === 0) {
                $name = substr($name, strlen(static::$RAW_SQL_IDENTIFIER));
                $fieldsString .= "{$name} = {$val}, ";
            } else {
                $bindParamName = ":_{$this->getQueryId()}_dupe_update_bind_{$name}";
                $fieldsString .= "{$name} = {$bindParamName}, ";
                if ($this->shouldConvertBoolToInt() && is_bool($val)) {
                    $val = $val ? 1 : 0;
                }
                $this->addBind($bindParamName, $val);
            }
        }
        $fieldsString = rtrim($fieldsString, ', ');

        if (strlen($fieldsString) > 0) {
            $this->sql .= ' ' . $fieldsString;
        }

        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryJoins()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return $this;
        }
        $joins = $this->getJoins();
        if (is_array($joins) && count($joins) > 0) {
            foreach ($joins as $join) {
                $this->sql .= " {$join->getType()} {$join->getTable()} ON {$join->getConditionsString()}";
            }
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryWheres()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return $this;
        }
        $wheres = $this->getWheres();

        $whereString = '';

        if (is_array($wheres) && count($wheres) > 0) {
            $firstWhere = true;
            foreach ($wheres as $col => $val) {
                $separator = $firstWhere ? 'WHERE' : ' AND';
                if (strpos($col, static::$RAW_SQL_IDENTIFIER) === 0 && is_string($val)) {
                    $whereString .= "{$separator} {$val}";
                } elseif (is_object($val) && $val instanceof Query && $val->getType() === 'SELECT') {
                    $val->buildQuery();

                    $subSql = $val->getSql();
                    $subBinds = $val->getBinds();

                    $whereSql = str_replace('%SQL%', $subSql, $col);

                    foreach ($subBinds as $k => $v) {
                        $this->addBind($k, $v);
                    }

                    $whereString .= "{$separator} {$whereSql}";
                } elseif (is_array($val)) {
                    $safeCol = str_replace('.', '_', $col);
                    $whereString .= "{$separator} {$col} IN (";

                    $x = 0;
                    foreach ($val as $v) {
                        $x++;
                        $paramId = ":_{$this->getQueryId()}_where_in_{$safeCol}_{$x}";
                        $whereString .= "{$paramId},";
                        if ($this->shouldConvertBoolToInt() && is_bool($v)) {
                            $v = $v ? 1 : 0;
                        }
                        $this->addBind($paramId, $v);
                    }
                    $whereString = rtrim($whereString, ',');

                    $whereString .= ")";
                } else {
                    $comparison = '=';
                    $lastSpace = strrpos($col, ' ');
                    if ($lastSpace !== false) {
                        $comparison = trim(substr($col, $lastSpace + 1));
                        if (strlen($comparison) > 0) {
                            $col = substr($col, 0, $lastSpace);
                        } else {
                            $comparison = '=';
                        }
                    }
                    $safeCol = str_replace('.', '_', $col);

                    $bindId = ":_{$this->getQueryId()}_where_{$safeCol}";
                    $whereString .= "{$separator} {$col} {$comparison} {$bindId}";
                    if ($this->shouldConvertBoolToInt() && is_bool($val)) {
                        $val = $val ? 1 : 0;
                    }
                    $this->addBind($bindId, $val);
                }
                $firstWhere = false;
            }

            $this->sql .= ' ' . $whereString;
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryGroupBys()
    {
        $validTypes = ['SELECT'];
        if (! in_array($this->getType(), $validTypes)) {
            return $this;
        }
        $groupBys = $this->getGroupBys();
        if (is_array($groupBys) && count($groupBys) > 0) {
            $groupByString = '';
            $groupByString .= 'GROUP BY ';
            foreach ($groupBys as $g) {
                $groupByString.= "{$g},";
            }
            $groupByString = rtrim($groupByString, ',');
            $this->sql .= ' ' . $groupByString;
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryOrderBys()
    {
        $validTypes = ['SELECT'];
        if (! in_array($this->getType(), $validTypes)) {
            return $this;
        }
        $orderBys = $this->getOrderBys();
        if (is_array($orderBys) && count($orderBys) > 0) {
            $orderByString = '';
            $orderByString .= 'ORDER BY ';
            foreach ($orderBys as $orderBy) {
                $orderByString .= "{$orderBy},";
            }
            $orderByString = rtrim($orderByString, ',');
            $this->sql .= ' ' . $orderByString;
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryOffset()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return $this;
        }
        if ($this->offset !== null) {
            $this->sql .= " OFFSET {$this->offset}";
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function buildQueryLimit()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return $this;
        }
        if ($this->offset !== null) {
            $this->sql .= " LIMIT {$this->limit}";
        }
        return $this;
    }


    /**
     * @return $this
     * @throws \Exception
     */
    public function buildQuery()
    {
        if ($this->getType() === null) {
            throw new \Exception("Missing Query Type.");
        }
        $this->sql = '';
        $this->setBuilding(true);

        $this->setRuntimeBinds($this->getBinds());

        $this->buildQueryType();
        $this->buildQueryTable();
        $this->buildQueryJoins();
        $this->buildQueryWheres();
        $this->buildOnDupeValues();
        $this->buildQueryGroupBys();
        $this->buildQueryOrderBys();
        $this->buildQueryLimit();
        $this->buildQueryOffset();

        $this->sql = trim($this->sql) . ';';

        $this->setBuilding(false);

        return $this;
    }


    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }


    /**
     * @return array
     */
    public function getBinds()
    {
        if (! $this->isBuilding()) {
            return $this->getRuntimeBinds();
        }
        return $this->binds;
    }


    /**
     * @param array $binds
     * @return $this
     */
    public function setBinds(array $binds)
    {
        if ($this->isBuilding()) {
            return $this->setRuntimeBinds($binds);
        }
        $this->binds = $binds;
        return $this;
    }


    /**
     * @return $this
     */
    public function clearBinds()
    {
        if ($this->isBuilding()) {
            return $this->clearRuntimeBinds();
        }
        $this->setBinds([]);
        return $this;
    }


    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addBind($name, $value)
    {
        if ($this->isBuilding()) {
            return $this->addRuntimeBind($name, $value);
        }
        $this->binds[$name] = $value;
        return $this;
    }


    /**
     * @return array
     */
    public function getRuntimeBinds()
    {
        return $this->runtimeBinds;
    }


    /**
     * @param array $binds
     * @return $this
     */
    public function setRuntimeBinds(array $binds)
    {
        $this->runtimeBinds = $binds;
        return $this;
    }


    /**
     * @return $this
     */
    public function clearRuntimeBinds()
    {
        $this->setRuntimeBinds([]);
        return $this;
    }


    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addRuntimeBind($name, $value)
    {
        $this->runtimeBinds[$name] = $value;
        return $this;
    }


    /**
     * @return bool
     */
    public function isBuilding()
    {
        return ($this->building == true);
    }


    /**
     * @param bool $building
     * @return $this
     */
    public function setBuilding($building)
    {
        $this->building = ($building == true);
        return $this;
    }


    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }


    /**
     * @param array $values
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }


    /**
     * @param string $name
     * @param mixed $value
     * @param bool $raw
     * @return $this
     */
    public function addValue($name, $value, $raw = false)
    {
        if ($raw) {
            $name = static::$RAW_SQL_IDENTIFIER . $name;
        }
        $this->values[$name] = $value;
        return $this;
    }


    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addRawValue($name, $value)
    {
        $this->addValue($name, $value, true);
        return $this;
    }


    /**
     * @return array
     */
    public function getOnDupeValues()
    {
        return $this->onDupeValues;
    }


    /**
     * @param array $onDupeValues
     * @return $this
     */
    public function setOnDupeValues(array $onDupeValues)
    {
        $this->onDupeValues = $onDupeValues;
        return $this;
    }


    /**
     * @param string $name
     * @param mixed $value
     * @param bool $raw
     * @return $this
     */
    public function addOnDupeValue($name, $value, $raw = false)
    {
        if ($raw) {
            $name = static::$RAW_SQL_IDENTIFIER . $name;
        }
        $this->onDupeValues[$name] = $value;
        return $this;
    }


    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addOnDupeRawValue($name, $value)
    {
        $this->addOnDupeValue($name, $value, true);
        return $this;
    }


    /**
     * @param int $page
     * @param null|int $perPage
     * @return $this
     * @throws \Exception
     */
    public function setPage($page, $perPage = null)
    {
        if ($perPage === null) {
            $perPage = $this->getLimit();
        }
        if ($perPage === null) {
            throw new \Exception('Either perPage or limit needs to be set.');
        }
        $this->setOffset(($page - 1) * $perPage);
        $this->setLimit($perPage);
        return $this;
    }


    /**
     * @return bool
     */
    public function shouldConvertBoolToInt()
    {
        return ($this->convertBoolToInt == true);
    }


    /**
     * @param bool $convertBoolToInt
     * @return $this
     */
    public function setConvertBoolToInt($convertBoolToInt)
    {
        $this->convertBoolToInt = ($convertBoolToInt == true);
        return $this;
    }

}