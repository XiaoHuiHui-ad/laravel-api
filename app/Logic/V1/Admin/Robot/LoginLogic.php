<?php
/**
 * Created by PhpStorm.
 * User: chen
 * Date: 2020/1/28
 * Time: 10:27
 */

namespace App\Logic\V1\Admin\Robot;


use App\Http\Middleware\ClientIp;
use App\Libraries\classes\ProxyIP\GetProxyIP;
use App\Logic\V1\Admin\Base\BaseLogic;
use App\Model\V1\Robot\HeartBeatModel;
use DdvPhp\DdvUtil\Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class LoginLogic extends BaseLogic
{
    protected $uuid = '';

    protected $wxId = '';

    /**
     * 获取QRCode
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function getQrcode()
    {
        $client = new Client();
            $res = $client->request('POST', 'http://106.15.235.187:1925/api/Login/GetQrCode', [
                'form_params' => [
                    "proxyIp" => "183.21.105.93:4287",
                    "proxyUserName" => "zhima",
                    "proxyPassword" => "zhima",
                    "deviceID" => "243d854c-aaaf-4f4d-8c95-222825867ee8",
                    "deviceName" => "iPad"
                ]
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res["Success"] ==  false){
                throw new Exception($res["Message"],'PROXY_TIME_OUT');
            }
            return $res["Data"];
    }

    /**
     * 检查是否登录
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkLogin()
    {
        $client = new Client();
        try {
            $res = $client->request('POST', 'http://106.15.235.187:1925/api/Login/CheckLogin/' . $this->uuid, [
                'form_params' => ["uuid" => $this->uuid]
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res["Success"]) {
                if (empty($res["Data"]["WxId"])){
                    return ["code" => "402", "message" => "等待微信扫描"];
                }
                // 更新微信信息
                (new HeartBeatModel())->checkWxInfo($res["Data"]);
                return ["data" => $res["Data"]];
            }
            return ["code" => $res["Code"], "message" => $res['Message']];
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }

    /**
     * 退出登录
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loginOut()
    {
        $client = new Client();
        try {
            $res = $client->request('POST', 'http://106.15.235.187:1925/api/Login/LogOut/' . $this->wxId);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res["Success"]){
                return ["data" => $res["Data"]];
            }
            return ["code" => $res["Code"],"message" => $res["Message"]];
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }

    /**
     * 检查心跳
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function heartBeat()
    {
        $client = new Client();
        try {
            $res = $client->request('POST', 'http://106.15.235.187:1925/api/Login/HeartBeat/' . $this->wxId);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res["Success"]){
                return ["data" => $res["Data"]];
            }
            return ["code" => $res["Code"],"message" => $res["Message"]];
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }

    /**
     * 二次登录
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function twiceLogin()
    {
        $client = new Client();
        try {
            $res = $client->request('POST', 'http://106.15.235.187:1925/api/Login/TwiceLogin', [
                'form_params' => ["wxId" => $this->wxId]
            ]);
            $res = $res->getBody()->getContents();
            return $res;
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }

    /**
     * 初始化好友
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function initUser()
    {
        $client = new Client();
        try {
            $res = $client->request('POST', 'http://106.15.235.187:1925/api/Login/InitUser', [
                'form_params' => ["initMsg" => $this->initMsg]
            ]);
            $res = $res->getBody()->getContents();
            return $res;
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }

    /**
     * 初始化用户
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function newInit()
    {
        $client = new Client();
        try {
            $res = $client->request('POST', 'http://106.15.235.187:1925/api/Login/NewInit', [
                'form_params' => ["wxId" => $this->wxId]
            ]);
            $res = $res->getBody()->getContents();
            return $res;
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }


}