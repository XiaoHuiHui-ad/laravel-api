<?php
/**
 * Created by PhpStorm.
 * User: chen
 * Date: 2020/1/28
 * Time: 10:27
 */

namespace App\Logic\V1\Admin\Robot;


use App\Http\Middleware\ClientIp;
use App\Jobs\HeartBeatRobot;
use App\Libraries\classes\ProxyIP\GetProxyIP;
use App\Logic\V1\Admin\Base\BaseLogic;
use App\Model\V1\Robot\WxRobotModel;
use DdvPhp\DdvUtil\Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LoginLogic extends BaseLogic
{
    protected $uuid = '';

    protected $wxId = '';

    protected $userName;

    protected $password;

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
        $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/GetQrCode');
        $res = json_decode($res->getBody()->getContents(), true);
        if ($res["Success"] == false) {
            throw new Exception($res["Message"], 'PROXY_TIME_OUT');
        }
        return $res["Data"];
    }

    /**
     * 62 数据登录
     *
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function data62Login()
    {
        $client = new Client();
        try {
            $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/Get62Data/' . $this->wxId);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res["Success"]) {
                $client = new Client();
                $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/Data62Login', [
                    'form_params' => [
                        "userName" => $this->userName,
                        "password" => $this->password,
                        "data62" => $res["Data"],
                        "proxyIp" => "113.94.123.53:4287",
                        "proxyUserName" => "zhima",
                        "proxyPassword" => "zhima",
                    ]
                ]);
                $res = json_decode($res->getBody()->getContents(), true);
                if ($res["Success"]) {
                    return true;
                }
                return ["code" => $res["Code"], "message" => $res['Message']];
            }
            return ["code" => $res["Code"], "message" => $res['Message']];
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
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
            $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/CheckLogin/' . $this->uuid, [
                'form_params' => ["uuid" => $this->uuid]
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res["Success"]) {
                if (empty($res["Data"]["WxId"])) {
                    return ["code" => "402", "message" => "等待微信扫描"];
                }
                // 更新微信信息
                $accountData = $this->newInit($res["Data"]["WxId"]);
                (new WxRobotModel())->checkWxInfo($res["Data"]);
                $heartBeatState = array_merge($this->stateHeartBeat($res["Data"]["WxId"]), $res["Data"]);
                return ["data" => array_merge($accountData["ModUserInfos"][0], $heartBeatState)];
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
            $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/LogOut/' . $this->wxId);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res["Success"]) {
                (new WxRobotModel())->where("wxid", $this->wxId)->update(["status" => 0]);
                return ["data" => $res["Data"]];
            }
            return ["code" => $res["Code"], "message" => $res["Message"]];
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }

    /**
     * 启动自动心跳
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function startHeartBeat()
    {
        $client = new Client();
        $res = $client->request('GET', 'http://114.55.164.90:1697/api/HeartBeat/StartHeartBeat/' . $this->wxId);
        $res = json_decode($res->getBody()->getContents(), true);
        return ["HeartBeatState" => $res["Message"] == "已启动" ? 1 : 0];
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function closeHeartBeat()
    {
        $client = new Client();
        $res = $client->request('GET', 'http://114.55.164.90:1697/api/HeartBeat/CloseHeartBeat/' . $this->wxId);
        $res = json_decode($res->getBody()->getContents(), true);
        return ["HeartBeatState" => $res["Message"] == "已启动" ? 1 : 0];
    }

    /**
     * 心跳状态
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function stateHeartBeat($wxId)
    {
        $client = new Client();
        $res = $client->request('GET', 'http://114.55.164.90:1697/api/HeartBeat/StateHeartBeat/' . $wxId);
        $res = json_decode($res->getBody()->getContents(), true);
        return ["HeartBeatState" => $res["Message"] == "已启动" ? 1 : 0];
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
            $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/TwiceLogin', [
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
            $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/InitUser', [
                'form_params' => ["initMsg" => $this->initMsg]
            ]);
            $res = $res->getBody()->getContents();
            return $res;
        } catch (\Throwable $e) {
            Log::info('Fail to call api');
        }
    }

    /**
     * 初始化用户数据
     *
     * @param $wxId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function newInit($wxId)
    {
        $client = new Client();
        $res = $client->request('POST', 'http://114.55.164.90:1697/api/Login/NewInit/'.$wxId, [
            'form_params' => ["WxId" => $wxId]
        ]);
        $res = json_decode($res->getBody()->getContents(), true);
        return $res["Data"];
    }


}