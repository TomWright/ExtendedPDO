<?php

namespace TomWright\Database\ExtendedPDO;

class Like
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $column;

    /**
     * @var string[]
     */
    protected $values;

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var string[]
     */
    protected $binds;

    /**
     * @var int
     */
    protected $likeId;


    public function __construct($type = 'contains', $values = [])
    {
        $this->setType($type);
        $this->setValues($values);
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
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }


    /**
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }


    /**
     * @param string|string[] $values
     */
    public function setValues($values)
    {
        if (is_string($values)) {
            $values = [$values];
        }
        $this->values = $values;
    }


    /**
     * @return \string[]
     */
    public function getValues()
    {
        return $this->values;
    }


    /**
     * @param string $values
     */
    public function addValue($values)
    {
        if (! in_array($values, $this->values)) {
            $this->values[] = $values;
        }
    }


    /**
     * @param Query $query
     * @return Like
     */
    public function build(Query $query)
    {
        $this->sql = '';
        $this->binds = [];
        $totalValues = count($this->values);
        if ($totalValues > 0) {
            $safeCol = $query->getSafeColumn($this->getColumn());

            $this->sql .= '(';

            $x = 0;
            foreach ($this->values as $val) {
                $bindId = ":_{$query->getQueryId()}_where_like_{$this->getLikeId()}_{$safeCol}_{$x}";
                $this->sql .= "{$this->getColumn()} LIKE {$bindId}";
                if ($x < ($totalValues - 1)) {
                    $this->sql .= ' OR ';
                }

                switch ($this->getType()) {
                    case 'contains':
                        $this->binds[$bindId] = "%{$val}%";
                        break;
                    case 'starts_with':
                        $this->binds[$bindId] = "{$val}%";
                        break;
                    case 'ends_with':
                        $this->binds[$bindId] = "%{$val}";
                        break;
                }

                $x++;
            }

            $this->sql .= ')';
        }
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
     * @return \string[]
     */
    public function getBinds()
    {
        return $this->binds;
    }


    /**
     * @return int
     */
    public function getLikeId()
    {
        if ($this->likeId === null) {
            $this->likeId = rand();
        }
        return $this->likeId;
    }

}