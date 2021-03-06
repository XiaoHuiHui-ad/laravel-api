<?php
/**
 * Created by PhpStorm.
 * User: chen
 * Date: 2018/11/25
 * Time: 23:07
 */

namespace App\Http\Controllers\V1\Admin\Article;

use App\HelpTrait\BroadcastHttpPush;
use App\Http\Controllers\Controller;
use App\Logic\V1\Admin\Article\ArticleLogic;
use Orzcc\TopClient\Facades\TopClient;
use TopClient\request\TbkJuTqgGetRequest;

class ArticleController extends Controller
{
    use BroadcastHttpPush;
    /**
     * @return string
     * @throws \DdvPhp\DdvRestfulApi\Exception\RJsonError
     * @throws \ReflectionException
     */
    public function index(){
        $this->validate(null, [
            'uid' => 'integer'
        ]);
        $articleLogic = new ArticleLogic();
        $articleLogic->load($this->verifyData);
        return  $articleLogic->lists();
    }

    /**
     * @return array
     * @throws \App\Logic\Exception
     * @throws \DdvPhp\DdvRestfulApi\Exception\RJsonError
     * @throws \ReflectionException
     */
    public function store(){
        $this->validate(null, [
            'uid' => 'required|integer',
            'title' => 'required|string',
            'price' => 'required|string',
            'cover' => 'required|string',
            'tagIds' => 'array',
            'address' => 'required|string',
            'openTime' => 'required|string',
            'categoryId' => 'required|string',
            'description' => 'required|string',
            'reason' => 'required|string',
        ]);
        $articleLogic = new ArticleLogic();
        $articleLogic->load($this->verifyData);
        if ($articleLogic->store()){
            return[];
        }
    }

    /**
     * @return array
     * @throws \App\Logic\Exception
     * @throws \DdvPhp\DdvRestfulApi\Exception\RJsonError
     * @throws \ReflectionException
     */
    public function update($articleId){
        $this->validate(['articleId' => $articleId], [
            'articleId' => 'required|integer',
            'uid' => 'required|integer',
            'title' => 'required|string',
            'price' => 'required|string',
            'address' => 'required|string',
            'openTime' => 'required|string',
            'reason' => 'required|string',
        ]);
        $articleLogic = new ArticleLogic();
        $articleLogic->load($this->verifyData);
        if ($articleLogic->update()){
            return[];
        }
    }
    
    /**
     * @param $articleId
     * @return array
     * @throws \App\Logic\Exception
     * @throws \DdvPhp\DdvRestfulApi\Exception\RJsonError
     * @throws \ReflectionException
     */
    public function show($articleId){
        $this->validate(['articleId' => $articleId], [
            'articleId' => 'required|integer'
        ]);
        $articleLogic = new ArticleLogic();
        $articleLogic->load($this->verifyData);
        return[
            'data' => $articleLogic->show()
        ];
    }

    /**
     * @return array
     * @throws \App\Logic\Exception
     * @throws \DdvPhp\DdvRestfulApi\Exception\RJsonError
     * @throws \ReflectionException
     */
    public function destroy($articleId){
        $this->validate(['articleId' => $articleId], [
            'articleId' => 'required|integer'
        ]);
        $articleLogic = new ArticleLogic();
        $articleLogic->load($this->verifyData);
        if ($articleLogic->destroy()){
            return[];
        }
    }

    /**
     * @return array
     * @throws \DdvPhp\DdvRestfulApi\Exception\RJsonError
     * @throws \ReflectionException
     */
    public function review(){
        $this->validate(null, [
            'articleIds' => 'required|array',
            'status' => 'required|integer'
        ]);
        $articleLogic = new ArticleLogic();
        $articleLogic->load($this->verifyData);
        if ($articleLogic->review()){
            return[];
        }
    }
}