<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2022/7/20
 * Time: 13:32
 */
namespace ShanDe\encrypt;

class RSA
{
    public $publicKeyFile;
    public $privateKeyFile;
    public $privateKeyPwd;

    private function publicKey()
    {
        try {
            $file = file_get_contents($this->publicKeyFile);
            if (!$file) {
                throw new \Exception('getPublicKey::file_get_contents ERROR 公钥文件读取有误,config文件夹中进行修改');
            }
            $cert   = chunk_split(base64_encode($file), 64, "\n");
            $cert   = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";
            $res    = openssl_pkey_get_public($cert);
            $detail = openssl_pkey_get_details($res);
            openssl_free_key($res);
            if (!$detail) {
                throw new \Exception('getPublicKey::openssl_pkey_get_details ERROR 公钥文件解析有误');
            }
            return $detail['key'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function privateKey()
    {
        try {
            $file = file_get_contents($this->privateKeyFile);
            if (!$file) {
                throw new \Exception('getPrivateKey::file_get_contents 私钥文件读取有误,config文件夹中进行修改');
            }
            if (!openssl_pkcs12_read($file, $cert, $this->privateKeyPwd)) {
                throw new \Exception('getPrivateKey::openssl_pkcs12_read ERROR 私钥密码错误，config文件夹中进行修改');
            }
            return $cert['pkey'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function sign($plainText)
    {
        try {
            $resource = openssl_pkey_get_private($this->privateKey());
            $result   = openssl_sign($plainText, $sign, $resource);
            openssl_free_key($resource);
            if (!$result) throw new \Exception('sign error');
            return base64_encode($sign);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function verify($plainText, $sign)
    {
        $resource = openssl_pkey_get_public($this->publicKey());
        $result   = openssl_verify($plainText, base64_decode($sign), $resource);
        openssl_free_key($resource);
        return $result;
    }
}