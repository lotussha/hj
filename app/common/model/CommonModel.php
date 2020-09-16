<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 9:02
 *                ..::::::::::::.                                女神保佑，代码无bug！！！
 *              ``::::::::::::::::                               Codes are far away from bugs with the goddess！！！
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *    ````':.          ':::::::::'                  ::::..
 *                       '.:::::'                    ':'````..
 *
 */
namespace app\common\model;

use think\facade\Db;
use think\Model;

/**
 * 一些常用数据库操作 by sakuno
 * common model
 * Class CommonModel
 * @package app\common\model
 */
class CommonModel extends Model
{

    /**
     * 查询对象
     * @var null
     */
    private static $ob_query = null;

    /**
     * 新增数据
     * @param array $data
     * @param bool $is_return_pk
     * @return mixed
     */
    final public function addInfo($data = [], $is_return_pk = true){
        $data['create_time'] = time();
        $return_data = $this->insert($data, $is_return_pk);
        return $return_data;
    }

    /**
     * 批量新增数据
     * @param array $data
     * @return mixed
     */
    final public function addInfoAll($data = []){
        return $this->insertAll($data);
    }

    /**
     * 保存数据
     * @param array $data
     * @param array $where
     * @param array $field_arr
     * @return bool
     */
    final public function saveInfo($data = [], $field_arr = []){
        $return_data = $this;
        if(!empty($field_arr)){
            $return_data = $return_data->allowField($field_arr);
        }
        $return_data = $return_data->save($data);
        return $return_data;
    }

    /**
     * 批量保存数据
     * @param array $data
     * @return \think\Collection
     * @throws \Exception
     */
    final public function saveInfoAll($data = []){
        $return_data = $this->saveAll($data);
        return $return_data;
    }

    /**
     * 设置数据列表
     * @param array $data_list
     * @param bool $replace
     * @return \think\Collection
     * @throws \Exception
     */
    final public function setList($data_list = [], $replace = false)
    {
        $return_data = $this->saveAll($data_list, $replace);
        return $return_data;
    }

    /**
     * 更新数据
     * @param array $where
     * @param array $data
     * @param array $field_arr
     * @return CommonModel
     */
    final public function updateInfo($where = [], $data = [],$field_arr = []){
        $data['update_time'] = time();
        $return_data = $this;
        if(!empty($field_arr)){
            $return_data = $return_data->allowField($field_arr);
        }
        $return_data = $return_data->where($where)->update($data);
        return $return_data;
    }

    /**
     * 设置某个字段值
     * @param array $where
     * @param string $field
     * @param string $value
     * @return CommonModel
     */
    final public function setFieldValue($where = [], $field = '', $value = '')
    {
        return $this->updateInfo($where, [$field => $value]);
    }

    /**
     * 删除数据
     * 删除数据
     * @param $where
     * @param bool $is_true 是否真删除 0否(伪删除) 1是
     * @return CommonModel|bool
     */
    final public function deleteInfo($where, $is_true = false){
        if($is_true){
            $return_data = $this->where($where)->delete();
        } else {
            $return_data = $this->updateInfo($where,  ['is_delete'=>config('status.mysql.table_delete'),'delete_time'=>time()]);
        }
        return $return_data;
    }

    /**
     * 获取某个列的数组
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    final public function getColumn($where = [], $field = '', $key = ''){
        return $this->where($where)->column($field, $key);
    }

    /**
     * 得到某个字段的值
     * @param array $where
     * @param string $field
     * @param string $key
     * @return mixed
     */
    final public function getValues($where = [], $field = '', $key = ''){
        return $this->where($where)->value($field, $key);
    }

