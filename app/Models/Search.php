<?php


namespace App\Models;


class Search extends Base
{
    protected $table = 'nlsg_search';

    protected $fillable = ['keywords' , 'user_id' , 'num'];

}