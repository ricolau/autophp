<?php


class controller_index extends controller{
    
    
    public function indexAction(){
        
        try{
            echo "\r\n <br /> hello, i'm indexAction in controller_index, nice 2 meet u! <br />\r\n ";

            echo "\r\n <br /> now set language to zh-cn! <br />\r\n ";
            i18n::language('en-us');

            echo "\r\n <br /> in english, my name is: ".i18n::get('author')." <br />\r\n ";

            echo "\r\n <br /> now set language to zh-cn! <br />\r\n ";
            i18n::language('zh-cn');
            echo "\r\n <br /> in chinese, my name is: ".i18n::get('author')." <br />\r\n ";

            $mDemo = new model_demo();
            $uin = 10000;
            $uinfo = $mDemo->getUserInfo($uin);
            $uinfo2 = $mDemo->getUserInfo2($uin);

            $update = $mDemo->updateInfo(6, 1);
            var_dump($uinfo,$uinfo2,$update);


            $confs = array(
                'title'=>'page title',
                'time'=>date('Ymd H:i:s'),
                'name'=>'ricolau',
            );
            
        }catch (exception_i18n $e){ //i18n 的异常，一般是由于语言包不存在
            $code = $e->getCode();
            if($code == exception_i18n::TYPE_LANGUAGE_NOT_EXIST){
                echo "\r\n <br /> exception: language of ".i18n::language().' not exist!\r\n <br />';
            }
            echo $e->getMessage();
        }catch (Exception $e){//如果实在还是有exception，那就捕捉到这里吧，ignore 忽略处理的话。代码继续执行 $this->render()，不会白页。
           
            //throw $e;  //如果把这个异常抛上去，当前页面就木有了，上层接收到的话自己处理就好了，比如报个异常啥的
            
            $code = $e->getCode();
            echo $e->getMessage();
            
            
        }
        
        
        //一般来说 render 这块儿可以不用 try 和 cache，只有 template 或 slot 不存在才会有异常而已。
        //但是建议和上面部分业务代码的try cache 结构分离，从而可以更好的决定，如果业务数据有问题，页面是否还继续render()
        try{
             //按变量单独 assign
            $this->assign('uinfo', $uinfo);
            $this->assign('updateresult', $update);

            //批量assign 一个数组可以！
            $this->massign($confs);

            $this->render();
        } catch(exception_render $e){  //一般来说这个exception  不会有，模板放好了就行了么
            $code = $e->getCode();
            if($code == exception_render::TYPE_TPL_NOT_EXIST){
                echo '\r\n <br /> exception: template not exist!\r\n <br />';
            }elseif($code == exception_render::TYPE_SLOT_NOT_EXIST){
                echo '\r\n <br /> exception: template not exist!\r\n <br />';
            }
            echo $e->getMessage();
            
        }
        
        
        
       
        
        // equals to $this->render('index', 'index');
        
        return;//绝对不要用 exit!!!!!
    }
    
    
}