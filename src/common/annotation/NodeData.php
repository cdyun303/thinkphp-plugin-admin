<?php
/**
 * NodeData.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/29 21:58
 */

declare (strict_types=1);

namespace app\admin\common\annotation;

abstract class NodeData
{
    /**
     * 标题
     * @var string
     */
    public string $title;
    /**
     * 标识 - 数据表中唯一
     * @var string
     */
    public string $key;
    /**
     * 图标
     * @var string
     */
    public string $icon;
    /**
     * 类型:1:目录 2:菜单 3:权限
     * @var int
     */
    public int $type;
    /**
     * 排序权重
     * @var int
     */
    public int $weight;
    /**
     * url
     * @var string
     */
    public string $href;
    /**
     * 是否菜单显示
     * @var bool
     */
    public bool $is_menu;

    public function __construct(array $data = [])
    {
        $this->title = !empty($data['title']) ? $data['title'] : '';
        $this->key = !empty($data['key']) ? $data['key'] : '';
        $this->icon = !empty($data['icon']) ? $data['icon'] : '';
        $this->type = !empty($data['type']) ? $data['type'] : 0;
        $this->weight = !empty($data['weight']) ? $data['weight'] : 0;
        $this->href = !empty($data['href']) ? $data['href'] : '';
        $this->is_menu = !empty($data['is_menu']) ? $data['is_menu'] : false;
    }

    /**
     * 获取数据
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function getData(): array
    {
        return [
            'title' => $this->title,
            'key' => $this->key,
            'icon' => $this->icon,
            'type' => $this->type,
            'weight' => $this->weight,
            'href' => $this->href,
            'is_menu' => $this->is_menu,
        ];
    }
}