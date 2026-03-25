<?php
/**
 * AdminUser.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 14:46
 */

declare (strict_types=1);

namespace app\admin\validate;

use support\base\BaseValidate;

class ConfigValidate extends BaseValidate
{
    protected $rule = [
        'data' => 'verifyUrlPath',
        'max' => 'number',
        'index.id' => 'number',
        'index.href' => 'verifyUrlPath',
        'defaultColor' => 'in:1,2,3,4,5',
    ];
    protected $message = [
        'data.verifyUrlPath' => '菜单url不合法',
        'max.number' => '最大标签数仅支持数字',
        'index.id.number' => '主标签ID仅支持数字',
        'index.href.verifyUrlPath' => '主标签URL不合法',
        'defaultColor.in' => '主题颜色仅支持1-5',
    ];
    protected $scene = [
        'edit' => ['data', 'max', 'index.id', 'index.href', 'defaultColor'],
    ];

    /**
     * 检测是否是合法URL Path
     * @param $value
     * @return bool|string
     * @author cdyun(121625706@qq.com)
     */
    protected function verifyUrlPath($value): bool|string
    {
        if (!is_string($value)) return false;
        if (str_starts_with($value, 'https://') || str_starts_with($value, 'http://')) {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                return false;
            }
        }
        if (!preg_match('/^[a-zA-Z0-9_\-\/&?.]+$/', $value)) {
            return false;
        }
        return true;
    }

}