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
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setFields(['*']);
        $this->setValues([]);
        $this->setRuntimeBinds([]);
        $this->setBinds([]);
        $this->setBuilding(false);
        $this->setJoins([]);
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
     */
    public function setType($type)
    {
        $this->type = strtoupper($type);
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
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }


    /**
     * @param string $field
     */
    public function addField($field)
    {
        if ($this->fields === null) {
            $this->fields = [];
        }
        if (! in_array($field, $this->fields)) {
            $this->fields[] = $field;
        }
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
     */
    public function setTable($table)
    {
        $this->table = $table;
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
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;
    }


    /**
     * @param Join $join
     */
    public function addJoin(Join $join)
    {
        if (! in_array($join, $this->joins)) {
            $this->joins[] = $join;
        }
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
     */
    public function setWheres($wheres)
    {
        $this->wheres = $wheres;
    }


    /**
     * @param string $column
     * @param mixed $value
     * @param bool $raw
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
    }


    /**
     * @param string $sql
     * @param string $reference
     */
    public function addRawWhere($sql, $reference = null)
    {
        if ($reference === null) {
            $reference = rand();
        }
        $this->addWhere($reference, $sql, true);
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
     */
    public function setOrderBys($orderBys)
    {
        $this->orderBys = $orderBys;
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
     */
    public function setGroupBys($groupBys)
    {
        $this->groupBys = $groupBys;
    }


    /**
     * @param string $groupBy
     */
    public function addGroupBy($groupBy)
    {
        if (! in_array($this->groupBys)) {
            $this->groupBys[] = $groupBy;
        }
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
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
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
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }


    protected function buildQueryType()
    {
        switch ($this->getType()) {
            case 'SELECT':
                $this->sql .= "{$this->getType()}";
                $this->buildQueryFields();
                break;
            case 'UPDATE':
                $this->sql .= "{$this->getType()}";
                break;
            case 'INSERT':
                $this->sql .= "{$this->getType()} INTO";
                break;
        }
    }


    protected function buildQueryTable()
    {
        switch ($this->getType()) {
            case 'SELECT':
                $this->sql .= " FROM {$this->getTable()}";
                break;
            case 'UPDATE':
            case 'INSERT':
                $this->sql .= " {$this->getTable()}";
                $this->buildQueryFields();
                break;
        }
    }


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
                    $bindParamName = ":_update_bind_{$name}";
                    $fieldsString .= "{$name} = {$bindParamName},";
                    $this->addBind($bindParamName, $val);
                }
                $fieldsString = rtrim($fieldsString, ',');
                break;
        }

        $this->sql .= ' ' . $fieldsString;
    }


    protected function buildQueryJoins()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return;
        }
        $joins = $this->getJoins();
        if (is_array($joins) && count($joins) > 0) {
            foreach ($joins as $join) {
                $this->sql .= " {$join->getType()} {$join->getTable()} ON {$join->getConditionsString()}";
            }
        }
    }


    protected function buildQueryWheres()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return;
        }
        $wheres = $this->getWheres();

        $whereString = '';

        if (is_array($wheres) && count($wheres) > 0) {
            $firstWhere = true;
            foreach ($wheres as $col => $val) {
                $separator = $firstWhere ? 'WHERE' : ' AND';
                if (strpos($col, static::$RAW_SQL_IDENTIFIER) === 0) {
                    $whereString .= "{$separator} {$val}";
                } elseif (is_array($val)) {
                    $safeCol = str_replace('.', '_', $col);
                    $whereString .= "{$separator} {$col} IN (";

                    $x = 0;
                    foreach ($val as $v) {
                        $x++;
                        $paramId = ":_where_in_{$safeCol}_{$x}";
                        $whereString .= "{$paramId},";
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

                    $whereString .= "{$separator} {$col} {$comparison} :_where_{$safeCol}";
                    $this->addBind(":_where_{$safeCol}", $val);
                }
                $firstWhere = false;
            }

            $this->sql .= ' ' . $whereString;
        }
    }


    protected function buildQueryGroupBys()
    {
        $validTypes = ['SELECT'];
        if (! in_array($this->getType(), $validTypes)) {
            return;
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
    }


    protected function buildQueryOrderBys()
    {
        $validTypes = ['SELECT'];
        if (! in_array($this->getType(), $validTypes)) {
            return;
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
    }


    protected function buildQueryOffset()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return;
        }
        if ($this->offset !== null) {
            $this->sql .= " OFFSET {$this->offset}";
        }
    }


    protected function buildQueryLimit()
    {
        $validTypes = ['SELECT', 'UPDATE', 'DELETE'];
        if (! in_array($this->getType(), $validTypes)) {
            return;
        }
        if ($this->offset !== null) {
            $this->sql .= " LIMIT {$this->limit}";
        }
    }


    public function buildQuery()
    {
        $this->sql = '';
        $this->setBuilding(true);

        $this->setRuntimeBinds($this->getBinds());

        $this->buildQueryType();
        $this->buildQueryTable();
        $this->buildQueryJoins();
        $this->buildQueryWheres();
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
     */
    public function setBinds(array $binds)
    {
        if ($this->isBuilding()) {
            return $this->setRuntimeBinds($binds);
        }
        $this->binds = $binds;
    }


    public function clearBinds()
    {
        if ($this->isBuilding()) {
            return $this->clearRuntimeBinds();
        }
        $this->setBinds([]);
    }


    /**
     * @param string $name
     * @param mixed $value
     */
    public function addBind($name, $value)
    {
        if ($this->isBuilding()) {
            return $this->addRuntimeBind($name, $value);
        }
        $this->binds[$name] = $value;
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
     */
    public function setRuntimeBinds(array $binds)
    {
        $this->runtimeBinds = $binds;
    }


    public function clearRuntimeBinds()
    {
        $this->setRuntimeBinds([]);
    }


    /**
     * @param string $name
     * @param mixed $value
     */
    public function addRuntimeBind($name, $value)
    {
        $this->runtimeBinds[$name] = $value;
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
     */
    public function setBuilding($building)
    {
        $this->building = ($building == true);
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
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }


    /**
     * @param string $name
     * @param mixed $value
     */
    public function addValue($name, $value)
    {
        $this->values[$name] = $value;
    }

}