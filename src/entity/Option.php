<?php
/**
 * Option.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/22 17:51
 */

declare (strict_types=1);

namespace app\admin\entity;

use app\admin\common\exception\AdminException;
use support\base\BaseEntity;

class Option extends BaseEntity
{
    /**
     * 获取字典
     * @param $name - 字典名
     * @return mixed|null
     * @author cdyun(121625706@qq.com)
     */
    public function get($name): mixed
    {
        $value = $this->where('name', $this->dictNameToOptionName($name))->value('value');
        return $value ? json_decode($value, true) : null;
    }

    /**
     * 字典名到option名转换
     * @param string $name - 字典名
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function dictNameToOptionName(string $name): string
    {
        return "dict_$name";
    }


    /**
     * option名到字典名转换
     * @param string $name
     * @return string
     */
    public function optionNameToDictName(string $name): string
    {
        return substr($name, 5);
    }

    /**
     * 保存字典
     * @param $name - 字典名
     * @param $value - 值
     * @return true
     * @author cdyun(121625706@qq.com)
     */
    public function doSaveDict($name, $value): bool
    {
        if (!preg_match('/[a-zA-Z]/', $name)) {
            throw new AdminException('字典名只能包含字母');
        }
        $option_name = $this->dictNameToOptionName($name);
        try {
            $option = $this->where('name', $option_name)->find();
            $fmtValue = $this->filterDictValue($value);

            // 附件上传字典，保留字段值为0，作为默认站点分类
            if ($name === 'upload'){
                $newArr = [];
                foreach ($fmtValue as $item){
                    if ($item['value'] == '0' && $item['name'] == '站点'){
                        continue;
                    }
                    if (!empty($item['ext']) && !preg_match('/[a-zA-Z,]/', $item['ext'])){
                        throw new AdminException('upload字典中其他字段只能包含字母和英文逗号');
                    }
                    $item['ext'] = mb_strtolower($item['ext']);

                    $newArr[] = $item;
                }
                $values = array_column($newArr, 'value');
                if (in_array('0', $values)){
                    throw new \Exception('附件upload字典中字段值不能为0');
                }
                $fmtValue = $newArr;
            }
            if ($option) {
                $option->value = json_encode($fmtValue, JSON_UNESCAPED_UNICODE);
                $option->save();
            } else {
                $this->name = $option_name;
                $this->value = json_encode($fmtValue, JSON_UNESCAPED_UNICODE);
                $this->save();
            }
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }

        return true;
    }

    /**
     * 过滤字典值
     * @param array $values - 值
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function filterDictValue(array $values): array
    {
        $format_values = [];
        foreach ($values as $item) {
            if (!isset($item['value']) || !isset($item['name']) || !isset($item['ext'])) {
                throw new AdminException('字典格式错误');
            }
            $format_values[] = ['value' => $item['value'], 'name' => $item['name'], 'ext' => $item['ext']];
        }
        return $format_values;
    }

    public function getSystemConfig()
    {
        $name = 'system_config';
        $rs = $this->where('name', $name)->find();
        if ($rs && !empty($rs->value)) {
            return json_decode($rs->value, true);
        }
        $config = file_get_contents(root_path('public/static/admin/config') . 'pear.config.json');
        if ($config === false) {
            error('读取配置文件失败');
        }
        if (!$rs) {
            $this->name = $name;
            $this->value = $config;
            $this->save();
        } else {
            $rs->value = $config;
            $rs->save();
        }
        return json_decode($config, true);

    }

    /**
     * 获取配置
     * @param $name
     * @return mixed
     * @author cdyun(121625706@qq.com)
     */
    public function getOptionConfig($name): mixed
    {
        if (!str_starts_with($name, 'config_')) {
            throw new AdminException('要获取的配置名称错误');
        }
        try {
            $rs = $this->where('name', $name)->find();
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        if ($rs) {
            if ($name === 'config_system') {
                if (empty($rs->value)) {
                    $content = file_get_contents(root_path('public/static/admin/config') . 'pear.config.json');
                    if ($content === false) {
                        throw new AdminException('读取配置文件失败');
                    }
                    $rs->value = json_encode(json_decode($content, true), JSON_UNESCAPED_UNICODE);
                    $rs->save();
                }

            }
            return json_decode($rs->value, true);
        }
        $config = '{}';
        if ($name === 'config_system') {
            $content = file_get_contents(root_path('public/static/admin/config') . 'pear.config.json');
            if ($content === false) {
                throw new AdminException('读取配置文件失败');
            }
            $config = json_encode(json_decode($content, true), JSON_UNESCAPED_UNICODE);
        }
        $this->save([
            'name' => $name,
            'value' => $config
        ]);
        return json_decode($config, true);
    }
}