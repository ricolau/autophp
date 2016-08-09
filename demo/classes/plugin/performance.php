<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @vesion 2016-08-05
 * @description 用于将 performance 的数据dump 出来并写入log
 *              performance 里面的数据还是比较全面的,而且建议调用方可以自己到处部署performance 的布点
 * 
 * */
class plugin_performance extends plugin_abstract{
    
    protected static $_samplingUriRateList = array(
        //  uri=>抽样概率
        '/index/user'=>999,
        '/index/view'=>49,
    );
    
    protected static $_performanceLog = '/tmp/logs/http/performance.log';
    
    protected static $_performanceLogCli = '/tmp/logs/cli/performance.log';

    public function call($tag, plugin_context &$ptx){
        /**
         * 判断  http 或者  cli 状态
         * http{
         * 需要注册到事件:  plugin::register( auto::plugin_shutdown, new plugin_performance())
         * 
         * 需要对指定的url performance 抽样,
         * 如果执行时间超过1s,则必须记录log
         * 
         * }
         * 
         * cli{
         * 需要注册到 事件: plugin::register('notice::performance::add', new plugin_performance()), 因为脚本跑的时间可能会很长
         * 需要注册到 事件: plugin::register( auto::plugin_shutdown, new plugin_performance()),  脚本结束运行时,log 拿出来
         * 
         * 记录cli 模式的全部log
         * 
         * }
         * 
         * 
         */
        $data = $this->_getData();
        if($data){
            if(auto::isCli()){
                $this->_writeData(self::$_performanceLogCli, $data);
            }else{
                $this->_writeData(self::$_performanceLog, $data);
            }
            
            if(auto::isOnlineMode()){
                $ptx->breakOut = true;
            }
            
        }
        
    }
    
    protected function _writeData($file,$pf){
        if(!$file || !$pf){
            return false;
            
        }
        $line = '';
                //        $pf[0] = array('time'=>time(),'tag'=>$tag,'timecost'=>$timecost, 'info'=>$info);

        foreach($pf as $v){
            
            $line .= 'time|'.t($v['time']).'||rid|'.REQUEST_ID.'||tag|'.$v['tag'].'||timecost|'.$v['timecost'].'||info|'. json_encode($v['info'])."\n";
        }
        
        file_put_contents($file, $line, FILE_APPEND);
    }
    
    
    
    /**
     * 用于全局操作 performance 的一些数据
     */
    protected function _getData(){
        $doFlush = true;
        if(!auto::isCli()){//http 请求,对某些api 抽样
            
            //对于这些指定的url,按照指定的概率进行抽样记录 performance
            $uri= dispatcher::instance()->getUri();
            if(isset(self::$_samplingUriRateList[$uri]) && rand(0, self::$_samplingUriRateList[$uri])!=0){
                $doFlush =  false;
            }
            
            //任何 runtime 超过1s 的进行 performance 全纪录,
            $runTime = time() - auto::getRuntimeStart();
            if($runTime>=1){
                $doFlush =  true;
            }
        }else{//cli 模式,暂时关闭 log,  or 打开performance  日志
            $doFlush = true;
        }
        
        
        if($doFlush===true){
            return performance::dumpClear();
        }else{
            performance::dumpClear();
            return false;
        }

        
        
    }
   
}
