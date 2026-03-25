<?php
/**
 * ConfigController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 22:02
 */

declare (strict_types=1);

namespace Thinkphp\Admin\controller\core;

use Thinkphp\Admin\controller\AdminBaseController;
use Thinkphp\Admin\entity\Option;
use Thinkphp\Admin\exception\AdminException;
use Thinkphp\Admin\validate\ConfigValidate;
use function app\admin\controller\core\app_domain_url;
use function app\admin\controller\core\success;
use function app\admin\controller\core\validate_data;

class ConfigController extends AdminBaseController
{
    protected string $systemConfig = 'config_system';

    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch('config/index');
    }

    /**
     * 获取系统配置
     * @return mixed
     * @author cdyun(121625706@qq.com)
     */
    public function get(): mixed
    {
        $entity = new Option();
        $config = $entity->getOptionConfig($this->systemConfig);
        if (!empty($config['logo']['image'])){
            $config['logo']['image'] = app_domain_url($config['logo']['image'], true);
        }
        return $config;
    }


    /**
     *  编辑
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function edit(): void
    {
        $params = $this->request->post();
        $entity = new Option();
        $config = $entity->getOptionConfig($this->systemConfig);
        $data = [];
        foreach ($params as $section => $item) {
            if (!isset($config[$section])) {
                continue;
            }
            validate_data($item, ConfigValidate::class, 'edit');
            switch ($section) {
                case 'logo':
                    $data[$section]['title'] = htmlspecialchars($item['title'] ?? '');
                    $data[$section]['image'] = app_domain_url($item['image'], false);
                    $data[$section]['icp'] = htmlspecialchars($item['icp'] ?? '');
                    $data[$section]['beian'] = htmlspecialchars($item['beian'] ?? '');
                    $data[$section]['footer_txt'] = htmlspecialchars($item['footer_txt'] ?? '');
                    break;
                case 'menu':
                    $data[$section]['data'] = $item['data'] ?? '';
                    $data[$section]['accordion'] = !empty($item['accordion']);
                    $data[$section]['collapse'] = !empty($item['collapse']);
                    $data[$section]['control'] = !empty($item['control']);
                    $data[$section]['controlWidth'] = (int)($item['controlWidth'] ?? 2000);
                    $data[$section]['select'] = (int)$item['select'] ?? 0;
                    $data[$section]['async'] = true;
                    break;
                case 'tab':
                    $data[$section]['enable'] = true;
                    $data[$section]['keepState'] = !empty($item['keepState']);
                    $data[$section]['preload'] = !empty($item['preload']);
                    $data[$section]['session'] = !empty($item['session']);
                    $data[$section]['max'] = $item['max'] ?? '30';
                    $data[$section]['index']['id'] = $item['index']['id'] ?? '0';
                    $data[$section]['index']['href'] = $item['index']['href'] ?? '';
                    $data[$section]['index']['title'] = htmlspecialchars($item['index']['title'] ?? '首页');
                    break;
                case 'theme':
                    $data[$section]['defaultColor'] = $item['defaultColor'] ?? '2';
                    $data[$section]['defaultMenu'] = $item['defaultMenu'] ?? '' == 'dark-theme' ? 'dark-theme' : 'light-theme';
                    $data[$section]['defaultHeader'] = $item['defaultHeader'] ?? '' == 'dark-theme' ? 'dark-theme' : 'light-theme';
                    $data[$section]['allowCustom'] = !empty($item['allowCustom']);
                    $data[$section]['banner'] = !empty($item['banner']);
                    break;
                case 'colors':
                    foreach ($config['colors'] as $index => $vo) {
                        if (!isset($item[$index])) {
                            $config['colors'][$index] = $vo;
                            continue;
                        }
                        $data_item = $item[$index];
                        $data[$section][$index]['id'] = $index + 1;
                        $data[$section][$index]['color'] = $this->filterColor($data_item['color'] ?? '');
                        $data[$section][$index]['second'] = $this->filterColor($data_item['second'] ?? '');
                    }
                    break;

            }
        }
        if (empty($data)) {
            success();
        }
        $config = array_merge($config, $data);
        $entity->where('name', $this->systemConfig)->update([
            'value' => json_encode($config)
        ]);
        success();
    }

    /**
     * 颜色检查
     * @param string $color
     * @return string
     */
    protected function filterColor(string $color): string
    {
        if (!preg_match('/\#[a-zA-Z]6/', $color)) {
            throw new AdminException('参数错误');
        }
        return $color;
    }
}