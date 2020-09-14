<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/08/06 15:25
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
namespace sakuno\services;

/**
 * 工具服务
 * Class UtilService
 * @package sakuno\services
 */
class UtilService
{

    /**
     * TODO 获取post参数
     * @param $params
     * @param null $request
     * @param bool $suffix
     * @return array
     */
    public static function postMore($params,$request = null,$suffix = false)
    {
        if($request === null) $request = app('request');
        $p = [];
        $i = 0;
        foreach ($params as $param){
            if(!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->param($param);
            }else{
                if(!isset($param[1])) $param[1] = null;
                if(!isset($param[2])) $param[2] = '';
                $name = is_array($param[1]) ? $param[0].'/a' : $param[0];
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $param[0])] = $request->param($name,$param[1],$param[2]);
            }
        }
        return $p;
    }

    /**
     * TODO 获取get参数
     * @param $params
     * @param null $request
     * @param bool $suffix
     * @return array
     */
    public static function getMore($params,$request=null,$suffix = false)
    {
        if($request === null) $request = app('request');
        $p = [];
        $i = 0;
        foreach ($params as $param){
            if(!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->param($param);
            }else{
                if(!isset($param[1])) $param[1] = null;
                if(!isset($param[2])) $param[2] = '';
                $name = is_array($param[1]) ? $param[0].'/a' : $param[0];
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $param[0])] = $request->param($name,$param[1],$param[2]);
            }
        }
        return $p;
    }

    /**
     * 路径转url路径
     * @param $path
     * @param string $http
     * @return string
     */
    public static function pathToUrl($path,$http = 'http')
    {
        $path = trim(str_replace(DS, '/', $path),'.');
        if (0 != strripos($path, 'http')){
            return $path;
        }
        return $http.'://'.$_SERVER['SERVER_NAME'].$path;
    }

    /**
     * url转换路径
     * @param $url
     * @return string
     */
    public static function urlToPath($url)
    {
        $path = trim(str_replace('/',DS,$url),DS);
        if(0 !== strripos($path, 'public'))
            $path = 'public' . DS . $path;
        return app()->getRootPath().$path;
    }

    /**
     * url转换路径
     * @param $url
     * @return string
     */
    public static function urlToName($url)
    {
        if(empty($url))
            return $url;

        if(0 === strripos($url, 'http'))
            $url = str_replace('https://'.$_SERVER['SERVER_NAME'],'',$url);
            $url = str_replace('http://'.$_SERVER['SERVER_NAME'],'',$url);

        $url = str_replace('\\','/',$url);
        return $url;
    }

    /**
     * 时间转换
     * @param $time
     * @return string
     */
    public static function timeTran($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c . $v . '前';
            }
        }
    }

    /**
     * 分级排序
     * @param $data
     * @param int $pid
     * @param string $field
     * @param string $pk
     * @param string $html
     * @param int $level
     * @param bool $clear
     * @return array
     */
    public static function sortListTier($data, $pid = 0, $field = 'pid', $pk = 'id', $html = '|-----', $level = 1, $clear = true)
    {
        static $list = [];
        if ($clear) $list = [];
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $res['html'] = str_repeat($html, $level);
                $list[] = $res;
                unset($data[$k]);
                self::sortListTier($data, $res[$pk], $field, $pk, $html, $level + 1, false);
            }
        }
        return $list;
    }

    /**
     * 分级返回多维数组
     * @param $data
     * @param int $pid
     * @param string $field
     * @param string $pk
     * @param int $level
     * @return array
     */
    public static function getChindNode($data, $pid = 0, $field = 'pid', $pk = 'id', $level = 1)
    {

        static $list = [];
        foreach ($data as $k => $res) {
            if ($res['pid'] == $pid) {
                $list[] = $res;
                unset($data[$k]);
                self::getChindNode($data, $res['id'], $field, $pk, $level + 1);

            }
        }
        return $list;
    }

    /**
     * 分级返回下级所有分类ID
     * @param $data
     * @param $pid
     * @param string $field
     * @param string $pk
     * @return string
     */
    public static function getChildrenPid($data,$pid, $field = 'pid', $pk = 'id')
    {
        static $pids = '';
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $pids .= ','.$res[$pk];
                self::getChildrenPid($data, $res[$pk], $field, $pk);
            }
        }
        return $pids;
    }

    /**
     * 是否为微信内部浏览器
     * @return bool
     */
    public static function isWechatBrowser()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false);
    }

    /**
     * 匿名处理
     * @param $name
     * @return string
     */
    public static function anonymity($name)
    {
        $strLen = mb_strlen($name,'UTF-8');
        $min = 3;
        if($strLen <= 1)
            return '*';
        if($strLen<= $min)
            return mb_substr($name,0,1,'UTF-8').str_repeat('*',$min-1);
        else
            return mb_substr($name,0,1,'UTF-8').str_repeat('*',$strLen-1).mb_substr($name,-1,1,'UTF-8');
    }

    /*
     * 获取当前控制器模型方法组合成的字符串
     * @paran object $request Request 实例化后的对象
     * @retun string
     * */
    public static function getCurrentController()
    {
        return strtolower(request()->app().'/'.request()->controller().'/'.request()->action());
    }

    /**
     * TODO 修改 https 和 http
     * @param $url $url 域名
     * @param int $type  0 返回https 1 返回 http
     * @return string
     */
    public static function setHttpType($url, $type = 0)
    {
        $domainTop = substr($url,0,5);
        if($type){ if($domainTop == 'https') $url = 'http'.substr($url,5,strlen($url)); }
        else{ if($domainTop != 'https') $url = 'https:'.substr($url,5,strlen($url)); }
        return $url;
    }

    /**
     * TODO 修改为站点
     * @param $image
     * @param string $siteUrl
     * @return mixed|string
     */
    public static function setSiteUrl($image, $siteUrl = '')
    {
        if(!strlen(trim($siteUrl)))  $siteUrl = SystemConfigService::get('site_url');
        $domainTop = substr($image,0,4);
        if($domainTop == 'http') return $image;
        $image = str_replace('\\', '/', $image);
        return $siteUrl.$image;
    }

    /**
     * TODO CURL 检测远程文件是否在
     * @param $url
     * @return bool
     */
    public static function CurlFileExist($url)
    {
        $ch = curl_init();
        try{
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $contents = curl_exec($ch);
            if (preg_match("/404/", $contents)) return false;
            if (preg_match("/403/", $contents)) return false;
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * TODO 获取图片转为base64
     * @param string $avatar
     * @return bool|string
     */
    public static function setImageBase64($avatar = '',$timeout=15){
        try{
            $header = array(
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
                'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding: gzip, deflate',);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $avatar);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            $data = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code == 200)  return "data:image/jpeg;base64," . base64_encode($data);
            else return false;
        }catch (\Exception $e){
            return false;
        }
    }


    //分享海报??
    public static function setSharePoster($config = array(), $path){
        $imageDefault = array(
            'left'=>0,
            'top'=>0,
            'right'=>0,
            'bottom'=>0,
            'width'=>100,
            'height'=>100,
            'opacity'=>100
        );
        $textDefault = array(
            'text'=>'',
            'left'=>0,
            'top'=>0,
            'fontSize'=>32,       //字号
            'fontColor'=>'255,255,255', //字体颜色
            'angle'=>0,
        );
        $background = $config['background'];//海报最底层得背景
        $backgroundInfo = getimagesize($background);
        $background = imagecreatefromstring(file_get_contents($background));
        $backgroundWidth = $backgroundInfo[0];  //背景宽度
        $backgroundHeight = $backgroundInfo[1];  //背景高度
        $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
        $color = imagecolorallocate($imageRes, 0, 0, 0);
        imagefill($imageRes, 0, 0, $color);
        imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
        if(!empty($config['image'])){
            foreach ($config['image'] as $key => $val) {
                $val = array_merge($imageDefault,$val);
                $info = getimagesize($val['url']);
                $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
                if($val['stream']){
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                $canvas=imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
                imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
            }
        }
        if(isset($config['text']) && !empty($config['text'])){
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($textDefault,$val);
                list($R,$G,$B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
                imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$val['text']);
            }
        }
        ob_start();
        imagejpeg ($imageRes);
        imagedestroy($imageRes);
        $res = ob_get_contents();
        ob_end_clean();
        $key = $path.substr(md5(rand(0, 9999)) , 0, 5). date('YmdHis') . rand(0, 999999) . '.png';
        header('Content-Type: image/png');
        imagepng($res,$key);

    }

    /**
     * 图片合并
     * 将源图片覆盖到目标图片上
     * @param string $dstPath 目标图片路径
     * @param string $srcPath 源图片路径
     * @param int $dstX 源图片覆盖到目标的X轴坐标
     * @param int $dstY 源图片覆盖到目标的Y轴坐标
     * @param int $srcX
     * @param int $srcY
     * @param int $pct 透明度
     * @param string $filename 输出的文件名，为空则直接在浏览器上输出显示
     * @return string $filename 合并后的文件名
     */
    static public function picMerge($dstPath,$srcPath,$dstX=0,$dstY=0,$srcX=0,$srcY=0,$pct=100,$filename=''){
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dstPath));
        $src = imagecreatefromstring(file_get_contents($srcPath));
        //获取水印图片的宽高
        list($src_w, $src_h) = getimagesize($srcPath);
        //将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
        imagecopymerge($dst, $src, 165, 225, 0, 0, $src_w, $src_h, 100);
        //如果水印图片本身带透明色，则使用imagecopy方法
        //imagecopy($dst, $src, 10, 10, 0, 0, $src_w, $src_h);
        //输出图片
        list($dst_w, $dst_h, $dst_type) = getimagesize($dstPath);
        switch ($dst_type) {
            case 1://GIF
                if(!$filename){
                    header('Content-Type: image/gif');
                    imagegif($dst);
                }else{
                    imagegif($dst,$filename);
                }
                break;
            case 2://JPG
                if(!$filename){
                    header('Content-Type: image/jpeg');
                    imagejpeg($dst);
                }else{
                    imagejpeg($dst,$filename);
                }
                break;
            case 3://PNG
                if(!$filename){
                    header('Content-Type: image/png');
                    imagepng($dst);
                }else{
                    imagepng($dst,$filename);
                }
                break;
            default:
                break;
        }
        imagedestroy($dst);
        imagedestroy($src);
        return $filename;
    }

    /**
     * 添加文字到图片上
     * @param $dstPath 目标图片
     * @param $fontPath 字体路径
     * @param $fontSize 字体大小
     * @param $text 文字内容
     * @param $dstY 文字Y坐标值
     * @param string $filename 输出文件名，为空则在浏览器上直接输出显示
     * @return string 返回文件名
     */
    static public function addFontToPic($dstPath,$fontPath,$fontSize,$text,$dstY,$filename=''){
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dstPath));
        //打上文字
        $fontColor = imagecolorallocate($dst, 0x00, 0x00, 0x00);//字体颜色
        $width = imagesx ( $dst );
        $height = imagesy ( $dst );
        $fontBox = imagettfbbox($fontSize, 0, $fontPath, $text);//文字水平居中实质
        imagettftext ( $dst, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), $dstY, $fontColor, $fontPath, $text);
        //输出图片
        list($dst_w, $dst_h, $dst_type) = getimagesize($dstPath);
        switch ($dst_type) {
            case 1://GIF
                if(!$filename){
                    header('Content-Type: image/gif');
                    imagegif($dst);
                }else{
                    imagegif($dst,$filename);
                }
                break;
            case 2://JPG
                if(!$filename){
                    header('Content-Type: image/jpeg');
                    imagejpeg($dst);
                }else{
                    imagejpeg($dst,$filename);
                }
                break;
            case 3://PNG
                if(!$filename){
                    header('Content-Type: image/png');
                    imagepng($dst);
                }else{
                    imagepng($dst,$filename);
                }
                break;
            default:
                break;
        }
        imagedestroy($dst);
        return $filename;
    }

}