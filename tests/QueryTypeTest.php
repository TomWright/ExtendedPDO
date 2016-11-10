<?php


use PHPUnit\Framework\TestCase;
use TomWright\Database\ExtendedPDO\QueryHelper;

class QueryTypeTest extends TestCase
{

    public function testQueryTypeWorks()
    {
        $helper = new QueryHelper();

        $sql = 'SELECT * FROM MY_TABLE';
        $this->assertEquals('SELECT', $helper->getQueryType($sql));

        $sql = '  
          SELECT 
          * FROM MY_TABLE';
        $this->assertEquals('SELECT', $helper->getQueryType($sql));

        $sql = '  
          SELECT    * FROM
          MY_TABLE';
        $this->assertEquals('SELECT', $helper->getQueryType($sql));
    }

}