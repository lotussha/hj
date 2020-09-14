<?php

namespace app\apiadmin\logic\coupon;

use app\common\model\coupon\CouponIssueModel;

class CouponIssueLogic
{
    public function getIssueList($page = 1, $list_rows = 10, $param)
    {
        $is_del      = $param['is_del'] ?? 0;
        $field       = $param['field'] ?? '';
        $issueModel = new CouponIssueModel();
        $lists = $issueModel
            ->field($field)
            ->scope('where', $param)
            ->where('is_del', $is_del)
            ->page($page, $list_rows)
            ->order('id desc')
            ->select();
        foreach ($lists as &$value){
            $value['receive'] = $issueModel->receive_text[$value['receive']];
            $value['status'] = $issueModel->status_text[$value['status']];
            $value['start_time'] = date('Y-m-d h:i:s', $value['start_time']);
            $value['end_time'] = date('Y-m-d h:i:s', $value['end_time']);
        }
        return arrString($lists->toArray());
    }
}
