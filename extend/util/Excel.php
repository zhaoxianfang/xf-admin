<?php
// +----------------------------------------------------------------------
// | Excel 处理类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace util;

use PHPExcel;
use PHPExcel_IOFactory;
use think\Exception;

class Excel
{
    protected static $instance;
    //默认配置
    protected $config = [
        'filePath'  => '', //加载的文件路径
        'data'      => [], //需要处理的数组数据
        'fileName'  => '', //下载时候的文件名称
        'headTitle' => '', //下载时候的文件内容标题名称
        'dirName'   => 'public', //上传文件对应的目录名称
        /**
         * 文件保存路径格式
         */
        'savekey'   => '/uploads/{year}/{mon}/{day}/{filemd5}{.suffix}',
        /**
         * 最大可上传大小
         */
        'maxsize'   => '10mb',
        /**
         * 可上传的文件类型
         */
        'mimetype'  => 'jpg,png,bmp,jpeg,gif,zip,rar,xls,xlsx,csv',
        /**
         * 是否支持批量上传
         */
        'multiple'  => false,
    ];
    public $options = [];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Tree
     */
    public static function init($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 添加需要处理的数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-08
     * @param    string       $arr [数组数据]
     */
    public function addData($arr = '')
    {
        if (is_array($arr) && !empty($arr)) {
            $this->options['data'] = $this->options['data'] ? array_merge($this->options['data'], $arr) : $arr;
        }
        return $this;
    }

    /**
     * 设置文件名称
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-08
     * @param    string       $fileName [下载文件时候显示的文件名称]
     */
    public function setFilename($fileName = '')
    {
        if ($fileName) {
            $this->options['fileName'] = $fileName;
        }
        return $this;
    }

    /**
     * 设置文件内容标题
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-08
     * @param    string       $headTitle [文件内容标题]
     */
    public function setHeadTitle($headTitle = '')
    {
        if ($headTitle) {
            $this->options['headTitle'] = $headTitle;
        }
        return $this;
    }

    /**
     * 设置上传文件的文件夹 相对于跟目录
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-08
     * @param    string       $dirName [文件夹名称]
     */
    public function setUploadDir($dirName = '')
    {
        if ($dirName) {
            $this->options['dirName'] = $dirName;
        }
        return $this;
    }

    /**
     * 把excel文件转换为数组 同过上传文件名 （不能为ajax 上传）
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-07
     * @param    string       $uploadVariableName [上传表单时候接受excel文件的变量名]
     */
    public function getExcelToArray($uploadVariableName = '')
    {
        if (!$uploadVariableName) {
            throw new Exception('未设置接收Excel的变量名称.');
        }
        // 读取excel文件
        try {
            $excel       = request()->file($uploadVariableName)->getInfo();
            $objPHPExcel = \PHPExcel_IOFactory::load($excel['tmp_name']); //读取上传的文件
            $arrExcel    = $objPHPExcel->getSheet(0)->toArray(); //获取其中的数据
        } catch (\Exception $e) {
            throw new Exception("加载Excel文件发生错误：" . $e->getMessage());
        }
        return $arrExcel;
    }

    /**
     * 把excel文件转换为数组 同过文件路径读取
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-07
     * @param    string       $excelfilepath [excel 文件路径]
     * @param    string       $defaultPaht   [相对根路径 文件夹]
     * @param    string       $destroyFile   [文件内容获取之后是否删除文件，默认删除]
     *
     *
     * __WEBDIR__ 在tp5 中 定义define('__WEBDIR__', realpath (dirname(__FILE__).'/../'));
     * realpath(__WEBDIR__ . '/public/' .
     */
    public function getExcelToArrayByFilePath($excelfilepath = '', $defaultPaht = "", $destroyFile = true)
    {
        $excelfilepath = $excelfilepath ? $excelfilepath : $this->options['filePath'];
        if (!$excelfilepath) {
            throw new Exception('找不到上传文件.');
        }
        $defaultPaht = $defaultPaht ? $defaultPaht : $this->options['dirName'];
        $filePath    = realpath(ROOT_PATH . '/' . $defaultPaht . '/' . $excelfilepath);
        if (!file_exists($filePath)) {
            throw new Exception('找不到Excel文件或者上传文件已经失效，请重新上传');
        }
        // 读取excel文件
        try {
            $objPHPExcel = \PHPExcel_IOFactory::load($filePath); //读取上传的文件
            $arrExcel    = $objPHPExcel->getSheet(0)->toArray(); //获取其中的数据
        } catch (\Exception $e) {
            throw new Exception("加载Excel文件发生错误：" . $e->getMessage());
        }
        if ($destroyFile) {
            //销毁文件
            $this->destroyFile($filePath);
        }
        return $arrExcel;
    }

    /**
     * 赵先方
     * 把数组生成到EXCEL文件中
     * @param  [type] $data       [必须||要生成的数组数据]
     * @param  string $filename   [可选||生成的文件名]
     * @param  string $head_title [可选||表头]
     * @param  string $title      [可选||shell表的表名]
     * @return [type]             [description]
     */
    public function arrayToExcel($data = array(), $filename = '', $head_title = '', $title = '表1')
    {
        $data = $data ? array_merge($this->options['data'], $data) : $this->options['data'];
        if (!is_array($data) || empty($data)) {
            throw new Exception('数据不能为空.');
        }
        ini_set('max_execution_time', '0');
        $filename = $filename ? $filename : ($this->options['fileName'] ? $this->options['fileName'] : time());

        $filename   = $filename . '.xls';
        $phpexcel   = new PHPExcel();
        $head_title = $head_title ? $head_title : ($this->options['headTitle'] ? $this->options['headTitle'] : '');
        if ($head_title) {
            $char  = "A";
            $first = count(reset($data)); //判断传入数组是否为一维
            $num   = ($first > 1) ? ($first - 1) : (count($data) - 1);
            ($first > 1) ? (array_unshift($data, (array) $head_title)) : ($data = array('0' => (array) $head_title, '1' => $data));
            while ($num) {
                $char++;
                $num--;
            }
            //合并cell
            $phpexcel->getActiveSheet()->mergeCells('A1:' . $char . '1');
            //        array_unshift($data,(array)$head_title);
            //居中
            $phpexcel->getActiveSheet()->getStyle('A1:' . $char . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        }
        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle($title);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        ob_clean(); //关键
        flush(); //关键
        $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');

    }

    /**
     * 数组生成文件
     * @Author   ZhaoXianFang
     * @DateTime 2018-04-24
     * @param    array        $data       [description]
     * @param    string       $filename   [description]
     * @param    string       $head_title [description]
     * @param    string       $title      [description]
     * @return   [type]                   [description]
     */
    public function arrayToFile($data = array(), $filename = '', $head_title = '', $title = '表1')
    {
        $data = $data ? array_merge($this->options['data'], $data) : $this->options['data'];
        if (!is_array($data) || empty($data)) {
            throw new Exception('数据不能为空.');
        }
        ini_set('max_execution_time', '0');
        $filename = $filename ? $filename : ($this->options['fileName'] ? $this->options['fileName'] : time());

        $phpexcel   = new PHPExcel();
        $head_title = $head_title ? $head_title : ($this->options['headTitle'] ? $this->options['headTitle'] : '');
        if ($head_title) {
            $char  = "A";
            $first = count(reset($data)); //判断传入数组是否为一维
            $num   = ($first > 1) ? ($first - 1) : (count($data) - 1);
            ($first > 1) ? (array_unshift($data, (array) $head_title)) : ($data = array('0' => (array) $head_title, '1' => $data));
            while ($num) {
                $char++;
                $num--;
            }
            //合并cell
            $phpexcel->getActiveSheet()->mergeCells('A1:' . $char . '1');
            //居中
            $phpexcel->getActiveSheet()->getStyle('A1:' . $char . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        }
        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle($title);
        // 设置第一个sheet为工作的sheet
        $phpexcel->setActiveSheetIndex(0);
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        ob_clean(); //关键
        flush(); //关键

        $filename = $filename . '.xlsx';
        // $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');//xls
        $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007'); //xlsx
        $objwriter->save($filename);
    }

    /**
     * 销毁文件
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-08
     * @param    [type]       $filePath [文件路径]
     * @return   [type]                 [description]
     */
    public function destroyFile($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception('找不到指定文件.');
            }
            $destroyResult = @unlink($filePath);
            if ($destroyResult == false) {
                throw new Exception('删除文件失败.' . $filePath);
            } else {
                return true;
            }
        } catch (Exception $e) {
            // dump($e->getMessage());
            return false;
        }
    }

    /**
     * 将带表头的普通数据处理为标准数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-08
     * @param    array        $dataArr       [待处理数据,下标为0 的数据为表头数据]
     * @param    array        $standard      [处理标准] 例如 ['title'=>'标题','content'=>'内容',...]
     * @param    array        $destroy       [删除不符合规范的数据,目前未使用该值]
     * @param    array        $ignoreColumn  [是否忽略多余列]
     * @return   [data]                      [处理之后的数据]
     * @return   [errNum]                    [不在模板范围内的数据条数（列）]
     */
    public function arrToStandardArr($dataArr = array(), $standard = array(), $ignoreColumn = true, $destroy = false)
    {
        if (!$dataArr || !$standard || !is_array($dataArr) || !is_array($standard)) {
            throw new Exception('数据不规范');
        }
        $kayArr    = []; //被替换的键值名称[0=>'title',1=>'name',……] 例如 a[0=>'111']替换为a['title'=>'111']
        $errNum    = 0; //不符合规范的列数量
        $errColumn = ''; //不符合规范的列名
        $dataTitle = [];
        // 匹配表头
        foreach ($dataArr[0] as $key => $title) {
            if (in_array($title, $standard)) {
                $kayArr[$key]             = array_search($title, $standard);
                $dataTitle[$kayArr[$key]] = $title; //表头
            } else {
                $kayArr[$key] = $key;
                $errNum++;
                $errColumn .= $errColumn ? "、" . $title : $title;
            }
        }
        array_shift($dataArr); //移除表头

        foreach ($dataArr as $key => &$value) {
            foreach ($value as $k => $v) {
                $value[$kayArr[$k]] = $v;
                unset($value[$k]); //删除不在模板范围内的数据
            }
        }

        //判断表头是否完整
        $titleDiffColumn = array_diff($standard, $dataTitle);
        $diff_column     = '';
        if ($titleDiffColumn) {
            foreach ($titleDiffColumn as $key => $value) {
                $diff_column .= $diff_column ? "、" . $value : $value;
            }
        }
        if ($errNum > 0 || $diff_column) {
            if ($diff_column) {
                throw new Exception('您上传的文件中缺失【' . $diff_column . '】列的数据');
            }
            //是否忽略excel 中的多余字段
            if (!$ignoreColumn) {
                throw new Exception('您上传的文件中有[' . $errNum . ']列【' . $errColumn . '】不在导入标准之中');
            }
        }
        //data 处理之后的数据 errNum 不在模板范围内的数据条数（列）
        //diff_column 缺失的列
        return ['data' => $dataArr, 'err_num' => $errNum, 'err_column' => $errColumn, 'diff_column' => $diff_column, 'excel_title' => $dataTitle];
    }

    /**
     * [uploadFile 处理文件上传]
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-13
     * @param    array        $option [文件上传的参数]
     * @return   [type]               [description]
     */
    public function uploadFile($option = [])
    {
        config('default_return_type', 'json');
        $file = request()->file('file');
        if (empty($file)) {
            throw new \Exception('没有文件上传或服务器上传限制超出');
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = $this->options;

        if (!empty($option)) {
            $upload = array_merge($upload, $option);
        }

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type     = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size     = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix   = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix   = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr     = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            throw new \Exception('上传文件不是指定的格式');
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName  = substr($savekey, strripos($savekey, '/') + 1);

        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                $imgInfo     = getimagesize($splInfo->getPathname());
                $imagewidth  = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $params = array(
                'filesize'    => $fileInfo['size'],
                'imagewidth'  => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $fileInfo['type'],
                'url'         => $uploadDir . $splInfo->getSaveName(),
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
            );
            return $params;
        } else {
            // 上传失败获取错误信息
            throw new \Exception($file->getError());
        }
    }

    /**
     * [valiField 验证字段]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-12
     * @param    string       $valArr        [被验证的数组]
     * @param    [type]       $valiFieldRule [使用的验证规则]
     * @param    string       $type          [验证类型]
     * @return   [type]                      [description]
     *           type类型说明  in_array      是否在数组中
     */
    // public static function valiField($valArr = '',$valiFieldRule,$type='in_array')
    public static function valiField()
    {
        dump(__WEBDIR__);
        dump(ROOT_PATH);die;
        foreach ($variable as $key => $value) {
            # code...
        }
    }
}