    /**
     * 获取数据列表
     * @param array $where
     * @param string $field
     * @param array $order
     * @param array $hidden
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function getList($where = [], $field = '*',$order = [],$hidden = []){
        return $this->where($where)->field($field)->hidden($hidden)->order($order)->select()->toArray();
    }

    /**
     * 获取数据列表(分页)
     * @param array $where
     * @param int $page
     * @param int $page_size
     * @param string $field
     * @param array $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function getListPage($where = [], $page = 1, $page_size = 10, $field = '*', $order = []){
        $limit = ($page - 1) * $page_size;
        $res = $this->where($where)->field($field)->order($order)->limit($limit,$page_size)->select();
        if(!empty($res)){
            $res = $res->toArray();
        }
        return $res;
    }

    /**
     * 单查询获取前多少条数据
     * @param array $where
     * @param int $limit
     * @param string $field
     * @param array $order
     * @param array $hidden
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function getLimitData($where = [], $limit = 0, $field = '*', $order = [],$hidden = []){
        $res =  $this->where($where)->field($field)->hidden($hidden)->order($order)->limit($limit)->select();
        if(!empty($res)){
            $res = $res->toArray();
        }
        return $res;
    }

    /**
     * 双查询获取前多少条数据
     * @param array $where
     * @param array $whereOr
     * @param int $limit
     * @param string $field
     * @param array $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function getLimitDataDoubleWhere($where = [], $whereOr = [], $limit = 0, $field = '*', $order = []){
        $res = $this->where($where)->whereOr($whereOr)->field($field)->order($order)->limit($limit)->select();
        if(!empty($res)){
            $res = $res->toArray();
        }
        return $res;
    }

    /**
     * 统计数据
     * @param array $where 条件
     * @param string $stat_type 统计方法 默认count
     * @param string $field 字段 默认id
     * @return mixed
     */
    final public function statInfo($where = [], $stat_type = 'count', $field = 'id'){
        return $this->where($where)->$stat_type($field);
    }

    /**
     * 获取单条数据
     * @param array $where
     * @param string $field
     * @param array $hiden
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function findInfo($where = [], $field = '*',$hiden = []){
        $res = $this->where($where)->field($field)->hidden($hiden)->find();
        if(!empty($res)){
            $res = $res->toArray();
        }
        return $res;
    }

    /**
     * 字段自增
     * @param $where 条件
     * @param $field 字段
     * @param int $step 步长
     * @return mixed
     */
    final public function setDataInc($where,$field,$step = 1){
        $result = $this->where($where)->inc($field,$step)->update();
        return $result;
    }

    /**
     * 字段自减
     * @param $where 条件
     * @param $field 字段
     * @param int $step 步长
     * @return mixed
     */
    final public function setDataDec($where,$field,$step = 1){
        $result = $this->where($where)->dec($field,$step)->update();
        return $result;
    }

    /**
     * 开启事务
     */
    public static function beginTrans(){
        Db::startTrans();
    }

    /**
     * 提交事务
     */
    public static function commitTrans(){
        Db::commit();
    }

    /**
     * 回滚事务
     */
    public static function rollbackTrans()
    {
        Db::rollback();
    }

    /**
     * 根据结果提交滚回事务
     * @param $res
     */
    public static function checkTrans($res)
    {
        if($res){
            self::commitTrans();
        }else{
            self::rollbackTrans();
        }
    }

    /**
     * 联表查询单条数据
     * @param array $where
     * @param string $field
     * @param null $join
     * @param string $alias
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function findInfoJoin($where = [], $field = '*', $join = null, $alias = 'a'){
        self::$ob_query = $this->where($where)->field($field);
        if (!empty($alias) && !empty($join)) {
            self::$ob_query = self::$ob_query->alias($alias);
        }
        if(is_array($join)){
            foreach ($join as &$v){
                self::$ob_query = self::$ob_query->join($v);
            }
        } else {
            !empty($join) && self::$ob_query = self::$ob_query->join($join);
        }
        $result_data = self::$ob_query->find()->toArray();
        return $result_data;
    }

    /**
     * 联表获取数据列表
     * @param array $where
     * @param array $whereOr
     * @param string $field
     * @param null $join
     * @param null $limit
     * @param array $order
     * @param string $group
     * @param string $alias
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function getListJoin($where = [],$whereOr = [],$field = '*',$join = null,$limit = null,$order = [],$group = '',$alias = 'a'){
        self::$ob_query = $this->where($where)->whereOr($whereOr)->order($order);
        self::$ob_query = self::$ob_query->field($field);
        if (!empty($alias) && !empty($join)) {
            self::$ob_query = self::$ob_query->alias($alias);
        }
        if(is_array($join)){
            foreach ($join as &$v){
                self::$ob_query = self::$ob_query->join($v);
            }
        } else {
            !empty($join) && self::$ob_query = self::$ob_query->join($join);
        }
        !empty($group) && self::$ob_query = self::$ob_query->group($group);
        !empty($limit) && self::$ob_query = self::$ob_query->limit($limit);
        $result_data = self::$ob_query->select()->toArray();
        return $result_data;
    }

    /**
     * 联表获取数据列表(分页)
     * @param array $where
     * @param array $whereOr
     * @param string $field
     * @param null $join
     * @param null $page
     * @param null $page_size
     * @param array $order
     * @param string $group
     * @param string $alias
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function getListLimitJoin($where = [],$whereOr = [],$field = '*',$join = null,$page = null,$page_size = null,$order = [],$group = '',$alias = 'a'){
        self::$ob_query = $this->where($where)->whereOr($whereOr)->order($order);
        self::$ob_query = self::$ob_query->field($field);
        if (!empty($alias) && !empty($join)) {
            self::$ob_query = self::$ob_query->alias($alias);
        }
        if(is_array($join)){
            foreach ($join as &$v){
                self::$ob_query = self::$ob_query->join($v);
            }
        } else {
            !empty($join) && self::$ob_query = self::$ob_query->join($join);
        }
        !empty($group) && self::$ob_query = self::$ob_query->group($group);
        if(!empty($page) && !empty($page_size)){
            $limit = ($page - 1) * $page_size;
            self::$ob_query = self::$ob_query->limit($limit,$page_size);
        }
        $result_data = self::$ob_query->select()->toArray();
        return $result_data;
    }

    /**********************************jomlz start****************************************************/

