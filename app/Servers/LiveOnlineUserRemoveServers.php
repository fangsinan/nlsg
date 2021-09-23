<?php

namespace App\Servers;

use App\Models\LiveOnlineUser;

class LiveOnlineUserRemoveServers implements \Iterator
{
    protected $index;
    protected $max_index;

    public function __construct($begin_id = 0, $end_id = 1)
    {
        $this->index = $begin_id;
        $this->max_index = $end_id;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return $this->index <= $this->max_index;
    }

    public function current()
    {
        $id = $this->index;
        $res = LiveOnlineUser::find($id);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    public function next()
    {
        return $this->index++;
    }

    public function key()
    {
        return $this->index;
    }
}
