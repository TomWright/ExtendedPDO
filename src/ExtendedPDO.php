<?php


namespace TomWright\Database\ExtendedPDO;


use TomWright\Singleton\SingletonTrait;

class ExtendedPDO extends \PDO
{

    use SingletonTrait;

    /**
     * @var string
     */
    protected $lastQuerySql;

    /**
     * @var QueryHelper
     */
    protected $queryHelper;

    /**
     * @var null|mixed
     */
    protected $defaultQueryResponse = null;


    public function __construct($dsn, $username, $passwd, $options)
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->queryHelper = new QueryHelper();
    }


    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param null|string $instanceId
     * @return static
     */
    public static function createConnection($dsn, $username, $password, $instanceId = null)
    {
        $db = new static($dsn, $username, $password);

        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        if ($instanceId !== null) {
            static::setInstance($instanceId, $db);
        }

        return $db;
    }


    /**
     * @param string $sql
     * @param array|null $bind
     * @param bool $obj
     * @param bool $returnStmt
     * @return bool|\PDOStatement
     */
    public function queryRow($sql, array $bind = null, $obj = true, $returnStmt = false)
    {
        return $this->dbQuery($sql, $bind, 'row', $obj, $returnStmt);
    }


    /**
     * @param string $sql
     * @param array|null $bind
     * @param bool $obj
     * @param bool $returnStmt
     * @return bool|\PDOStatement
     */
    public function queryAll($sql, array $bind = null, $obj = true, $returnStmt = false)
    {
        return $this->dbQuery($sql, $bind, 'all', $obj, $returnStmt);
    }


    /**
     * @param string $sql
     * @param array|null $bind
     * @param string $fetch
     * @param bool $obj
     * @param bool $returnStmt
     * @return bool|\PDOStatement
     */
    public function dbQuery($sql, array $bind = null, $fetch = 'all', $obj = true, $returnStmt = false)
    {
        $sql = $this->queryHelper->trim($sql);

        $fetchType = $obj ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC;

        $this->setLastQuerySql($sql);

        $stmt = $this->prepare($sql);

        if (isset($bind) && is_array($bind)) {
            $stmt->execute($bind);
        } else {
            $stmt->execute();
        }

        $queryType = $this->queryHelper->getQueryType($sql);

        $result = $stmt;

        if (! $returnStmt) {
            $result = $this->getDefaultQueryResponse();
            switch ($queryType) {
                case 'SELECT':
                case 'SHOW':
                    $stmtMethod = ($fetch === 'all') ? 'fetchAll' : 'fetch';
                    if ($stmt->rowCount() > 0) {
                        $result = $stmt->{$stmtMethod}($fetchType);
                    }
                    break;

                case 'INSERT':
                    if ($stmt->rowCount() > 0) {
                        $result = $this->lastInsertId();
                    }
                    break;

                case 'UPDATE':
                case 'DELETE':
                    $result = $stmt->rowCount();
                    break;
            }
        }

        return $result;
    }


    /**
     * @return string
     */
    public function getLastQuerySql()
    {
        return $this->lastQuerySql;
    }


    /**
     * @param string $lastQuerySql
     */
    private function setLastQuerySql($lastQuerySql)
    {
        $this->lastQuerySql = $lastQuerySql;
    }


    /**
     * @return mixed|null
     */
    public function getDefaultQueryResponse()
    {
        return $this->defaultQueryResponse;
    }


    /**
     * @param mixed|null $defaultQueryResponse
     */
    public function setDefaultQueryResponse($defaultQueryResponse)
    {
        $this->defaultQueryResponse = $defaultQueryResponse;
    }

}