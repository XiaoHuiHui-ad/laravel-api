<?php
/**
 * Created by PhpStorm.
 * User: chen
 * Date: 2018/12/17
 * Time: 11:18
 */

namespace App\Http\Controllers\V1\Login;
use App\Http\Controllers\Controller;
use App\Logic\V1\Login\RegisterLogic;

class RegisterController extends Controller
{
    /**
     * @return array
     * @throws \DdvPhp\DdvRestfulApi\Exception\RJsonError
     * @throws \ReflectionException
     */
    public function register(){
        $this->validate(null, [
            'account' => 'required|string',
            'password' => 'required|string',
            'headimg' => 'string',
        ]);
        $registerLogic = new RegisterLogic();
        $registerLogic->load($this->verifyData);
        if ($registerLogic->register()){
            return [];
        };
    }
}