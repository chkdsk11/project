<?php

/**
 * Created by PhpStorm.
 * User: codecooker
 * Date: 16/7/13
 * Time: 下午6:39
 */
namespace Shop\Libs;
use Shop\Libs\LibraryBase;

class Lock extends LibraryBase
{
    /**
     * 加锁
     * @param string $name 锁名称
     * @return bool
     */
    public function setLock($name)
    {
        $this->cache->selectDb(14);
        return $this->cache->setValue('redisRlock_' . $name,1,60*2);
    }

    /**
     * 获取锁
     * @param string $name 锁名称
     * @return string
     */
    public function getLock($name)
    {
        $this->cache->selectDb(14);
        return $this->cache->getValue('redisRlock_' . $name);
    }
    /**
     * 解除锁
     * @param string $name 锁名称
     * @return bool
     */
    public function unLock($name)
    {
        $this->cache->selectDb(14);
        return $this->cache->delete('redisRlock_' . $name);
    }
}