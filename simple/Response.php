<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/1/29
 * Time: 5:59 PM
 */

namespace simple;


class Response
{

    /**
     * @param string $url
     * @param array $data
     * @param array $header
     * @param int $timeout
     * @return array|mixed
     */
    public static function SendUrl(string $url, array $data = [], array $header = [], int $timeout = 30)
    {
        $ssl = substr($url, 0, 8) == "https://" ? true : false;

        $ch = curl_init();

        $opt = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_ENCODING => '',
        );

        if ($data) {
            $opt[CURLOPT_POST] = 1;
            $opt[CURLOPT_POSTFIELDS] = is_array($data) ? http_build_query($data) : $data;
        }

        if ($header) {
            $opt[CURLOPT_HTTPHEADER] = $header;
        }

        if ($ssl) {
            $opt[CURLOPT_SSL_VERIFYHOST] = 1;
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
        }

        curl_setopt_array($ch, $opt);

        $data = curl_exec($ch);

        curl_close($ch);

        return $data;
    }

    /**
     * api接口回调消息封装方法
     * @param int $status 业务状态码
     * @param string $msg 提示信息
     * @param array $data 数据
     * @param int $httpCode http状态码
     * @return void
     */
    public static function apiResult(int $status, string $msg, $data = [], int $httpCode = 200): void
    {
        $result = [
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        ];
        config('http_code_show') && http_response_code($httpCode);
        echo json_encode($result);
    }

}