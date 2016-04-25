<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-03-29
 * @desc orm, must be based on pdo!
 *
 */
class orm extends base {

    const db_type_slave = 'slave';
    const db_type_master = 'master';
    const db_type_auto = 'auto';

    protected $_dbAlias = null;
    protected $_table = null;
    protected $_dbObj = array();
    protected $_dbObjMode = self::db_type_auto;
    // auto | master | slave
    protected $_currentDbCon = null;
    protected $_sql = null;
    protected $_dangerCheck = null;
    protected $_pages = null;
    protected $_lastQuery = null;
    protected static $_tableStructure = array();

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
     *      after                           orm::instance($dbAlias)->getPdo(db_mysqlpdo::TYPE_SERVER_MASTER);  to get your pdo object
     *          this equals to              new db_mysqlpdo($alias, $conf)->connectSlave();
     * @param type $type
     * @return type
     */
    public function getPdo($type = null) {
        if ($type === null) {
            $type = ($this->_dbObjMode !== self::db_type_master) ? self::db_type_slave : self::db_type_master;
        }
        if ($type != self::db_type_master) {
            return isset($this->_dbObj[self::db_type_slave]) ? $this->_dbObj[self::db_type_slave] :
                    ($this->_dbObj[self::db_type_slave] = $this->_getPdoServerWithAlias($this->_dbAlias, self::db_type_slave));
        } else {
            return isset($this->_dbObj[self::db_type_master]) ? $this->_dbObj[self::db_type_master] :
                    ($this->_dbObj[self::db_type_master] = $this->_getPdoServerWithAlias($this->_dbAlias, self::db_type_master));
        }
    }

    protected function _getPdoServerWithAlias($alias, $type = self::db_type_slave) {
        $dataDriver = db::instance($alias, $type);
        return $dataDriver;
    }

    public function setPdo($pdoObject, $type = self::db_type_slave) {
        $this->_dbObj[$type] = $pdoObject;
        return $this;
    }

    protected function _getPdoByMethodName($operationType = null) {
        if ($this->_dbObjMode !== self::db_type_auto) {
            return $this->getPdo($this->_dbObjMode);
        }
        if (in_array($operationType, array('insert', 'update', 'delete'))) {
            return $this->getPdo(self::db_type_master);
        } else {
            return $this->getPdo(self::db_type_slave);
        }
    }

    /**
     * set database mode to master
     */
    public function setDbMaster() {
        $this->_dbObjMode = self::db_type_master;
        return $this;
    }

