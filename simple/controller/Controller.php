<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/2
 * Time: 11:05 AM
 */

namespace simple\controller;


class Controller
{

    public function options()
    {
        success('Options Success');
    }

    /**
     * 变量传递加载方法
     * @param array $tpl
     */
    public function assign(array $tpl): void
    {

        $type = [
            'string' => function ($key, $value) {
                return "var $key = '$value';";
            },
            'integer' => function ($key, $value) {
                return "var $key = $value;";
            },
            'array' => function ($key, $value) {
                return "var $key = " . json_encode($value) . ";";
            },
        ];

        $js = "<script>\n";
        foreach ($tpl as $key => $value) {
            $js .= $type[gettype($value)]($key, $value);
            $js .= "\n";
        }
        $js .= '</script>';
        echo $js;

    }

    /**
     * html页面加载方法
     * @param array $tpl
     * @param string $path
     */
    public function display(array $tpl, string $path = ''): void
    {

        $this->assign($tpl);

        $group = getName('group');
        $controller = getName('controller');
        $action = getName('action');

        if (!empty($path)) {
            $path_arr = explode('/', $path);
            $size = count($path_arr);

            switch ($size) {
                case 1:
                    $action = $path;
                    break;
                case 2:
                    $controller = $path_arr[0];
                    $action = $path_arr[1];
                    break;
                case 3:
                    $group = $path_arr[0];
                    $controller = $path_arr[1];
                    $action = $path_arr[2];
                    break;
                default:
                    debug('模版路径不正确！');
                    exit();
            }
        }

        require APP_PATH . '/' . $group . '/view/' . $controller . '/' . $action . '.html';
    }
}