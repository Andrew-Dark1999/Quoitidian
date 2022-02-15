<?php

/**
 * Class DbCommand
 *
 * @author Aleksandr Roik
 */
class DbCommand extends \CDbCommand
{
    public function insert($table, $columns)
    {
        $params = [];
        $names = [];
        $placeholders = [];
        foreach ($columns as $name => $value) {
            $names[] = $this->getConnection()->quoteColumnName($name);
            $name = Helper::replaceToSqlParam($name);
            if ($value instanceof CDbExpression) {
                $placeholders[] = $value->expression;
                foreach ($value->params as $n => $v) {
                    $params[$n] = $v;
                }
            } else {
                $placeholders[] = ':' . $name;
                $params[':' . $name] = $value;
            }
        }
        $sql = 'INSERT INTO ' . $this->getConnection()->quoteTableName($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        return $this->setText($sql)->execute($params);
    }
}
