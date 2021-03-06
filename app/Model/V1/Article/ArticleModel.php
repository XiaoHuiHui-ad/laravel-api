<?php
/**
 * Created by PhpStorm.
 * User: chen
 * Date: 2018/11/25
 * Time: 23:11
 */

namespace App\Model\V1\Article;

use App\Model\Model;
use App\Model\V1\User\UserBaseModel;

class ArticleModel extends Model
{
    protected $table = 'article';

    protected $primaryKey = 'article_id';

    const STATUS_PASS = 1;

    public function hasOneUserBaseModel(){
        return $this->hasOne(UserBaseModel::class,'uid','uid');
    }
}