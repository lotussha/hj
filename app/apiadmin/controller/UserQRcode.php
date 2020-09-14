<?php
declare (strict_types = 1);

namespace app\apiadmin\controller;

//use dh2y\qrcode\QRcode;
//use Endroid\QrCode\QrCode;
use sakuno\services\UtilService;
use think\Request;
class UserQRcode
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        UtilService::setSharePoster([],'http://www.baidu.com');
        header("Content-type:image/png");
//        halt(Request()->domain()."/public/index.php/../vendor/dh2y/qrcode/src/QRcode.php");
//        include (Request()->domain()."/public/index.php/../vendor/dh2y/qrcode/src/QRcode.php");
//        $da =  new QRcode() ;
        $qrCode = new QrCode('http://www.baidu.com');
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
//        echo $qrCode;
//        halt($qrCode);

    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
