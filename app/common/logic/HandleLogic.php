<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/7/31
 * Time: 17:03
 */

namespace app\common\logic;


class HandleLogic
{
    public function Handle($data,$model='',$act='',$key_name='id')
    {
        $HandleModel = model($model);
        if($act == 'add'){
            $data['add_time'] = time();
            $r = $HandleModel->create($data);
            $object_id = $r->$key_name ?? $r->id;
        }
        if($act == 'edit'){
            $object_id = $data[$key_name];
            $save = $HandleModel::where($key_name,$data[$key_name])->find();
            if (!$save){
                return $arr = array('status'=>0,'code'=>'10000','object_id'=>$object_id,'msg'=>'信息不存在');exit;
            }
            $r = $save->save($data);
        }

        if($act == 'del'){
            $object_id = $data[$key_name];
            if (isset($HandleModel->noDeletionId) && count($HandleModel->noDeletionId) > 0) {
                if (is_array($object_id)) {
                    if (array_intersect($HandleModel->noDeletionId, $object_id)) {
                        return $arr = array('status'=>0,'msg'=>'ID为' . implode(',', $HandleModel->noDeletionId) . '的数据无法删除');
                    }
                } else if (in_array($object_id, $HandleModel->noDeletionId)) {
                    return $arr = array('status'=>0,'msg'=>'ID为' . $object_id . '的数据无法删除');
                }
            }
//            $r = $HandleModel->destroy(function($query) use ($data,$key_name){
//                $query->whereIn($key_name,$data[$key_name]);
//            });
            $r = $HandleModel->whereIn($key_name,[$data[$key_name]])->delete();
        }
        if ($r){
            return $arr = array('status'=>1,'code'=>'10000','object_id'=>$object_id,'msg'=>'操作成功');exit;
        }else{
            return $arr = array('status'=>0,'code'=>'10000','object_id'=>$object_id,'msg'=>'操作失败');exit;
        }
    }
}