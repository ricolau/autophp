<?php




class plugin_init extends plugin_abstract{
    
    
    
    public function call($tag, plugin_context &$ptx){
        $ptx->breakOut = 'hahahaha';
        echo "\r\n <br /> hello, im plugin wbinit before your action run! \r\n <br /> ";
    }
    
    
}