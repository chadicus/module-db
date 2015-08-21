<?php
namespace Codeception\Lib\Driver;

class MySql extends Db
{
    public function cleanup()
    {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        $res = $this->dbh->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE '%TABLE';")->fetchAll();
        foreach ($res as $row) {
            $this->dbh->exec('drop table `' . $row[0] . '`');
        }
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        parent::sqlQuery($query);
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function getQuotedName($name)
    {
        return '`' . str_replace('.', '`.`', $name) . '`';
    }

    /**
     * @param string $tableName
     *
     * @return string
     * @throws \Exception
     */
    public function getPrimaryColumn($tableName)
    {
        if (false === isset($this->primaryColumns[$tableName])) {
            $stmt = $this->getDbh()->query('SHOW KEYS FROM ' . $this->getQuotedName($tableName) . ' WHERE Key_name = "PRIMARY"');
            $columnInformation = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (true === empty($columnInformation)) { // Need a primary key
                throw new \Exception('Table ' . $tableName . ' is not valid or doesn\'t have no primary key');
            }

            $this->primaryColumns[$tableName] = $columnInformation['Column_name'];
        }

        return $this->primaryColumns[$tableName];
    }
}
