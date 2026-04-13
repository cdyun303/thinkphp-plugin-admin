<?php
/**
 * NodeGroup.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/29 16:22
 */

namespace app\admin\common\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class NodeGroup extends NodeData
{
}