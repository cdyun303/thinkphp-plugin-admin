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
     * @param $menus
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function empty_filter($menus): array
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
     * @param $array - 数组
     * @param $key - 键
     * @param $values - 值
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function removeNotContain(&$array, $key, $values): void
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
     * @param $key - 键
     * @param $values - 值
     * @return bool
     * @author cdyun(121625706@qq.com)
     */
    protected function arrayContain(&$array, $key, $values): bool
    {
        if (!is_array($array)) {
            return false;
        }
        if (isset($array[$key]) && in_array($array[$key], $values)) {
            return true;
        }
        if (!isset($array['children'])) {
            return false;
        }
        foreach ($array['children'] as $item) {
            if ($this->arrayContain($item, $key, $values)) {
                return true;
            }
        }
        return false;
    }

}