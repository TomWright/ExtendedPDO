<?php


namespace TomWright\Database\ExtendedPDO;


use TomWright\Database\QueryBuilder\QueryHelper;
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

    /**
     * @var string
     */
    protected $dsn;

    /**
     * @var string
     */
    protected $returnType = ExtendedPDO::RETURN_TYPE_OBJECT;

    const RETURN_TYPE_OBJECT = 'object';
    const RETURN_TYPE_ASSOC = 'assoc';
    const RETURN_TYPE_STMT = 'stmt';


    /**
     * ExtendedPDO constructor.
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param array $options
     */
    public function __construct($dsn, $username = '', $passwd = '', $options = array())
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setDsn($dsn);
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
     * @return bool|\PDOStatement
     */
    public function queryRow($sql, array $bind = null)
    {
        return $this->dbQuery($sql, $bind, 'row');
    }


    /**
     * @param string $sql
     * @param array|null $bind
     * @return bool|\PDOStatement
     */
    public function queryAll($sql, array $bind = null)
    {
        return $this->dbQuery($sql, $bind, 'all');
    }


    /**
     * @param string $sql
     * @param array|null $bind
     * @param string $fetch
     * @return bool|\PDOStatement
     * @throws Exception
     */
    public function dbQuery($sql, ?array $bind = null, $fetch = 'all')
    {
        $sql = $this->queryHelper->trim($sql);

        $this->setLastQuerySql($sql);

        $stmt = $this->prepare($sql);

        if ($stmt === false) {
            $errInfo = $this->errorInfo();
            throw new Exception('Failed to prepare statement: ' . implode(', ', $errInfo));
        }

        $stmt->execute($bind);

        $queryType = $this->queryHelper->getQueryType($sql);

        if ($this->returnType === ExtendedPDO::RETURN_TYPE_STMT) {
            return $stmt;
        }

        switch ($queryType) {
            case 'SELECT':
            case 'SHOW':
                if ($stmt->rowCount() > 0) {
                    $stmtMethod = ($fetch === 'all') ? 'fetchAll' : 'fetch';
                    return $stmt->{$stmtMethod}($this->getSelectQueryFetchType());
                }
                break;

            case 'INSERT':
                if ($stmt->rowCount() > 0) {
                    return $this->lastInsertId();
                }
                break;

            case 'UPDATE':
            case 'DELETE':
                return $stmt->rowCount();
                break;
        }

        return $this->getDefaultQueryResponse();
    }


    /**
     * Returns the fetch type to be used when selecting data.
     * @return int|string
     */
    private function getSelectQueryFetchType()
    {
        switch ($this->returnType) {
            case ExtendedPDO::RETURN_TYPE_OBJECT:
                return \PDO::FETCH_OBJ;
            case ExtendedPDO::RETURN_TYPE_ASSOC:
                return \PDO::FETCH_ASSOC;
        }
        return $this->returnType;
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


    /**
     * Extracts the host from the current connection.
     * @return null|string
     */
    public function getHost()
    {
        $result = null;
        $status = $this->getAttribute(\PDO::ATTR_CONNECTION_STATUS);
        $spacePos = strpos($status, ' ');
        if ($spacePos !== false) {
            $result = substr($status, 0, $spacePos);
        }
        return $result;
    }


    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }


    /**
     * @param string $dsn
     */
    protected function setDsn(string $dsn)
    {
        $this->dsn = $dsn;
    }


    /**
     * @return mixed
     */
    public function getReturnType()
    {
        return $this->returnType;
    }


    /**
     * @param mixed $returnType
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

}