<?php
// +---------------------------------------------------------------------
// | 系统公共配置
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\model;

use think\Model;

/**
 * 配置模型
 */
class Config extends Model
{
    // 表名,不含前缀
    protected $name = 'config';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    /**
     * 本地上传配置信息
     * @return array
     */
    public static function upload()
    {
        $uploadcfg = config('upload');

        $upload = [
            'cdnurl'    => $uploadcfg['cdnurl']?$uploadcfg['cdnurl']:'',
            'uploadurl' => $uploadcfg['uploadurl'],
            'bucket'    => 'local',
            'maxsize'   => $uploadcfg['maxsize'],
            'mimetype'  => $uploadcfg['mimetype'],
            'multipart' => [],
            'multiple'  => $uploadcfg['multiple'],
        ];
        return $upload;
    }

    /**
     * 读取配置类型
     * @return array
     */
    public static function getTypeList()
    {
        $typeList = [
            'string'   => '字符',
            'text'     => '文本',
            'editor'   => '编辑器',
            'number'   => '数字',
            'date'     => '日期',
            'time'     => '时间',
            'datetime' => '日期时间',
            'select'   => '列表',
            'selects'  => '列表(多选)',
            'image'    => '图片',
            'images'   => '图片(多)',
            'file'     => '文件',
            'files'    => '文件(多)',
            'checkbox' => '复选',
            'radio'    => '单选',
            'array'    => '数组',
            'custom'   => 'Custom',
        ];
        return $typeList;
    }

    public static function getArrayData($data)
    {
        $fieldarr = $valuearr = [];
        $field = isset($data['field']) ? $data['field'] : [];
        $value = isset($data['value']) ? $data['value'] : [];
        foreach ($field as $m => $n)
        {
            if ($n != '')
            {
                $fieldarr[] = $field[$m];
                $valuearr[] = $value[$m];
            }
        }
        return $fieldarr ? array_combine($fieldarr, $valuearr) : [];
    }

    /**
     * 读取分类分组列表
     * @return array
     */
    public static function getGroupList()
    {
        $groupList = config('site.configgroup');
        // foreach ($groupList as $k => &$v)
        // {
        //     $v = __($v);
        // }
        return $groupList;
    }

    /**
     * 将字符串解析成键值数组
     * @param string $text
     * @return array
     */
    public static function decode($text, $split = "\r\n")
    {
        $content = explode($split, $text);
        $arr = [];
        foreach ($content as $k => $v)
        {
            if (stripos($v, "|") !== false)
            {
                $item = explode('|', $v);
                $arr[$item[0]] = $item[1];
            }
        }
        return $arr;
    }

    /**
     * 将键值数组转换为字符串
     * @param array $array
     * @return string
     */
    public static function encode($array, $split = "\r\n")
    {
        $content = '';
        if ($array && is_array($array))
        {
            $arr = [];
            foreach ($array as $k => $v)
            {
                $arr[] = "{$k}|{$v}";
            }
            $content = implode($split, $arr);
        }
        return $content;
    }

}
