<?php
/**
 * Created by PhpStorm.
 * User: Jomlz
 * Date: 2020/8/4
 * Time: 20:17
 */

namespace app\common\model;

use think\Model;

class GoodsImagesModel extends Model
{
    protected $name = 'goods_images';

    public function getGoodsImg($goods_id=0)
    {
        return $this->field('img_id,image_url')->where(['goods_id'=>$goods_id])->select();
    }
}