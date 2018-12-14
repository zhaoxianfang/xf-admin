<?php
// +---------------------------------------------------------------------+
// | 爬虫管理    | [ 昆明信息港 ]    [远程截图]                             |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                   |
// +---------------------------------------------------------------------+

namespace app\admin\controller\example;

use app\admin\controller\AdminBase;
use zxf\JonnyW\PhantomJs\Client;

use zxf\JonnyW\PhantomJs\DependencyInjection\ServiceContainer;

class Printscreen extends AdminBase
{
    protected $noNeedRight = [];
    protected $noNeedLogin = [];

    /**
     * 权限控制控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();

    }

    //判断操作系统类型
    protected function isWinOs()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? true : false;
    }

    public function index()
    {
        //获取权限列表
        return $this->fetch();
    }

    /**
     * 执行远程截图
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-25
     * @return   [type]       [description]
     */
    public function screen()
    {
        $row = $this->request->param('row/a');

        $ext  = $row['file_ext'] ? $row['file_ext'] : 'jpg';
        $outType = $ext == 'pdf'?'file':'image';//输出类型文件或者图片
        $link = $row['link'];
        // $link = 'http://www.baidu.com';
        if (!$link) {
            $this->jump([1, '未填写url地址']);
        }
        $delay           = $row['delay'] ? $row['delay'] : 5;
        $backgroundColor = $row['background_color']?$row['background_color']:'';
        if ($row['pdf_width'] && $row['pdf_height']) {
            $pdfPaperSize = array($row['pdf_width'] . 'cm', $row['pdf_height'] . 'cm');
        } else {
            $pdfPaperSize = '';
        }
        if ($row['img_width'] && $row['img_height'] && $row['img_top'] && $row['img_left']) {

            $captureDimensions = array($row['img_width'], $row['img_height'], $row['img_top'], $row['img_left']);
        } else {
            $captureDimensions = '';
        }
        $pdfHeader = $row['pdf_header'];
        $pdfFooter = $row['pdf_footer'];
        //PDF纸张边距
        $pdfMargin = "1cm";


        // //生成的文件类型后缀 图片(jpg,png)或者 pdf
        // $ext = 'jpg';
        // //目标地址
        // $link   = 'https://image.baidu.com/';
        // $delay   = 3; //设置延迟时间 秒
        // // $savePath = 'E:/www/zxf_test/download/file1101.png';
        // // $savePath = PUBLIC_PATH.'uploads'.DS.date("Ymd",time()).DS.time().'.'.$ext;
        // $savePath = PUBLIC_PATH . 'uploads/' . date("Ymd", time()) . '/' . time() . '.' . $ext;
        // // 设置背景色
        // $backgroundColor = '#ff0000';
        //PDF纸张边距
        // $pdfMargin = "1cm";
        // //PDF纸张大小
        // $pdfPaperSize = array('10cm', '20cm');
        // //是否设置截图大小 //设置截图宽高与边距$width, $height, $top, $left
        // $captureDimensions= array(240, 320, 10, 20);
        // //pdf文件的头信息也尾信息
        // $pdfHeader = 'Header';
        // $pdfFooter = 'Footer';

        $pathName = PUBLIC_PATH . 'uploads/screen/' . date("Ymd", time());
        if (!file_exists($pathName)) {
            mkdir($pathName, 0777, true);
            // echo '创建文件夹bookcover成功';
        }

        $savePath = $pathName . '/' . time() . '.' . $ext;
        

        if ($this->isWinOs()) {
            //windows OS
            $softPath = PUBLIC_PATH . 'bin' . DS . 'phantomjs.exe';
            $sys_ds = '/';
            $savePath = strtr($savePath, '\\', $sys_ds); 
        } else {
            //linux OS
            $softPath = PUBLIC_PATH . 'bin' . DS . 'phantomjs';
            
        }

        $client = Client::getInstance();
        $client->getEngine()->setPath($softPath);
        //上面一行要填写自己的phantomjs路径\ linux 换为 二进制文件

        $request = $client->getMessageFactory()->createPdfRequest($link, 'GET'); //参数里面的数字5000是网页加载的超时时间，放在网络问题一直加载，可以不填写，默认5s。

        // $request->setTimeout($delay+2);//超过指定时间则中断渲染
        $request->setDelay($delay); //设置延迟5秒 设置delay是因为有一些特效会在页面加载完成后加载，没有等待就会漏掉

        /*截图(图或PDF文件)*/
        $request->setRepeatingHeader('<h1>' . $pdfHeader . ' <span style="float:right">%pageNum% / %pageTotal%</span></h1>', 100); //自定义PDF类的头尾及其高度
        $request->setRepeatingFooter('<footer>' . $pdfFooter . ' <span style="float:right">%pageNum% / %pageTotal%</span></footer>', 100); //自定义PDF类的头尾
        $request->setViewportSize(200, 100); //设置可视宽高
        if ($backgroundColor) {
            $request->setBodyStyles(array('backgroundColor' => $backgroundColor)); //设置纸张背景色
        }
        $request->setFormat('A4'); //设置尺寸格式,如A4
        //
        // $request->setOrientation('landscape'); //设置纸张方向如纵向
        $request->setOrientation('portrait'); //设置纸张方向如横向

        if ($pdfPaperSize) {
            // $request->setPaperSize('10cm', '20cm'); //PDF纸张大小
            $request->setPaperSize($pdfPaperSize[0], $pdfPaperSize[1]); //PDF纸张大小
        }
        if ($pdfMargin) {
            $request->setMargin($pdfMargin); //PDF纸张边距
        }
        $request->setOutputFile($savePath); //截图或PDF存储路径
        //是否设置截图大小
        if ($captureDimensions) {
            // $request->setCaptureDimensions(240, 320, 10, 20); //设置截图宽高与边距$width, $height, $top, $left
            $request->setCaptureDimensions($captureDimensions[0], $captureDimensions[1], $captureDimensions[2], $captureDimensions[3]); //设置截图宽高与边距$width, $height, $top, $left
        }

        $response = $client->getMessageFactory()->createResponse();

        // Send the request
        $client->send($request, $response);

        /*响应结果*/
        // $headers = $response->getHeaders(); //返回头组成的数组
        // $response->getHeader();//返回头
        $status = $response->getStatus(); //返回状态码:200则正确,其余错误.
        // $content     = $response->getContent(); //返回正文
        // $contentType = $response->getContentType(); //返回正文类型
        // $url         = $response->getUrl(); //返回请求地址
        // $redirectUrl = $response->getRedirectUrl(); //返回重定向后的地址
        // $redirect    = $response->isRedirect(); //返回是否重定向
        // $console     = $response->getConsole(); //返回JS控制台内容

        if ($status == 200 || $status == 302) {
            //截图成功
            // dump('ok');
            // $this->jump([0,'成功']);
            
            $fileUrl = '/'.strstr($savePath, 'uploads');

            return json(['code'=>1,'url'=>$fileUrl,'msg'=>'成功','type'=>$outType]);
        } else {
            //截图失败
            // $this->jump([1,'失败']);
            return json(['code'=>0,'url'=>'','msg'=>'失败'.$status]);
        }
    }

    public function linuxTest()
    {
        /*正常实例*/
        $client = Client::getInstance();//实例
         $softPath = PUBLIC_PATH . 'bin' . DS . 'phantomjs';
        // $client->getEngine()->setPath('/usr/www/myweb/public/bin/phantomjs');
        $client->getEngine()->setPath($softPath);
        $file = '/usr/www/myweb/public/uploads/abc1.pdf';
         // $client = Client::getInstance();
    
        // $client = Client::getInstance();
        // $client->getEngine()->setPath('/usr/local/bin/phantomjs');

        /**
         * @see JonnyW\PhantomJs\Http\Request
         * 不能使用百度的http，百度自动跳转到https会导致生成pdf失败
         **/
        $request = $client->getMessageFactory()->createPdfRequest('https://www.baidu.com', 'GET');

        $request->setOutputFile($file);
        $request->setFormat('A4');
        $request->setOrientation('landscape');
        $request->setMargin('1cm');

        /**
         * @see JonnyW\PhantomJs\Http\Response
         **/
        $response = $client->getMessageFactory()->createResponse();

        // Send the request
        $client->send($request, $response);

        header('location:'.$file);
    }
   

}
