<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/2
 * Time: 3:24 PM
 */

namespace app\http\index\controller;


use simple\controller\Controller;

class Index extends Controller
{
    public function index()
    {
        echo '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> simpleApi V1<br/><span style="font-size:30px">简约而不简单 - 为API开发而生的高性能框架</span></p><span style="font-size:22px;">[ V1.0 版本由 <a href="https://gitee.com/marslilei/simpleApi">marsli</a> 独家发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script>';
    }

    public function view(){
        $tpl['content'] = 'Hello Mars';
        $tpl['age'] = 333;
        $tpl['arr1'] = [
            ['text' => '111'],
            ['text' => '222']
        ];
        $this->display($tpl);
    }
}