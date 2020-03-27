<?php
/**
 * User: gedongdong
 * Date: 2020-03-18 22:01
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorites extends Model
{
    use SoftDeletes;

    protected $table = 'favorites';

    protected $guarded = [];
}