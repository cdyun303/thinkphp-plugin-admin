<?php
/**
 * Upload.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/24 15:44
 */

declare (strict_types=1);

namespace Thinkphp\Admin\entity;

use Thinkphp\Admin\exception\AdminException;
use Cdyun\ThinkphpUpload\UploadEnforcer;
use support\base\BaseEntity;
use think\facade\Db;
use think\File;
use function app\admin\entity\config;

class Upload extends BaseEntity
{

    /**
     * 上传文件
     * @param array $data
     * @return \StdClass
     * @author cdyun(121625706@qq.com)
     */
    public function doMoveUpload(array $data): \StdClass
    {
        try {
            $file = $data['file'];
            if (!$file || !$file->isValid()) {
                throw new \Exception('未找到上传文件');
            }
            $size = $file->getSize();
            $mine = $file->getMime();
            $ext = strtolower($file->extension());
            $path = $data['path'] ?? '';
            if (empty($path)) {
                $path = $this->getUploadPath($ext);
            }
            $upload = new UploadEnforcer();
            $upload = $upload->to($path);
            if (isset($data['name'])) {
                $upload = $upload->name($data['name']);
            }
            if (isset($data['validate'])) {
                $upload = $upload->validate($data['validate']);
            }

            // 执行上传操作
            $result = $upload->move($file);

            // 保存上传记录
            $this->doUploadSave([
                'ext' => $ext,
                'name' => $result->fileName,
                'url' => $result->originLink,
                'file_size' => $size,
                'mime_type' => $mine,
                'category' => !empty($data['category']) ? (int)$data['category'] : 0,
            ]);
            return $result;
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 转换上传路径
     * @param string $ext - 文件后缀
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    protected function getUploadPath(string $ext): string
    {
        // 图片
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
            return 'images';
        }
        // 视频
        if (in_array($ext, ['mp4', 'avi', 'wmv', 'mov', 'flv', 'mkv', 'webm'])) {
            return 'videos';
        }
        // 音频
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'wma', 'aac', 'flac', 'm4a', 'wma', 'aiff', 'ape', 'amr', 'midi', 'mid'])) {
            return 'audios';
        }
        // 文档
        if (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'md', 'csv'])) {
            return 'documents';
        }
        // 其他
        return config('cdyun.upload.path', 'files');
    }

    /**
     *  保存上传记录
     * @param array $data
     * @return bool
     * @author cdyun(121625706@qq.com)
     */
    protected function doUploadSave(array $data): bool
    {
        try {
            $data['storage'] = config('filesystem.default', 'local');
            $data['admin_id'] = admin('id');
            return $this->save($data);
        } catch (\Exception $e) {
            throw new AdminException('上传记录存储失败');
        }
    }

    /**
     * 删除文件
     * @param $id - 文件ID
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function doDeleteFile($id): void
    {
        try {
            $file = $this->find($id);
            if (!$file) {
                throw new AdminException('未找到上传文件');
            }

            // 删除存储桶文件
            $upload = new UploadEnforcer();
            $upload->delete($file->url);

            // 删除数据库记录
            $file->delete();
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
    }

}