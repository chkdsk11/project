<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/26 0026
 * Time: 下午 3:52
 */

namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class CheckSecurity extends LibraryBase
{
    protected $expireTime=120;

    protected $cookieKey='chk';

    protected $key;

    /**
     *  生成key
     */
    public function setSecurityKey()
    {
        $this->key=md5(microtime().mt_rand(10000,99999));
        $this->cookies->set($this->cookieKey,$this->key,0,'/',false,$this->config->cookie->domain,true);
        return $this->key;
    }

    /**
     * @return string生成值
     */
    public function setSecurityValue()
    {
        $value=substr(str_shuffle('abcdzfsafjwefdskfnasmwerwdghhjkvcxvewexdxcncxbkjafaf122y4o3t42sfhoweurfhsjsdvsv'),0,9);
        $this->cache->selectDb(1);
        $this->cache->setValue($this->key,$value,$this->expireTime);
        return $value;
    }

    /**
     * @return bool
     */
    public function checkSecurity()
    {
        $this->cache->selectDb(1);
        $key=$this->cookies->get($this->cookieKey)->getValue();
        $value=$this->cache->getValue($key);
        if($this->request->isPost()){
            $confirmValue=$this->request->getPost($key,'trim');
            return $confirmValue==$value;
        }elseif($this->request->isGet()){
            $confirmValue=$this->request->get($key,'trim');
            return $confirmValue==$value;
        }elseif($this->request->isAjax()){
            $confirmValue=$this->request->getQuery($key,'trim');
            return $confirmValue==$value;
        }
    }
}