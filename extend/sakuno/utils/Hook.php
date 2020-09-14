<?php
/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'                                   Created by PhpStorm.
 *                .::::::::::                                    User: SakunoRyoma QQ3079714
 *           '::::::::::::::..                                   Time: 2020/8/10 20:22
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
namespace sakuno\utils;

use think\helper\Str;

/**
 * 行为钩子
 * Class Hook
 * @package sakuno\utils
 */
class Hook
{
    /**
     * 类名
     * @var string
     */
    protected $namespace;

    /**
     * 方法前缀
     * @var string
     */
    protected $prefix;

    /**
     * 构造方法
     * Hook constructor.
     * @param string $namespace
     * @param string|null $prefix
     */
    public function __construct(string $namespace, string $prefix = null)
    {
        $this->namespace = $namespace;
        if ($prefix) {
            $this->prefix = $prefix;
        }
    }

    /**
     * 执行挂载方法
     * @param string $hookName
     * @param mixed ...$arguments
     * @return bool
     */
    public function listen(string $hookName, ...$arguments)
    {
        if (class_exists($this->namespace)) {
            $handle = new $this->namespace;
            $hookName = Str::studly(($this->prefix ?: '') . ucfirst($hookName));
            if (method_exists($handle, $hookName)) {
                try {
                    return $handle->{$hookName}(...$arguments);
                } catch (\Throwable $e) {
                }
            }
        }
        return false;
    }

}