    /**
     * set database mode slave
     */
    public function setDbSlave() {
        $this->_dbObjMode = self::db_type_slave;
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
            $this->_raiseError('unsafe mode checked for method: ' . $method, exception_mysqlpdo::type_high_risk_query);
        }
    }

    public function insert($data, $getLastInsertId = false) {
        $_debugMicrotime = microtime(true);
        if (empty($data) || !is_array($data)) {
            $this->_raiseError('insert query data empty~', exception_mysqlpdo::type_input_data_error);
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
        if ($res === false) {
            
            ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
            $this->_raiseError('insert query failed~', exception_mysqlpdo::type_query_error);
        }
        if ($getLastInsertId) {
            $lastInsertId = $db->lastInsertId();
            //有时候table 可能没有primary key
            if ($lastInsertId) {
                ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
                return $lastInsertId;
            }
        }
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }

    public function autoUpdate($data) {
        $structure = $this->structure();
        if (!is_array($structure)) {
            $this->_raiseError('get data structure failed', exception_mysqlpdo::type_input_data_error);
        }
        foreach ($data as $k => $v) {
            if (!in_array($k, $structure))
                unset($data[$k]);
        }
        $ret = $this->update($data);
        return $ret;
    }

    public function autoInsert($data, $getLastInsertId = false) {
        $structure = $this->structure();
        if (!is_array($structure)) {
            $this->_raiseError('get data structure failed', exception_mysqlpdo::type_input_data_error);
        }
        foreach ($data as $k => $v) {
            if (!in_array($k, $structure))
                unset($data[$k]);
        }
        $ret = $this->insert($data, $getLastInsertId);
        return $ret;
    }

    public function update($data, $returnAffectedRows = false) {
        $_debugMicrotime = microtime(true);
        $this->_checkDanger(__FUNCTION__);
        if (empty($data) || !is_array($data)) {
            $this->_raiseError('empty data for update function query', exception_mysqlpdo::type_input_data_error);
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
        $ret = $sth->execute($values);
        if ($ret === false) {
            $this->_raiseError('update query failed~', exception_mysqlpdo::type_query_error);
        }
        if ($returnAffectedRows) {
            $ret = $sth->rowCount();
        }
        $this->_clearStat();
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
        return $ret;
    }

    public function delete() {
        $_debugMicrotime = microtime(true);
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
        if ($res === false) {
            $this->_raiseError('delete query failed~', exception_mysqlpdo::type_query_error);
        }

        $this->_clearStat();
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }

    public function fields($fields = array()) {
        $this->_sql['fields'] = $fields;
        return $this;
    }

    public function select() {
        $_debugMicrotime = microtime(true);
        $where = $this->_getWhere();
        $sql = $where['sql'];
        $sqlData = $where['data'];
        $values = array();
        if (is_array($sqlData)) {
            $values = util::array_merge($values, $sqlData);
        }
        if (isset($this->_sql['groupby'])) {
            $sql .= ' GROUP BY ' . $this->_sql['groupby'];
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
        if ($res === false) {
            $this->_raiseError('select query failed~', exception_mysqlpdo::type_query_error);
        }
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);

        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));

        $this->_clearStat();

        return $res;
    }

    public function selectOne() {
        return $this->getOne();
    }

    public function getOne() {
        $data = $this->limit(1)->select();
        if (is_array($data)) {
            $data = array_shift($data);
        }
        return $data;
    }

    public function count($key = '') {
        $_debugMicrotime = microtime(true);
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
        if ($res === false) {
            $this->_raiseError('count query failed~', exception_mysqlpdo::type_query_error);
        }
        $count = $sth->fetchColumn();
        $this->_clearStat();
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));

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

    public function whereMatch($match = array(), $in = array(), $notIn = array(), $like=array(), $between = array()) {
        /**
         * where a=1 and b=2 and c in (123,34,3,5) and b not in (3,2,4) and x like '435%' and y between y1 and y2,     group by a order by b desc limit 20
         * *
         * array('match'=>array(), 'in'=>array(), 'like'=>array() )
         * 
         * @return string
         */
        $sql = '1';
        $whereData = array();
        if($match && is_array($match)){
            foreach($match as $k=>$v){
                $sql .= ' AND '.$k.' = ? ';
                $whereData[] = $v;
            }
        }
        if($in && is_array($in)){
            foreach($in as $k=>$v){
                if(!is_array($v)){
                    continue;
                }
                $insteads = array_fill(0, count($v), '?');
                $sql .= ' AND '.$k.' IN( '.implode(',',$insteads).') ';
                $whereData = util::array_merge($whereData, $v);
            }
        }
        if($notIn && is_array($notIn)){
            foreach($notIn as $k=>$v){
                if(!is_array($v)){
                    continue;
                }
                $insteads = array_fill(0, count($v), '?');
                $sql .= ' AND '.$k.' NOT IN( '.implode(',',$insteads).') ';
                $whereData = util::array_merge($whereData, $v);
            }
        }
        if($like && is_array($like)){
            foreach($like as $k=>$v){
                $sql .= ' AND '.$k.' LIKE ?  ';
                $whereData[] = $v;
            }
        }
        
        if($between && is_array($between)){
            foreach($between as $k=>$v){
                $sql .= ' AND ( '.$k.' BETWEEN ? AND ? )';
                $whereData[] = $v[0];
                $whereData[] = $v[1];
            }
        }
        $this->_sql['where'] = $sql;
        $this->_sql['whereData'] = $whereData;
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

    public function groupby($groupby) {
        $this->_sql['groupby'] = $groupby;
        return $this;
    }

    /**
     * run query
     * @param type $sql
     * @return type
     */
    public function query($sql, $data) {
        $_debugMicrotime = microtime(true);

        $subSql = strtolower(trim(substr(trim($sql), 0, 7)));
        $updateType = array('insert' => true, 'update' => true, 'delete' => true, 'replace' => true);
        $queryType = isset($updateType[$subSql]) ? 'update' : 'select';

        $this->_lastQuery = $sql;

        $db = $this->_getPdoByMethodName($queryType);
        $sth = $db->prepare($sql);
        $res = $sth->execute($data);

        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }
    
    public function queryFetch($sql, $data = array(), $forceMaster = false) {
        $_debugMicrotime = microtime(true);
        $this->_lastQuery = array($sql, $data);
        if ($forceMaster) {
            $this->setDbMaster();
        }
        $sth = $this->_getPdoByMethodName(__FUNCTION__)->prepare($sql);
        $res = $sth->execute($data);
        if (!$res) {
            $this->_raiseError('select query failed~', exception_mysqlpdo::type_query_error);
        }
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
        $this->_clearStat();
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
        return $res;
    }

    public function structure($fullType = false) {
        $_debugMicrotime = microtime(true);
        if (isset(self::$_tableStructure[$this->_dbAlias][$this->_table])) {
            return self::$_tableStructure[$this->_dbAlias][$this->_table];
        }
        $sql = "DESC " . $this->_table;
        $this->_lastQuery = $sql;
        $sth = $this->_getPdoByMethodName()->prepare($sql);
        $res = $sth->execute(array());
        if (!$res) {
            $this->_raiseError('select query failed~', exception_mysqlpdo::type_query_error);
        }
        $dt = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (!$fullType && is_array($dt)) {
            $fields = array();
            foreach ($dt as $v) {
                $fields[] = $v['Field'];
            }
            $dt = $fields;
        }


        self::$_tableStructure[$this->_dbAlias][$this->_table] = $dt;
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, $this->_lastQuery)  && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's of query: ' . var_export($this->_lastQuery, true));
        return $dt;
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
