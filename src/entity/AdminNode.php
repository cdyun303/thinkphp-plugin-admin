<?php
/**
 * AdminNode.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 23:43
 */

declare (strict_types=1);

namespace app\admin\entity;

use support\base\BaseEntity;

class AdminNode extends BaseEntity
{
    /**
     * 移除空数组
     * @param array $menus
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function empty_filter(array $menus): array
    {
        return array_map(
            function ($menu) {
                if (isset($menu['children'])) {
                    $menu['children'] = $this->empty_filter($menu['children']);
                }
                return $menu;
            },
            array_values(array_filter(
                $menus,
                function ($menu) {
                    return $menu['type'] != 0 || isset($menu['children']) && count($this->empty_filter($menu['children'])) > 0;
                }
            ))
        );
    }

    /**
     * 移除不包含某些数据的数组
     * @param array $array - 数组
     * @param string $key - 键
     * @param array $values - 值
     * @author cdyun(121625706@qq.com)
     */
    public function removeNotContain(array &$array, string $key, array $values): void
    {
        foreach ($array as $k => &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!$this->arrayContain($item, $key, $values)) {
                unset($array[$k]);
            } else {
                if (!isset($item['children'])) {
                    continue;
                }
                $this->removeNotContain($item['children'], $key, $values);
            }
        }
    }

    /**
     * 判断数组是否包含某些数据
     * @param $array - 数组
     * @param string $key - 键
     * @param array $values - 值
     * @return bool
     * @author cdyun(121625706@qq.com)
     */
    protected function arrayContain(&$array, string $key, array $values): bool
    {
        if (!is_array($array)) {
            return false;
        }
        if (isset($array[$key]) && in_array($array[$key], $values)) {
            return true;
        }
        if (empty($array['children'])) {
            return false;
        }
        foreach ($array['children'] as $item) {
            if ($this->arrayContain($item, $key, $values)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 将节点Key转换成权限码
     * @param string $key
     * @return false|string
     * @author cdyun(121625706@qq.com)
     */
    public function getNodePermission(string $key): bool|string
    {
        $key = strtolower($key);
        $action = '';
        if (strpos($key, '@')) {
            [$key, $action] = explode('@', $key, 2);
        }
        $appDirName = 'app';
        if (!str_starts_with($key, $appDirName . '\\')) {
            return false;
        }
        if (str_ends_with($key, 'controller')) {
            $key = substr($key, 0, -strlen('controller'));
        }
        $paths = explode('\\', $key);
        if (count($paths) < 2) {
            return false;
        }
        $controllerLayer = config('route.controller_layer', 'controller');
        foreach ($paths as $index => $path) {
            if ($path === $controllerLayer) {
                unset($paths[$index]);
            }
            if ($path === $appDirName) {
                unset($paths[$index]);
            }
        }
        $code = implode('/', $paths);
        return $action ? "$code/$action" : $code;
    }
}