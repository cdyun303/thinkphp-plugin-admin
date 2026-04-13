<?php
/**
 * Node.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/29 20:36
 */

declare (strict_types=1);

namespace app\admin\common;

use app\admin\common\exception\AdminException;
use app\admin\entity\AdminNode;
use Cdyun\PhpTool\Dir;
use ReflectionClass;

class Node
{
    /**
     * 节点分组注解
     */
    protected const NODEGROUP = 'app\\admin\\common\\annotation\\NodeGroup';
    /**
     * 节点项注解
     */
    protected const NODEITEM = 'app\\admin\\common\\annotation\\NodeItem';

    /**
     * 导入节点
     * @param string $appName - 应用名称
     * @return true
     * @throws \ReflectionException
     * @author cdyun(121625706@qq.com)
     */
    public static function import(string $appName): bool
    {
        $entity = new AdminNode();
        $keys = $entity->where('app_name', $appName)->column('key');

        $keysInFiles = [];

        $path = realpath(base_path($appName) . config('route.controller_layer'));
        $files = Dir::scanFile($path, ['php'], true);
        $classFiles = array_map(function ($file) use ($appName, $path) {
            $file = str_replace(['.php', $path . DIRECTORY_SEPARATOR], '', $file);
            return 'app\\' . $appName . '\\controller\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $file);
        }, $files);
        foreach ($classFiles as $classFile) {
            $reflectionClass = new ReflectionClass($classFile);
            $class = $reflectionClass->getName();
            $pid = 0;
            // 先获取分组注解
            $groupAttributes = $reflectionClass->getAttributes(self::NODEGROUP);
            if (!empty($groupAttributes)) {
                $groupNode = $groupAttributes[0]->newInstance()->getData();
                $groupNode['title'] = !empty($groupNode['title']) ? $groupNode['title'] : $class;
                $groupNode['key'] = !empty($groupNode['key']) ? $groupNode['key'] : $class;
                $groupNode['type'] = !empty($groupNode['type']) ? $groupNode['type'] : 1;
                $groupNode['is_menu'] = !empty($groupNode['is_menu']) ? $groupNode['is_menu'] : true;
                $groupNode['pid'] = $pid;
                $groupNode['app_name'] = $appName;
                $pid = self::save($groupNode);
                $keysInFiles[] = $groupNode['key'];
            }
            // 获取类注解
            $itemAttributes = $reflectionClass->getAttributes(self::NODEITEM);
            if (!empty($itemAttributes)) {
                $itemNode = $itemAttributes[0]->newInstance()->getData();
                $itemNode['title'] = !empty($itemNode['title']) ? $itemNode['title'] : $class;
                $itemNode['key'] = !empty($itemNode['key']) ? $itemNode['key'] : $class;
                $itemNode['type'] = !empty($itemNode['type']) ? $itemNode['type'] : 2;
                $itemNode['is_menu'] = !empty($itemNode['is_menu']) ? $itemNode['is_menu'] : true;
                $itemNode['pid'] = $pid;
                $itemNode['app_name'] = $appName;
                $pid = self::save($itemNode);
                $keysInFiles[] = $itemNode['key'];
            }

            $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                $methodAttributes = $method->getAttributes(self::NODEITEM);
                if (!empty($methodAttributes)) {
                    $method_name = $method->getName();
                    if (strtolower($method_name) === 'index' || str_starts_with($method_name, '__')) {
                        continue;
                    }
                    $name = "$class@$method_name";
                    $methodNode = $methodAttributes[0]->newInstance()->getData();

                    $methodNode['title'] = !empty($methodNode['title'])
                        ? $methodNode['title']
                        : (self::getCommentFirstLine($method->getDocComment()) ?: $method_name);
                    $methodNode['key'] = !empty($methodNode['key']) ? $methodNode['key'] : $name;
                    $methodNode['type'] = !empty($methodNode['type']) ? $methodNode['type'] : 3;
                    $methodNode['is_menu'] = !empty($methodNode['is_menu']) ? $methodNode['is_menu'] : false;
                    $methodNode['pid'] = $pid;
                    $methodNode['app_name'] = $appName;
                    self::save($methodNode);
                    $keysInFiles[] = $methodNode['key'];
                }
            }
        }

        // 从数据库中删除已经不存在的方法
        $deleteNodes = array_diff($keys, $keysInFiles);
        if (!empty($deleteNodes)) {
            $entity->destroy(
                function ($query) use ($appName, $deleteNodes) {
                    $query->where('app_name', $appName)->whereIn('key', $deleteNodes);
                }
            );
        }
        return true;
    }

    /**
     * 保存节点信息
     * @param array $data
     * @return mixed
     * @author cdyun(121625706@qq.com)
     */
    public static function save(array $data): mixed
    {
        $entity = new AdminNode();
        try {
            $node = $entity->where('key', $data['key'])->find();
            if ($node) {
                $node->title = $data['title'];
                $node->type = $data['type'];
                $node->pid = $data['pid'];
                $node->weight = $data['weight'];
                $node->is_menu = $data['is_menu'];
                $node->href = $data['href'];
                $node->save();
                return $node->id;
            }
            $rs = $entity->create($data);
            return $rs->id;
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 获取注释中第一行
     * @param $comment
     * @return false|string
     */
    protected static function getCommentFirstLine($comment): bool|string
    {
        if ($comment === false) {
            return false;
        }
        foreach (explode("\n", $comment) as $str) {
            if ($s = trim($str, "*/\ \t\n\r\0\x0B")) {
                return $s;
            }
        }
        return $comment;
    }

}