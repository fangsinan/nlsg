<?php


namespace App\Models\Message;


use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageType extends Base
{
    const DB_TABLE = 'nlsg_message_type';

    protected $table = 'nlsg_message_type';

    protected $fillable = [
        'title', 'created_at', 'updated_at',
    ];

    public function childList(): HasMany
    {
        return $this->hasMany(self::class, 'pid', 'id');
    }

    //校验id是否可以用于创建模板
    public function checkUsableById(int $id = 0): array
    {
        if (empty($id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        $check = self::query()->where('pid', '=', $id)->first();
        if ($check) {
            return ['code' => false, 'msg' => '该类型不能直接用于创建模板'];
        }

        return ['code' => true, 'msg' => 'ok'];
    }
}
