<?php
/**
 * Created by PhpStorm.
 * User: jomlz
 * Date: 2019/8/28
 * Time: 22:14
 */

namespace app\common;

use think\exception\ClassNotFoundException;

class Loader
{
    /**
     * @var array 实例数组
     */
    protected static $instance = [];
    protected $request;

    public static function model($name = '', $layer = 'model', $appendSuffix = false,$module, $common = 'common')
    {
        $uid = $name . $layer;
        if (isset(self::$instance[$uid])) {
            return self::$instance[$uid];
        }

        list($module, $class) = self::getModuleAndClass($name, $layer, $appendSuffix,$module);

        if (class_exists($class)) {
            $model = new $class();
        } else {
            $class = str_replace('\\' . $module . '\\', '\\' . $common . '\\', $class);

            if (class_exists($class)) {
                $model = new $class();
            } else {
                throw new ClassNotFoundException('class not exists:' . $class, $class);
            }
        }

        return self::$instance[$uid] = $model;
    }
    /**
     * 解析模块和类名
     * @access protected
     * @param  string $name         资源地址
     * @param  string $layer        验证层名称
     * @param  bool   $appendSuffix 是否添加类名后缀
     * @return array
     */
    protected static function getModuleAndClass($name, $layer, $appendSuffix,$module)
    {
        if (false !== strpos($name, '\\')) {
            $module = $module;
            $class  = $name;
        } else {
            if (strpos($name, '/')) {
                list($module, $name) = explode('/', $name, 2);
            } else {
                $module = $module;
            }
            $class = self::parseClass($module, $layer, $name, $appendSuffix);
        }
        return [$module, $class];
    }


    /**
     * 解析应用类的类名
     * @access public
     * @param  string $module       模块名
     * @param  string $layer        层名 controller model ...
     * @param  string $name         类名
     * @param  bool   $appendSuffix 是否添加类名后缀
     * @return string
     */
    public static function parseClass($module, $layer, $name, $appendSuffix = false)
    {
        $suffix = false;
        $array = explode('\\', str_replace(['/', '.'], '\\', $name));
        $class = self::parseName(array_pop($array), 1);
        $class = $class . ($suffix || $appendSuffix ? ucfirst($layer) : '');
        $path  = $array ? implode('\\', $array) . '\\' : '';

        return 'app' . '\\' .
            ($module ? $module . '\\' : '') .
            $layer . '\\' . $path . $class;
    }

    /**
     * 字符串命名风格转换
     * type 0 将 Java 风格转换为 C 的风格 1 将 C 风格转换为 Java 的风格
     * @access public
     * @param  string  $name    字符串
     * @param  integer $type    转换类型
     * @param  bool    $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }

}