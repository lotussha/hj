<?php


namespace app\apiadmin\logic\order;


use app\common\model\order\OrderAttachedModel;
use app\common\model\order\OrderLogModel;
use app\exception\OrderCancelException;
use app\exception\OrderStatusException;


class OrderOldLogic
{
    protected $model;

    public function __construct(OrderAttachedModel $model)
    {
        $this->model = $model;
    }

    public static $whereArrField = [
        'order_type'    => 'order_type',
        'order_status'  => 'status',
        'order_sn'      => 'tid',
        'start_time'    => 'created_at',
        'end_time'      => 'created_at',
    ];

    public function getList($admin_type = 1, $search = [])
    {
        //$search = [
        //  'order_type' => 1,
        //  'order_status' => 1,
        //  'start_time' => 2020-06-06,    //下单时间选择开始时间
        //  'end_time' => 2020-06-07,      //下单时间选择结束时间
        //  'order_sn' => 202009090909,    //订单编号
        //];
        //根据不用的门店显示不同的门店订单
        //todo 平台既可以查自己的,也可以查全部的
        $fiels = ['oid', 'status', 'amount', 'ascription', 'order_type', 'receiver_name', 'shop_mrho',
                  'receiver_tel', 'amount', 'created_at', 'after_sales_status', 'express_delivery_company', 'invoice_no'];
        $where = [
            'ascription' => $admin_type           //订单所属角色
        ];
        if ($search) {
            $searchWhere = $this->serachWhere($search);
            if ($searchWhere) {
                $where = array_merge($searchWhere, $where);
            }
        }
        $data = $this->model::with([
            'goods' => function ($query) {
                $query->field(['oid', 'goods_name', 'goods_image', 'goods_price', 'quantity', 'sku_value']);
            }
        ])->field($fiels)
          ->where($where)
          ->paginate(10)
          ->toArray();

        if (isset($search['export']) && $data) {
            $this->export($data);
        }
        return $data;
    }

    //订单列表搜索条件集合
    protected function serachWhere($where)
    {
        $newWhere = [];
        foreach ($where as $k => $v) {
            if (array_key_exists(self::$whereArrField[$k], $newWhere)) {
                $newWhere[self::$whereArrField[$k]] = [
                    'in', $newWhere[self::$whereArrField[$k]], $v
                ];
            } else {
                $newWhere[self::$whereArrField[$k]] = $v;
            }
        }
        return $newWhere;
    }

    //导出订单
    public function export($data)
    {


        $title = [
            '订单号', '下单用户', '商品名称', '规格', '数量', '货号', '成本价', '收件人', '收
             件人电话', '地址', '商家备注', '订单类型', '实付款金额', '下单时间', '售后状态','发货快递
             公司', '快递单号'
        ];

        $fileName = '订单导出.xlsx';
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

    //修改价格
    public function updateOrderAmount($params)
    {

        $oid = $params['oid'];
        //先判断订单状态是否未付款
        $status = $this->model->getOrderStatus($oid);
        if ($status > OrderAttachedModel::ORDER_STATUS_UNPAID) {
            throw new OrderStatusException();
        }
        //修改金额
        $this->model::update(['amount' => $params['price']], ['oid' => $oid]);
        //todo 记录行为操作日志
    }

    //取消订单
    public function cancelOrder($params)
    {
        $oid = $params['oid'];
        $notCanceStatus = [
            OrderAttachedModel::ORDER_STATUS_COMPLETED,
            OrderAttachedModel::ORDER_STATUS_CANCELLED,
        ];
        $status = $this->model->getOrderStatus($oid);
        if (in_array($status, $notCanceStatus)) {
            throw new OrderCancelException();
        }
        //修改订单状态
        $this->model::update(['status' => OrderAttachedModel::ORDER_STATUS_CANCELLED], ['oid' => $oid]);
        //todo 记录行为操作日志
    }

}