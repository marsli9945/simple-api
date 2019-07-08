<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/2
 * Time: 11:47 AM
 */

namespace simple\controller;


use simple\Unit;

class ModelController extends Controller implements Restful
{
    protected $model;
    protected $modelName;

    public function __construct()
    {
        $modeName = $this->modelName ?: getName('controller');
        $this->model = model($modeName);
    }

    /**
     * @throws \Exception
     */
    public function fetchList()
    {
        $param = input('get');
        $param['_limit'] && $param['_limit'] = Unit::formatLimit($param['_limit']);
        success([
            'items' => $this->model->select($param),
            'total' => $this->model->getTotal($param)
        ]);
    }

    /**
     * @param $primary
     * @throws \Exception
     */
    public function getInfo($primary)
    {
        success($this->model->selectByPrimary($primary));
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        $param = input('put');
        success($this->model->update($param['where'], $param['data']));
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        success($this->model->insert(input('post')));
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        success($this->model->delete(input('delete')));
    }

}