<?php

include dirname(__FILE__).'/loader.php';

class daemon_demo extends dameon{
    
    public function _init(){
        
    }
    
    public function main(){
        try{
            echo "\r\n <br /> hello, i'm indexAction in Controller_Index, nice 2 meet u! <br />\r\n ";

            echo "\r\n <br /> now set language to zh-cn! <br />\r\n ";
            i18n::setLanguage('en-us');

            echo "\r\n <br /> in english, my name is: ".i18n::get('author')." <br />\r\n ";

            echo "\r\n <br /> now set language to zh-cn! <br />\r\n ";
            i18n::setLanguage('zh-cn');
            echo "\r\n <br /> in chinese, my name is: ".i18n::get('author')." <br />\r\n ";

            $mDemo = new Model_Demo();
            $uin = 10000;
            $uinfo = $mDemo->getUserInfo($uin);
            $uinfo2 = $mDemo->getUserInfo2($uin);

            $update = $mDemo->updateInfo(6, 1);
            var_dump($uinfo,$uinfo2,$update);


        } catch (Exception $e) {
            exception_handler::topDeal($e);
        }
       

    }    
}


$demo = new daemon_demo();
$demo->main();