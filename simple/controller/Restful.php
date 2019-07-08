<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/2
 * Time: 11:18 AM
 */

namespace simple\controller;


interface Restful
{
    const RESTFUL_METHOD = [
        'GET'      => 'fetchList',
        'PUT'      => 'update',
        'POST'     => 'create',
        'DELETE'   => 'delete'
    ];

    public function fetchList();

    public function getInfo($primary);

    public function update();

    public function create();

    public function delete();

}