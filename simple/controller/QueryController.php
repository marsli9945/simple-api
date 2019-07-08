<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2019/2/2
 * Time: 11:55 AM
 */

namespace simple\controller;


use simple\Unit;

class QueryController extends Controller implements Restful
{
    protected $query;
    protected $database;

    /**
     * QueryController constructor.
     * @throws \simple\exception\ApiException
     */
    public function __construct()
    {
        $database = getName('group');
        $database = config('model_default_database') ?: $database;
        $database = $this->database ?: $database;
        $this->query = query($database,getName('controller'));
    }

    /**
     * @throws \Exception
     */
    public function fetchList()
    {
        $param = input('get');
        $param['_limit'] && $param['_limit'] = Unit::formatLimit($param['_limit']);
        success([
            'items' => $this->query->select($param),
            'total' => $this->query->getTotal($param)
        ]);
    }

    /**
     * @param $primary
     * @throws \Exception
     */
    public function getInfo($primary)
    {
        success($this->query->select([config('model_default_primary') => $primary]));
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        $param = input('put');
        success($this->query->update($param['where'],$param['data']));
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        success($this->query->insert(input('post')));
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        success($this->query->delete(input('delete')));
    }

}