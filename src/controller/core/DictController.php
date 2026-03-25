<?php
/**
 * DictController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/22 17:46
 */

declare (strict_types=1);

namespace Thinkphp\Admin\controller\core;

use Thinkphp\Admin\controller\AdminBaseController;
use Thinkphp\Admin\entity\Option;
use function app\admin\controller\core\error;
use function app\admin\controller\core\paginate;
use function app\admin\controller\core\success;

class DictController extends AdminBaseController
{

    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch('dict/index');
    }

    /**
     * 查询
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function list(): void
    {
        $name = $this->request->get('name', '');
        $page = (int)$this->request->get('page', 1);
        $limit = (int)$this->request->get('limit', 10);
        $offset = ($page - 1) * $limit;

        $searchValue = $name && is_string($name) ? "dict_$name%" : 'dict_%';
        $entity = new Option();
        $query = $entity->where('name', 'like', $searchValue);
        $items = $query->limit($offset, $limit)->select();
        foreach ($items as &$item) {
            $item['name'] = $entity->optionNameTodictName($item['name']);
        }

        paginate($items->toArray(), $query->count());
    }

    /**
     * 获取
     * @param string $name - 字典名称
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function get(string $name): void
    {
        $entity = new Option();
        $result = $entity->get($name);

        // 附件上传字典添加一个默认的类别
        if ($result && $name === 'upload') {
            array_unshift($result, ['name' => '站点', 'value' => 0, 'ext' => '']);
        }
        success('操作成功', $result);
    }

    /**
     * 新增
     * @return string
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function create(): string
    {
        if ($this->request->isAjax()) {
            $name = $this->request->post('name', '');
            $entity = new Option();
            if ($entity->get($name)) {
                error('字典已经存在');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                error('字典名称只能是字母数字下划线的组合');
            }
            $values = (array)$this->request->post('value', []);
            $entity->doSaveDict($name, $values);
            success();
        }
        return $this->fetch('dict/create');
    }

    /**
     * 编辑
     * @return string
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function edit(): string
    {
        if ($this->request->isAjax()) {
            $name = $this->request->post('name', '');
            $entity = new Option();
            if (!$entity->get($name)) {
                error('字典不存在');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                error('字典名称只能是字母数字下划线的组合');
            }
            $values = (array)$this->request->post('value', []);
            $entity->doSaveDict($name, $values);
            success();
        }
        return $this->fetch('dict/edit');
    }

    /**
     * 删除
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function delete(): void
    {
        $names = $this->request->post('name', []);
        if (empty($names)) {
            error('参数错误');
        }
        $entity = new Option();
        $names = is_array($names) ? $names : [$names];
        foreach ($names as $index => $name) {
            $names[$index] = $entity->dictNameToOptionName($name);
        }
        $entity->destroy(
            function ($query) use ($names) {
                $query->whereIn('name', $names);
            }
        );
        success();
    }
}