    //可搜索字段
    protected $searchField = [];

    //可作为条件的字段
    protected $whereField = [];

    //可字段搜索器 时间范围查询
    protected $timeField = [];

    //禁止删除的数据id
    public $noDeletionId = [];

    //软删除字段默认值
    protected $defaultSoftDelete = 0;

    /**
     * 查询处理
     * @param $query
     * @param $param
     * User: Jomlz
     * Date: 2020/8/11 14:00
     */
    public function scopeWhere($query, $param)
    {
        //关键词like搜索
        $keywords = $param['keywords'] ?? '';
        if (!empty($keywords) && count($this->searchField) > 0) {
            $this->searchField = implode('|', $this->searchField);
            $query->where($this->searchField, 'like', '%' . $keywords . '%');
        }
        //字段条件查询
        if (count($this->whereField) > 0 && count($param) > 0) {
            foreach ($param as $key => $value) {
                if (!empty($value) && in_array($key, $this->whereField)) {
                    $query->where($key, $value);
                }
            }
        }
        //时间范围查询
        if (count($this->timeField) > 0 && count($param) > 0) {
            foreach ($param as $item=>$value){
                if(!empty($value) && in_array($item,$this->timeField)){
                    $time_value = explode(',',$value);
                    if (!empty($time_value[0]) && !empty($time_value[1])){
                        $query->whereBetweenTime($item, strtotime($time_value[0]),strtotime($time_value[1]));
                    }
                }
            }
        }
        //排序
        $order = $param['order'] ?? '';
        $by    = $param['by'] ?? 'desc';
        $query->order($order ?: '', $by ?: 'desc');
    }
    /*************************************end*************************************************/



    /**********************************sakuno start****************************************************/

    public function checkAdminDoAuth(bool $return_where = false,int $role_type = null,int $role_admin_id = null){
        // TODO 获取当前登录用户(1平台,2供应商,3商家,4团长)信息
        $adminInfo = [];
        // 分为平台和非平台
        if($adminInfo['role_type'] == 1){

        }
    }

    /**********************************sakuno end****************************************************/



    /**********************************hao start****************************************************/

    /**
     * 新增数据
     * @param array $data
     * @param bool $is_return_pk
     * @return mixed
     */
    final public function addInfoId($data = [], $is_return_pk = true){
        $data['create_time'] = time();
        $return_data = $this->insertGetId($data, $is_return_pk);
        return $return_data;
    }

    /**
     * 获取列表
     * @return array
     * User: hao
     * Date: 2020.09.16
     */
    public function getCommonLists($receive){
        $receive['list_rows'] = isset($receive['list_rows']) ? $receive['list_rows'] : 20;  //多少条
        $receive['field'] = isset($receive['field']) ? $receive['field'] : true;//指定字段
        $receive['where'] = isset($receive['where']) ? $receive['where'] : '';//指定字段

        $data = $this
            ->field($receive['field'])
            ->where($receive['where'])
            ->where('is_delete','=',0)
            ->scope('where', $receive)
            ->paginate($receive['list_rows']);
        return $data->toArray();
    }
    /*************************************hao*************************************************/

}
