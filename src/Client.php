<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2022/7/20
 * Time: 14:05
 */

namespace ShanDe;

use ShanDe\encrypt\RSA;

class Client
{
    public $publicKeyFile;
    public $privateKeyFile;
    public $privateKeyPwd;
    public $domain;
    public $signType ;
    protected $sign;
    protected $client;

    public function __construct($publicKeyFile,$privateKeyFile,$privateKeyPwd,$domain,$signType='01')
    {
        $this->domain = $domain;
        $this->publicKeyFile  = $publicKeyFile;
        $this->privateKeyPwd = $privateKeyPwd;
        $this->privateKeyFile = $privateKeyFile;
        $this->signType = $signType;
    }

    public function post($uri, $data)
    {
        $postData = array(
            'charset'  => 'utf-8',
            'signType' => $this->signType,
            'data'     => json_encode($data)
        );
        $postData['sign']= $this->sign($postData['data']);
        $this->getClient();
        $data = $this->getClient()->post($uri, ['body'=>http_build_query($postData)]);
        return $this->parseResult($data->getBody()->getContents());
    }
    protected function parseResult($result)
    {
        $arr      = array();
        $response = urldecode($result);
        $arrStr   = explode('&', $response);
        foreach ($arrStr as $str) {
            $p         = strpos($str, "=");
            $key       = substr($str, 0, $p);
            $value     = substr($str, $p + 1);
            $arr[$key] = $value;
        }
        return $arr;
    }

    public function data($data)
    {
        return json_decode($data['data'],true);
    }
    protected function getClient(){
        if(!$this->client){
            $config['base_uri'] = $this->domain;
            $config['verify'] = false;
            $config['headers'] = [
                'Content-Type'=>'application/x-www-form-urlencoded',
            ];
            $config['debug'] = false;
            $config['version'] = CURL_HTTP_VERSION_1_1;
            $config['http_errors'] = false;
            $this->client = new \GuzzleHttp\Client($config);
        }
        return $this->client;
    }
    protected function getRsa(){
        if(!$this->sign){
            $this->sign = new RSA();
            $this->sign->privateKeyFile = $this->privateKeyFile;
            $this->sign->publicKeyFile = $this->publicKeyFile;
            $this->sign->privateKeyPwd = $this->privateKeyPwd;
        }
        return $this->sign;
    }
    public function sign($data){
        return $this->getRsa()->sign($data);
    }
    public function verify($data,$sign)
    {
        return $this->getRsa()->verify($data,$sign);
    }
}