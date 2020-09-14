<?php


namespace app\apiadmin\logic\order;


use app\common\model\order\OrderAftersalesModel;
use app\exception\NotDataException;
use app\exception\OrderStatusException;

class OrderAfterSaleLogicOld
{
    protected $model;

    public function __construct(OrderAftersalesModel $orderAftersalesModel)
    {
        $this->model = $orderAftersalesModel;
    }


    /**
     * @param $admin  string  当前管理员身份
     * @param $search array   搜索条件
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getList($admin = 1, $search)
    {
        $where = [
            'shop_id' => $admin,
        ];
        $orderWhere = [];
        $field = [
            'aftersales_bn', 'shop_id', 'user_id', 'aftersales_type', 'oid', 'goods_id', 'num', 'reason',
            'description', 'evidence_pic', 'shop_explanation', 'admin_explanation', 'refunds_reason',
            'sendback_data', 'sendconfirm_data', 'status'
        ];

        if ($search) {
            $searchWhere = $this->searchWhere($search);
            if ($searchWhere) {
                $where = array_merge($searchWhere, $where);
            }
        }

        $model = $this->model::with([
            'goods' => function ($query) {
                $query->field(['oid', 'goods_name', 'goods_image', 'goods_price', 'quantity', 'sku_value']);
            },
            'orders' => function ($query){
                $query->field(['oid', 'status']);
            }
        ])->field($field)
          ->where($where);

        if (isset($search['export'])) {
            $data = $model->select()->toArray();
            $this->export($data);
        }

        $data = $model->paginate(OrderAftersalesModel::PAGINATION);

        return $data;
    }

    /**
     * 详情
     * @param $id
     * @return array
     * @throws NotDataException
     * @throws \think\db\exception\DataNotFoundException
     */
    public function getInfo($id)
    {
        $fiels = [
            'aftersales_bn', 'shop_id', 'user_id', 'aftersales_type', 'oid', 'goods_id', 'num', 'reason',
            'description', 'evidence_pic', 'shop_explanation', 'admin_explanation', 'refunds_reason',
            'sendback_data', 'sendconfirm_data', 'gift_data', 'status'
        ];

        $data = $this->model::where('id', $id)->field($fiels)->find()->toArray();
        if (empty($data)) {
            throw new NotDataException();
        }

        return $data;

    }

    //审核
    public function audit($params)
    {
        $id = $params['id'];
        $status = $params['status'];   //1-同意 2-驳回

        //查看当前售后订单状态
        $infos = $this->model::where('id', $id)->field(['aftersales_type', 'status'])->find();
        if ($infos['status'] != OrderAftersalesModel::AFTER_SALE_PENDING) {
            throw new OrderStatusException(['msg' => '该订单已经处理过了']);
        }

        //判断订单类型
        switch ($infos['aftersales_type']) {
            case OrderAftersalesModel::AFTER_SALE_REFUND :   //仅退款
                //todo 先计算该订单的实付金额或者商品的实付金额，调用退款接口，退款回调中更改订单状态为已处理，减会员积分、佣金相关
                break;
            case OrderAftersalesModel::AFTER_SALE_RETURN :   //退货退款
                //todo 更新订单状态为处理中(更改progress为1)或驳回，
                $where = [
                    'id' => $id,
                ];
                $update_data = [];

                if ($status == OrderAftersalesModel::AFTER_SALE_AGREE) {
                    $update_data['progress'] = OrderAftersalesModel::AFTER_SALE_POST_BACK;
                    $update_data['status']   = OrderAftersalesModel::AFTER_SALE_PROCESSING;
                } else {
                    $update_data['status'] = OrderAftersalesModel::AFTER_SALE_OVERRULE;
                }

                $this->model::update($where, $update_data);
                break;
        }

    }


    protected function searchWhere($where)
    {
        $newWhere = [];
        if (isset($where['start_time']) && isset($where['end_time'])) {
            $newWhere['created_at'] = [
                'in', strtotime($where['start_time']), strtotime($where['end_time'])
            ];
        }
        if (isset($where['order_sn'])) {
            $newWhere['aftersales_bn'] = $where['order_sn'];

        }
        if (isset($where['order_status'])) {
            $newWhere['order_status'] = $where['status'];
        }
        return $newWhere;
    }

    //导出
    protected function export($data)
    {
        $title = [
            '所属平台','订单号', '下单用户', '商品名称', '规格', '数量', '货号', '成本价', '收件人', '收件人电话',
            '地址', '商家备注', '售后类型', '退款金额', '申请理由', '申请售后时间', '售后状态', '发货快递公司', '快递单号'
        ];

        $fileName = '售后单导出.xlsx';
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //表头
        //设置单元格内容
        foreach ($title as $key => $value) {
            // 单元格内容写入
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $value);
        }
        $row = 2; // 从第二行开始
        foreach ($data as $item) {
            $column = 1;
            foreach ($item as $value) {
                // 单元格内容写入
                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}