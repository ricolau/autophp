<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2017-06-28
 * @desc orm, need PDO extension !
 *           
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
    protected $_lastQuery = null;
    
    protected $_queryObj = null;

    protected static $_tableStructure = array();
    
    protected static $_reentrantErrorTimes = array();
    protected static $_reentrantErrorTimesLimit = 5;
    protected static $_reentrantErrorStartTime = array();
    
    protected $_forceDbReconnect = false;

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
    
  
    
    protected function _exceptionHandle($func, $args, $e){
        //设置重入次数上限,防止程序陷入死循环重入崩溃
        $seqid = md5($func . serialize($args));
        if(isset(self::$_reentrantErrorTimes[$seqid]) && self::$_reentrantErrorTimes[$seqid] >= self::$_reentrantErrorTimesLimit) {
            ($timeCost = microtime(true) - self::$_reentrantErrorStartTime[$seqid]) && performance::add(__METHOD__.'::errorMax', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'exception'=>$e   ));
            self::$_reentrantErrorTimes[$seqid] = null;
            throw $e;
        }
        if(!isset(self::$_reentrantErrorTimes[$seqid])) {
            self::$_reentrantErrorStartTime[$seqid] = microtime(true);
            self::$_reentrantErrorTimes[$seqid] = 0;
        }
        self::$_reentrantErrorTimes[$seqid] += 1;

        $ptx = new plugin_context(__METHOD__.'::error', array('alias' => $this->_dbAlias, 'env'=>auto::getMode(),'exception' => $e, 'obj' => $this, 'func'=>$func,'args'=>$args));
        plugin::call(__METHOD__ . '::error', $ptx);
        if($ptx->breakOut !== null) {
            return $ptx->breakOut;
        }
        self::$_reentrantErrorTimes[$seqid] = null;
        ($timeCost = microtime(true) - self::$_reentrantErrorStartTime[$seqid]) && performance::add(__METHOD__.'::errorAbort', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'exception'=>$e   ));
        throw $e;
    }
   
   
    
    public function forceDbReconnect(){
        $this->_forceDbReconnect = true;
        return $this;
    }
    

    
    
    private function _clearStat() {
        $this->_sql = array();
        $this->_dangerCheck = true;
        //$this->_dbObjMode = 'auto';
        $this->_forceDbReconnect = false;
    }

    /**
     * @usage
     *    after   orm::instance($dbAlias)->getPdo(db::server_type_master);  to get your pdo object
     *    this equals to   new db_mysqlpdo($alias, $conf)->connect(db::server_type_master);
     * @param type $type
     * @return type
     */
    public function getPdo($type = null, $forceNewConnection = false) {
        if ($type === null) {
            $type = ($this->_dbObjMode !== self::db_type_master) ? self::db_type_slave : self::db_type_master;
        }
        if ($type != self::db_type_master) {
            return (isset($this->_dbObj[self::db_type_slave]) && !$forceNewConnection) ? $this->_dbObj[self::db_type_slave] :
                    ($this->_dbObj[self::db_type_slave] = $this->_getPdoServerWithAlias($this->_dbAlias, self::db_type_slave, $forceNewConnection));
        } else {
            return (isset($this->_dbObj[self::db_type_master]) && !$forceNewConnection) ? $this->_dbObj[self::db_type_master] :
                    ($this->_dbObj[self::db_type_master] = $this->_getPdoServerWithAlias($this->_dbAlias, self::db_type_master, $forceNewConnection));
        }
    }
    
    protected function _getPdoServerWithAlias($alias, $type = self::db_type_slave, $forceNewConnection = false) {
        $dataDriver = db::instance($alias, $type, $forceNewConnection);
        return $dataDriver;
    }

    public function setPdo($pdoObject, $type = self::db_type_slave) {
        $this->_dbObj[$type] = $pdoObject;
        return $this;
    }

    protected function _getPdoByMethodName($operationType = null) {
        if ($this->_dbObjMode !== self::db_type_auto) {
            return $this->getPdo($this->_dbObjMode,$this->_forceDbReconnect);
        }
        if (in_array($operationType, array('insert', 'update', 'delete'))) {
            return $this->getPdo(self::db_type_master, $this->_forceDbReconnect);
        } else {
            return $this->getPdo(self::db_type_slave, $this->_forceDbReconnect);
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
    
    protected static function _getErrorInfo($obj){
        if(!is_object($obj)){
            return '';
        }
        $info = $obj->errorInfo();
        if(is_array($info)){
            $ret = implode('|', $info);
        }else{
            $ret = (string)$info;
        }
        $ret = get_class($obj).'|'.$ret;
        return $ret;
        
    }

    /**
     * 
     * @param array/struct $data
     * @param bool $getLastInsertId
     * @return bool/int
     */
    public function insert($data, $getLastInsertId = false) {
        try{
            $_debugMicrotime = microtime(true);
            if (empty($data)) {
                $this->_raiseError('insert query data empty~', exception_mysqlpdo::type_input_data_error);
            }
            $fields = $values = array();
            foreach($data as $k=>$v){
                $fields[] = $k;
                $values[] = $v;
            }
            if (empty($values)) {
                $this->_raiseError('insert query data empty 2!', exception_mysqlpdo::type_input_data_error);
            }

            $insteads = array_fill(0, count($values), '?');

            $sql = 'INSERT INTO ' . $this->_table . '(`' . implode('`, `', $fields) . '`) VALUE(' . implode(',', $insteads) . ')';
            $this->_lastQuery = array($sql, $values);

            $this->_queryObj = $this->_getPdoByMethodName(__FUNCTION__);
            $sth = $this->_queryObj->prepare($sql);
            if($sth===false){
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__)));
                $this->_raiseError('prepare failed for '.__METHOD__.', ['.self::_getErrorInfo($this->_queryObj).']', exception_mysqlpdo::type_query_error);
            }
            $res = $sth->execute($values);

            $this->_clearStat();
            if ($res === false) {
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__))) ;
                $this->_raiseError('insert query failed for '.__METHOD__.', ['.self::_getErrorInfo($sth).']', exception_mysqlpdo::type_query_error);
            }
            if ($getLastInsertId) {
                $lastInsertId = $this->_queryObj->lastInsertId();
                //有时候table 可能没有primary key
                if ($lastInsertId) {
                    ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'lastQuery'=>$this->_lastQuery,'ret'=>  $lastInsertId));
                    return $lastInsertId;
                }
            }
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__))) ;
            return $res;
        }catch(Exception $e){
            return $this->_exceptionHandle(__FUNCTION__, func_get_args(), $e);
        }
    }

    /**
     * 
     * @param array/struct $data
     * @param bool $returnAffectedRows
     * @return bool/int
     */
    public function autoUpdate($data, $returnAffectedRows = false) {
        
        $structure = $this->structure();
        if (!is_array($structure)) {
            $this->_raiseError('get data structure empty', exception_mysqlpdo::type_input_data_error);
        }
        $dt = array();
        foreach ($data as $k => $v) {
            if (in_array($k, $structure)){
                $dt[$k] = $v;
            }
        }
        $ret = $this->update($dt,$returnAffectedRows);
        return $ret;
    }
    /**
     * 
     * @param array/struct $data
     * @param bool $getLastInsertId
     * @return type
     */
    public function autoInsert($data, $getLastInsertId = false) {
        $structure = $this->structure();
        if (!is_array($structure)) {
            $this->_raiseError('get data structure failed', exception_mysqlpdo::type_input_data_error);
        }
        $dt = array();
        foreach ($data as $k => $v) {
            if (in_array($k, $structure)){
                $dt[$k] = $v;
            }
        }
        $ret = $this->insert($dt, $getLastInsertId);
        return $ret;
    }

    /**
     * 
     * @param array/struct $data
     * @param bool $returnAffectedRows
     * @return bool/int
     */
    public function update($data, $returnAffectedRows = false) {
        try{
            $_debugMicrotime = microtime(true);
            $this->_checkDanger(__FUNCTION__);
            if (empty($data)) {
                $this->_raiseError('empty data for update function query', exception_mysqlpdo::type_input_data_error);
            }
            $fields = $values = array();
            foreach($data as $k=>$v){//support array and struct
                $fields[] = $k . '= ? ';
                $values[] = $v;
            }
            if (empty($values)) {
                $this->_raiseError('empty data for update function query', exception_mysqlpdo::type_input_data_error);
            }
            $where = $this->_getWhere();
            $sql = 'UPDATE ' . $this->_table . ' SET ' . implode(',', $fields) . $where['sql'];
            $sqlData = $where['data'];
            if (is_array($sqlData)) {
                $values = util::array_merge($values, $sqlData);
            }

            $this->_lastQuery = array($sql, $values);
            $this->_queryObj = $this->_getPdoByMethodName(__FUNCTION__);
            $sth = $this->_queryObj->prepare($sql);
            if($sth===false){
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__)));

                $this->_raiseError('prepare failed for '.__METHOD__.', ['.self::_getErrorInfo($this->_queryObj).']', exception_mysqlpdo::type_query_error);
            }
            $ret = $sth->execute($values);
            if ($ret === false) {
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($ret,__METHOD__)));

                $this->_raiseError('update query failed for '.__METHOD__.', ['.self::_getErrorInfo($sth).']', exception_mysqlpdo::type_query_error);
            }
            if ($returnAffectedRows) {
                $ret = $sth->rowCount();
            }
            $this->_clearStat();
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($ret,__METHOD__))) ;
            return $ret;
        }catch(Exception $e){
            return $this->_exceptionHandle(__FUNCTION__, func_get_args(), $e);
        }
    }

    public function delete() {
        try{
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

            $this->_queryObj = $this->_getPdoByMethodName(__FUNCTION__);
            $sth = $this->_queryObj->prepare($sql);
            if($sth===false){
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__)));

                $this->_raiseError('prepare failed for '.__METHOD__.', ['.self::_getErrorInfo($this->_queryObj).']', exception_mysqlpdo::type_query_error);
            }
            $res = $sth->execute($values);
            if ($res === false) {
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__))) ;

                $this->_raiseError('execute failed for '.__METHOD__.', ['.self::_getErrorInfo($sth).']', exception_mysqlpdo::type_query_error);
            }

            $this->_clearStat();
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__)));
            return $res;
        }catch(Exception $e){
            return $this->_exceptionHandle(__FUNCTION__, func_get_args(), $e);
        }
    }

    /**
     * 
     * @param array/string $fields
     * @return $this
     */
    public function fields($fields = array()) {
        $this->_sql['fields'] = $fields;
        return $this;
    }
    public function selectObject() {
        return $this->_formatObject($this->select());
    }

    protected function _formatObject($res){
        if(!is_array($res) || empty($res)){
            return $res;
        }
        $st = array();
        foreach($res[0] as $k=>$v){
            $vtype = gettype($v);
            $st[$k] = struct::typeExist($vtype) ? $vtype : struct::type_string;
        }
        $dt = array();
        foreach($res as $k=>$v){
            $tmp = new struct($st, false);
            foreach($v as $kk=>$vv){
                $tmp->$kk = $vv;
            }
            $dt[] = $tmp;
            unset($res[$k]);
        }
        //$res = $dt;
        
        return $dt;
    }
    
    public function select() {
        try{
            $_debugMicrotime = microtime(true);
            $where = $this->_getWhere();
            $sql = isset($where['sql']) ? $where['sql'] : '';
            $sqlData = isset($where['data']) ? $where['data'] : array();
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
                $fields = is_array($this->_sql['fields']) ? implode(',', $this->_sql['fields']) : $fields;
            } else {
                $fields = '*';
            }
            $sql = 'SELECT ' . $fields . ' FROM ' . $this->_table . $sql;
            $this->_lastQuery = array($sql, $values);

            $this->_queryObj = $this->_getPdoByMethodName(__FUNCTION__);
            $sth = $this->_queryObj->prepare($sql);
            if($sth===false){
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__)));

                $this->_raiseError('prepare failed for '.__METHOD__.', ['.self::_getErrorInfo($this->_queryObj).']', exception_mysqlpdo::type_query_error);
            }
            $res = $sth->execute($values);
            if ($res === false) {
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__)));
                $this->_raiseError('execute failed for '.__METHOD__.', ['.self::_getErrorInfo($sth).']', exception_mysqlpdo::type_query_error);
            }
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);


            $this->_clearStat();
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__)) ) ;
            return $res;
        }catch(Exception $e){
            return $this->_exceptionHandle(__FUNCTION__, func_get_args(), $e);
        }
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
        try{
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
            $this->_queryObj = $this->_getPdoByMethodName(__FUNCTION__);
            $sth = $this->_queryObj->prepare($sql);
            if($sth===false){
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__))) ;
                $this->_raiseError('prepare failed for '.__METHOD__.', ['.self::_getErrorInfo($this->_queryObj).']', exception_mysqlpdo::type_query_error);
            }
            $res = $sth->execute($values);
            if ($res === false) {
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__))) ;
                $this->_raiseError('execute query failed for '.__METHOD__.', ['.self::_getErrorInfo($sth).']', exception_mysqlpdo::type_query_error);
            }
            $res = $sth->fetchColumn();
            $this->_clearStat();
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__))) ;

            return $res;
        }catch(Exception $e){
            return $this->_exceptionHandle(__FUNCTION__, func_get_args(), $e);
        }
    }

    protected function _raiseError($msg, $code = -2) {

        throw new exception_mysqlpdo('mysql ' . $msg . ' || ' . json_encode($this->getLastQuery()), $code);
    }


    public function where($where, $data = null) {
        $this->_sql['where'] = $where;
        $this->_sql['whereData'] = $data;
        return $this;
    }

    /**
     * 
     * @param array $match as  array('name'=>'rico', 'age'=>20)
     * @param array $in as  array('id'=>array(12,5,68,58), 'name'=>array('jim','tom'))
     * @param array $notIn as array('id'=>array(1,2,3,4), 'name'=>array('lily','tom'))
     * @param array $like as  array('name'=>'rico%', 'address'=>'haidian area%')
     * @param array $between as  array('age'=>array(12, 23), 'time'=>array(1478034707,1478054707))
     * @return \orm
     */
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
                    $this->_raiseError('sql param error for "in" clause of '.__METHOD__.', [value of '.$k.' is not an array]', exception_mysqlpdo::type_input_data_error);
                    break;
                }
                $insteads = array_fill(0, count($v), '?');
                $sql .= ' AND '.$k.' IN( '.implode(',',$insteads).') ';
                $whereData = util::array_merge($whereData, $v);
            }
        }
        if($notIn && is_array($notIn)){
            foreach($notIn as $k=>$v){
                if(!is_array($v)){
                    $this->_raiseError('sql param error for "notIn" clause of '.__METHOD__.', [value of '.$k.' is not an array]', exception_mysqlpdo::type_input_data_error);
                    break;
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

    /**
     * 
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit, $offset = null) {
        if($offset===null){
            $this->_sql['limit'] = $limit;
        }else{
            $this->_sql['limit'] = $limit.','.$offset;
        }
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
    public function query($sql, $data, $returnAffectedRows = false) {
        try{
            $_debugMicrotime = microtime(true);

            $subSql = strtolower(trim(substr(trim($sql), 0, 7)));
            $updateType = array('insert' => true, 'update' => true, 'delete' => true, 'replace' => true);
            $queryType = isset($updateType[$subSql]) ? 'update' : 'select';

            $this->_lastQuery = array($sql,$data);

            $this->_queryObj = $this->_getPdoByMethodName($queryType);
            $sth = $this->_queryObj->prepare($sql);
            if($sth===false){
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__)));
                $this->_raiseError('prepare failed for '.__METHOD__, exception_mysqlpdo::type_query_error);
            }
            $res = $sth->execute($data);
            if($res && $returnAffectedRows){
                $res = $sth->rowCount();
            }

            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__)));
            return $res;
        }catch(Exception $e){
            return $this->_exceptionHandle(__FUNCTION__, func_get_args(), $e);
        }
    }

    public function queryFetchObject($sql,$data = array(), $forceMaster = false){
        return $this->_formatObject($this->queryFetch($sql, $data, $forceMaster));
    }
    
    public function queryFetch($sql, $data = array(), $forceMaster = false) {
        $_debugMicrotime = microtime(true);
        $this->_lastQuery = array($sql, $data);
        if ($forceMaster) {
            $this->setDbMaster();
        }
        $this->_queryObj = $this->_getPdoByMethodName(__FUNCTION__);
        $sth = $this->_queryObj->prepare($sql);
        if($sth===false){
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__)));
            $this->_raiseError('prepare failed for '.__METHOD__, exception_mysqlpdo::type_query_error);
        }
        $res = $sth->execute($data);
        if (!$res) {
            $this->_raiseError('select query failed~', exception_mysqlpdo::type_query_error);
        }
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        $this->_clearStat();
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__)));
        return $res;
    }

    /**
     * get table structure
     * @param type $fullType
     * @return type
     */
    public function structure($fullType = false) {
        try{
            $fullType  = intval($fullType);
            $_debugMicrotime = microtime(true);
            if (isset(self::$_tableStructure[$this->_dbAlias][$this->_table][$fullType])) {
                return self::$_tableStructure[$this->_dbAlias][$this->_table][$fullType];
            }
            $sql = "DESC " . $this->_table;
            $this->_lastQuery = $sql;
            $this->_queryObj = $this->_getPdoByMethodName(__FUNCTION__);
            $sth = $this->_queryObj->prepare($sql);
            if($sth===false){
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($sth,__METHOD__))) ;
                $this->_raiseError('prepare failed for '.__METHOD__, exception_mysqlpdo::type_query_error);
            }
            $res = $sth->execute(array());
            if (!$res) {
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_dbAlias,'line'=>__LINE__,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($res,__METHOD__))) ;
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


            self::$_tableStructure[$this->_dbAlias][$this->_table][$fullType] = $dt;
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_dbAlias,'lastQuery'=>$this->_lastQuery,'ret'=>  performance::summarize($dt,__METHOD__)))  ;
            return $dt;
        }catch(Exception $e){
            return $this->_exceptionHandle(__FUNCTION__, func_get_args(), $e);
        }
    }

    /**
     * get last query sql
     * @return type
     */
    public function getLastQuery() {
        return $this->_lastQuery;
    }
    
    /**
     * get last query pdo object
     * @return type
     */
    public function getLastQueryPdo(){
        return $this->_queryObj;
    }

    public static function magicInstance($dbAlias, $tablename) {
        return self::instance($dbAlias)->table($tablename);
    }

}
