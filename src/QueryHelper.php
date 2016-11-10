<?php


namespace TomWright\Database\ExtendedPDO;


class QueryHelper
{

    /**
     * @param string $sql
     * @return string
     */
    public function trim($sql)
    {
        return trim($sql);
    }


    /**
     * @param string $sql
     * @return null|string
     */
    public function getQueryType($sql)
    {
        $sql = $this->trim($sql);
        
        $queryType = null;

        $matched = preg_match('/^[\s|\n]*?([A-Za-z]+)[\s|\n]+/', $sql, $matches);
        if ($matched && isset($matches[1])) {
            $queryType = strtoupper($matches[1]);
        }

        return $queryType;
    }

}