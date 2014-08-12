<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03-18
 * @desc model orm, must be based on pdo!
 *
 */
class modelorm extends model{

    const DB_TYPE_SLAVE = 'slave';
    const DB_TYPE_MASTER = 'master';
    const DB_TYPE_AUTO = 'auto';

    protected $_dbAlias = null;
    protected $_table = null;
    protected $_dbObj = array();
    protected $_dbObjMode = self::DB_TYPE_AUTO;
    // auto | master | slave
    protected $_currentDbCon = null;
    protected $_sql = null;
    protected $_dangerCheck = null;
    protected $_pages = null;
    protected $_lastQuery = null;

    public function __construct($dbAlias = null, $tablename = null) {
        if ($dbAlias) {
            $this->_dbAlias = $dbAlias;
        }
        if ($tablename) {
            $this->_table = $tablename;
        }
        $this->_clearStat();
        parent::__construct();
    }

    public static function instance($dbAlias) {
        return new self($dbAlias);
    }

    private function _clearStat() {
        $this->_sql = array();
        $this->_dangerCheck = true;
        //$this->_dbObjMode = 'auto';
        $this->_pages = false;
    }

    /**
     * @usage
     *      after                           modelORM::instance($dbAlias)->getPdo(db_mysqlpdo::TYPE_SERVER_MASTER);  to get your pdo object
     *          this equals to              new db_mysqlpdo($alias, $conf)->connectSlave();
     * @param type $type
     * @return type
     */
    public function getPdo($type = null) {
        if ($type === null) {
            $type = ($this->_dbObjMode !== self::DB_TYPE_MASTER) ? self::DB_TYPE_SLAVE : self::DB_TYPE_MASTER;
        }
        if ($type != self::DB_TYPE_MASTER) {
            return isset($this->_dbObj[self::DB_TYPE_SLAVE]) ? $this->_dbObj[self::DB_TYPE_SLAVE] :
                ($this->_dbObj[self::DB_TYPE_SLAVE] = $this->_getPdoServerWithAlias($this->_dbAlias, self::DB_TYPE_SLAVE));
        } else {
            return isset($this->_dbObj[self::DB_TYPE_MASTER]) ? $this->_dbObj[self::DB_TYPE_MASTER] :
                ($this->_dbObj[self::DB_TYPE_MASTER] = $this->_getPdoServerWithAlias($this->_dbAlias, self::DB_TYPE_MASTER));
        }
    }

    protected function _getPdoServerWithAlias($alias, $type = self::DB_TYPE_SLAVE) {
        $dataDriver = db::instance($alias, $type);
        return $dataDriver;
    }

    public function setPdo($pdoObject, $type = self::DB_TYPE_SLAVE) {
        $this->_dbObj[$type] = $pdoObject;
    }

    protected function _getPdoByMethodName($operationType = null) {
        if ($this->_dbObjMode !== self::DB_TYPE_AUTO) {
            return $this->getPdo($this->_dbObjMode);
        }
        if (in_array($operationType, array('insert', 'update', 'delete'))) {
            return $this->getPdo(self::DB_TYPE_MASTER);
        } else {
            return $this->getPdo(self::DB_TYPE_SLAVE);
        }
    }

    /**
     * set database mode to master
     */
    public function setDbMaster() {
        $this->_dbObjMode = self::DB_TYPE_MASTER;
        return $this;
    }

    /**
     * set database mode slave
     */
    public function setDbSlave() {
        $this->_dbObjMode = self::DB_TYPE_SLAVE;
        return $this;
    }

    /**
     * specific the table to query in
     * @param type $tableName
     * @return self
     */
    public function table($tableName) {
        $this->_table = $tableName;
        return $this;
    }

    public function closeDangerCheck() {
        $this->_dangerCheck = false;
    }

    private function _checkDanger($method) {
        if (empty($this->_sql['where']) && $this->_dangerCheck) {
            $this->_raiseError('unsafe mode checked for method: ' . $method, exception_mysqlpdo::TYPE_HIGH_RISK_QUERY);
        }
    }

    public function insert($data, $getLastInsertId = false) {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        if (empty($data) || !is_array($data)) {
            $this->_raiseError('insert query data empty~', exception_mysqlpdo::TYPE_INPUT_DATA_ERROR);
        }
        $fields = array_keys($data);
        $values = array_values($data);
        $insteads = array_fill(0, count($values), '?');

        $sql = 'INSERT INTO ' . $this->_table . '(' . implode(',', $fields) . ') VALUE(' . implode(',', $insteads) . ')';
        $this->_lastQuery = array($sql, $values);

        $db = $this->_getPdoByMethodName(__FUNCTION__);
        $sth = $db->prepare($sql);
        $res = $sth->execute($values);

        $this->_clearStat();
        if (!$res) {
            auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));
            return $res;
        }
        if ($getLastInsertId) {
            $lastInsertId = $db->lastInsertId();
            //有时候table 可能没有primary key
            if ($lastInsertId) {
                auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));
                return $lastInsertId;
            }
        }
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }

    public function update($data) {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $this->_checkDanger(__FUNCTION__);
        if (empty($data) || !is_array($data)) {
            $this->_raiseError('empty data for update function query', exception_mysqlpdo::TYPE_INPUT_DATA_ERROR);
        }
        $fields = array_keys($data);
        $values = array_values($data);

        foreach ($fields as $k => $f) {
            $fields[$k] = $f . '= ? ';
        }
        $where = $this->_getWhere();
        $sql = 'UPDATE ' . $this->_table . ' SET ' . implode(',', $fields) . $where['sql'];
        $sqlData = $where['data'];
        if (is_array($sqlData)) {
            $values = util::array_merge($values, $sqlData);
        }

        $this->_lastQuery = array($sql, $values);
        $sth = $this->_getPdoByMethodName(__FUNCTION__)
            ->prepare($sql);
        $res = $sth->execute($values);

        $this->_clearStat();
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }

    public function delete() {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $this->_checkDanger(__FUNCTION__);

        $values = array();
        $where = $this->_getWhere();
        $sql = 'DELETE FROM ' . $this->_table . $where['sql'];
        $sqlData = $where['data'];
        if (is_array($sqlData)) {
            $values = util::array_merge($values, $sqlData);
        }
        $this->_lastQuery = array($sql, $sqlData);

        $sth = $this->_getPdoByMethodName(__FUNCTION__)->prepare($sql);
        $res = $sth->execute($values);

        $this->_clearStat();
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }

    public function fields($fields = array()) {
        $this->_sql['fields'] = $fields;
        return $this;
    }

    public function select() {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $where = $this->_getWhere();
        $sql = $where['sql'];
        $sqlData = $where['data'];
        $values = array();
        if (is_array($sqlData)) {
            $values = util::array_merge($values, $sqlData);
        }
        if (isset($this->_sql['order'])) {
            $sql .= ' ORDER BY ' . $this->_sql['order'];
        }
        if (isset($this->_sql['limit'])) {
            $sql .= ' LIMIT ' . $this->_sql['limit'];
        }
        if (isset($this->_sql['fields'])) {
            $fields = implode(',', $this->_sql['fields']);
        } else {
            $fields = '*';
        }
        $sql = 'SELECT ' . $fields . ' FROM ' . $this->_table . $sql;
        $this->_lastQuery = array($sql, $values);

        $sth = $this->_getPdoByMethodName(__FUNCTION__)->prepare($sql);
        $res = $sth->execute($values);
        if (!$res) {
            $this->_raiseError('select query failed~', exception_mysqlpdo::TYPE_QUERY_ERROR);
        }
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);

        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));

        $this->_clearStat();

        return $res;
    }

    public function getOne() {
        $data = $this->limit(1)->select();
        if (is_array($data)) {
            $data = array_shift($data);
        }
        return $data;
    }

    public function count($key = '') {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $countKey = empty($key) ? '*' : $key;

        $where = $this->_getWhere();
        $sql = $where['sql'];
        $sqlData = $where['data'];
        $values = array();
        if (is_array($sqlData)) {
            $values = util::array_merge($values, $sqlData);
        }

        $sql = "SELECT COUNT({$countKey}) FROM " . $this->_table . $sql;
        $this->_lastQuery = array($sql, $values);
        $sth = $this->_getPdoByMethodName(__FUNCTION__)->prepare($sql);
        $res = $sth->execute($values);
        if (!$res) {
            $this->_raiseError('count query failed~', exception_mysqlpdo::TYPE_QUERY_ERROR);
        }
        $count = $sth->fetchColumn();
        $this->_clearStat();
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));

        return $count;
    }

    protected function _raiseError($msg, $code = -2) {
        if (auto::isDebugMode()) {
            throw new exception_mysqlpdo('mysql query failed, ' . $msg . ' :' . var_export($this->getLastQuery(), true), $code);
        } else {
            throw new exception_mysqlpdo('mysql query failed, ' . $msg, $code);
        }
    }

    /*
      public function page(&$pageinfo){
      $this->_pages = $pageinfo;
      }

     */

    public function where($where, $data = null) {
        $this->_sql['where'] = $where;
        $this->_sql['whereData'] = $data;
        return $this;
    }

    protected function _getWhere() {
        if ($this->_sql['where'] == '') {
            return '';
        } else {
            $ret = array('sql' => ' WHERE ' . $this->_sql['where'], 'data' => $this->_sql['whereData']);
            return $ret;
        }
    }

    public function limit($limit) {
        $this->_sql['limit'] = $limit;
        return $this;
    }

    public function order($order) {
        $this->_sql['order'] = $order;
        return $this;
    }

    /**
     * run query
     * @param type $sql
     * @return type
     */
    public function query($sql) {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);

        $subSql = strtolower(trim(substr(trim($sql), 0, 7)));
        $updateType = array('insert' => true, 'update' => true, 'delete' => true, 'replace' => true);
        $queryType = isset($updateType[$subSql]) ? 'update' : 'select';

        $this->_lastQuery = $sql;
        $dt = $this->_getPdoByMethodName($queryType)->query($sql);

        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));
        return $dt;
    }

    public function queryFetch($sql, $data = array(), $forceMaster = false) {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $this->_lastQuery = array($sql, $data);
        if ($forceMaster) {
            $this->setDbMaster();
        }
        $sth = $this->_getPdoByMethodName(__FUNCTION__)->prepare($sql);
        $res = $sth->execute($data);
        if (!$res) {
            $this->_raiseError('select query failed~', exception_mysqlpdo::TYPE_QUERY_ERROR);
        }
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
        $this->_clearStat();
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }

    /**
     * get last query sql
     * @return type
     */
    public function getLastQuery() {
        return $this->_lastQuery;
    }

    public static function magicInstance($dbAlias, $tablename) {
        return self::instance($dbAlias)->table($tablename);
    }

}
