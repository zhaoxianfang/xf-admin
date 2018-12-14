<?php
namespace util;

// +----------------------------------------------------------------------
// | 自动闭合html标签 以及清洗html
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

/**
 * 使用
 * CloseHtml::instance($str)->output();
 * CloseHtml::instance($str)->delHtmlTag()->output();
 * CloseHtml::instance($str)->delHtmlTag(['img','a'])->output();
 * CloseHtml::instance()->input($str)->delHtmlTag(['img','a'])->output();
 */
class CloseHtml
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    protected $inputHtmlStr  = '';
    protected $outputHtmlStr = '';
    //“合法”的单闭合标签
    protected $singleTagArray = array(
        'meta',
        'link',
        'base',
        'br',
        'hr',
        'input',
        'img',
    );

    public function __construct($str = '')
    {
        if ($str) {
            $this->inputHtmlStr = $str;
            $this->run();
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return CloseHtml
     */
    public static function instance($htmlStr = '')
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($htmlStr);
        }
        return self::$instance;
    }

    //接收需要处理的html字符串
    public function input($htmlStr = '')
    {
        $this->inputHtmlStr = $htmlStr;
        $this->run();

        return $this;
    }

    //返回处理后的字符串
    public function output()
    {
        return $this->outputHtmlStr;
    }

    /**
     * [delHtmlTag 删除html标签]
     * @Author   ZhaoXianFang
     * @DateTime 2018-11-20
     * @param    array        $exclude   [排除清洗的标签]
     * @param    array        $thorough  [是否需要彻底清洗 是：清洗后为存文本；否:只去除js和css ]
     * @return   [type]                  [description]
     */
    public function delHtmlTag($exclude = array('img'), $thorough = false)
    {

        $strtemp = trim($this->outputHtmlStr);
        $search  = array(
            "|<script[^>].*?</script>|Uis", // 去掉 javascript
            "|<style[^>].*?</style>|Uis", // 去掉 style
        );
        $replace = array(
            "",
            "",
        );
        $text = preg_replace($search, $replace, $strtemp);
        if ($thorough) {
            //排除处理的标签
            $excludeTags = '';
            if ($exclude) {
                foreach ($exclude as $key => $tag) {
                    $excludeTags .= '<' . $tag . '>';
                }
            }
            $this->outputHtmlStr = strip_tags($text, $excludeTags);
        } else {
            $this->outputHtmlStr = $text;
        }
        return $this;
    }

    private function run()
    {

        empty($this->inputHtmlStr) && $this->inputHtmlStr = '';
        $str_len                                          = strlen($this->inputHtmlStr);

        //记录起始标签
        $pre_data = array();
        //记录起始标签位置
        $pre_pos    = array();
        $last_data  = array();
        $error_data = array();
        $error_pos  = array();

        $i = 0;

        //标记为  <  开始
        $start_flag = false;
        while ($i < $str_len) {
            if ($this->inputHtmlStr[$i] == "<" && $this->inputHtmlStr[$i + 1] != '/' && $this->inputHtmlStr[$i + 1] != '!') {
                $i++;
                $_tmp_str = '';
                //标记为 < 开始
                $start_flag = true;
                //标记空白
                $space_flag = false;
                while ($this->inputHtmlStr[$i] != ">" && $this->inputHtmlStr[$i] != "'" && $this->inputHtmlStr[$i] != '"' && $this->inputHtmlStr[$i] != '/' && $i < $str_len) {
                    if ($this->inputHtmlStr[$i] == ' ') {
                        $space_flag = true;
                    }
                    if (!$space_flag) {
                        $_tmp_str .= $this->inputHtmlStr[$i];
                    }
                    $i++;
                }
                $pre_data[] = $_tmp_str;
                $pre_pos[]  = $i;
            } else if ($this->inputHtmlStr[$i] == "<" && $this->inputHtmlStr[$i + 1] == '/') {
                $i += 2;
                $_tmp_str = '';
                while ($this->inputHtmlStr[$i] != ">" && $i < $str_len) {
                    $_tmp_str .= $this->inputHtmlStr[$i];
                    $i++;
                }
                $last_data[] = $_tmp_str;
                //查看开始标签的上一个值
                if (count($pre_data) > 0) {
                    $last_pre_node = $this->getLastNode($pre_data, 1);
                    if ($last_pre_node == $_tmp_str) {
                        //配对上, 删除对应位置的值
                        array_pop($pre_data);
                        array_pop($pre_pos);
                        array_pop($last_data);
                    } else {
                        //没有配对上， 有两种情况
                        //情况一： 只有闭合标签， 没有开始标签
                        //情况二：只有开始标签， 没有闭合标签
                        array_pop($last_data);
                        $error_data[] = $_tmp_str;
                        $error_pos[]  = $i;
                    }
                } else {
                    array_pop($last_data);
                    $error_data[] = $_tmp_str;
                    $error_pos[]  = $i;
                }
            } elseif ($this->inputHtmlStr[$i] == "<" && $this->inputHtmlStr[$i + 1] == "!") {
                $i++;
                while ($i < $str_len) {
                    if ($this->inputHtmlStr[$i] == "-" && $this->inputHtmlStr[$i + 1] == "-" && $this->inputHtmlStr[$i + 2] == ">") {
                        $i++;
                        break;
                    } else {
                        $i++;
                    }
                }
                $i++;
            } elseif ($this->inputHtmlStr[$i] == '/' && $this->inputHtmlStr[$i + 1] == '>') {
                //跳过自动单个闭合标签
                if ($start_flag) {
                    array_pop($pre_data);
                    array_pop($pre_pos);
                    $i += 2;
                }
            } elseif ($this->inputHtmlStr[$i] == "/" && $this->inputHtmlStr[$i + 1] == "*") {
                $i++;
                while ($i < $str_len) {
                    if ($this->inputHtmlStr[$i] == "*" && $this->inputHtmlStr[$i + 1] == "/") {
                        $i++;
                        break;
                    } else {
                        $i++;
                    }
                    $i++;
                }
            } elseif ($this->inputHtmlStr[$i] == "'") {
                $i++;
                while ($this->inputHtmlStr[$i] != "'" && $i < $str_len) {
                    $i++;
                }
                $i++;
            } else if ($this->inputHtmlStr[$i] == '"') {
                $i++;
                while ($this->inputHtmlStr[$i] != '"' && $i < $str_len) {
                    $i++;
                }
                $i++;
            } else {
                $i++;
            }
        }

        $this->sort_data($pre_data, $pre_pos, $error_data, $error_pos);
        $this->outputHtmlStr = $this->modify_data($this->inputHtmlStr, $pre_data, $pre_pos, $error_data, $error_pos);
    }

    //确定起始标签的位置
    private function confirm_pre_pos($str, $pre_pos)
    {
        $str_len = strlen($str);
        $j       = $pre_pos;
        while ($j < $str_len) {
            if ($str[$j] == '"') {
                $j++;
                while ($j < $str_len) {
                    if ($str[$j] == '"') {
                        $j++;
                        break;
                    }
                    $j++;
                }
            } elseif ($str[$j] == "'") {
                $j++;
                while ($j < $str_len) {
                    if ($str[$j] == "'") {
                        $j++;
                        break;
                    }
                    $j++;
                }
            } elseif ($str[$j] == ">") {
                $j++;
                while ($j < $str_len) {
                    if ($str[$j] == "<") {
                        //退回到原有内容位置
                        $j--;
                        break;
                    }
                    $j++;
                }
                break;
            } else {
                $j++;
            }
        }
        return $j;
    }

    //确定起始标签的位置
    private function confirm_err_pos($str, $err_pos)
    {
        $j = $err_pos;
        $j--;
        while ($j > 0) {
            if ($str[$j] == '"') {
                $j--;
                while ($j < $str_len) {
                    if ($str[$j] == '"') {
                        $j--;
                        break;
                    }
                    $j--;
                }
            } elseif ($str[$j] == "'") {
                $j--;
                while ($j < $str_len) {
                    if ($str[$j] == "'") {
                        $j--;
                        break;
                    }
                    $j--;
                }
            } elseif ($str[$j] == ">") {
                $j++;
                break;
            } else {
                $j--;
            }
        }
        return $j;
    }

    //获取数组的倒数第num个值
    private function getLastNode(array $arr, $num)
    {
        $len = count($arr);
        if ($len > $num) {
            return $arr[$len - $num];
        } else {
            return $arr[0];
        }
    }

    //整理数据， 主要是向后看， 进一步进行检查
    private function sort_data(&$pre_data, &$pre_pos, &$error_data, &$error_pos)
    {
        $rem_key_array = array();
        $rem_i_array   = array();
        //获取需要删除的值
        foreach ($error_data as $key => $value) {
            $count = count($pre_data);
            for ($i = ($count - 1); $i >= 0; $i--) {
                if ($pre_data[$i] == $value && !in_array($i, $rem_i_array)) {
                    $rem_key_array[] = $key;
                    $rem_i_array[]   = $i;
                    break;
                }
            }
        }

        //删除起始标签相应的值
        foreach ($rem_key_array as $_item) {
            unset($error_pos[$_item]);
            unset($error_data[$_item]);
        }

        //删除结束标签相应的值
        foreach ($rem_i_array as $_item) {
            unset($pre_data[$_item]);
            unset($pre_pos[$_item]);
        }
    }

    //整理数据， 闭合标签
    private function modify_data($str, $pre_data, $pre_pos, $error_data, $error_pos)
    {

        $move_log = array();
        //只有闭合标签的数据
        foreach ($error_data as $key => $value) {
            $_tmp_move_count = 0;
            foreach ($move_log as $pos_key => $move_value) {
                # code...
                if ($error_pos[$key] >= $pos_key) {
                    $_tmp_move_count += $move_value;
                }
            }

            $data                   = $this->insert_data($str, $value, $error_pos[$key] + $_tmp_move_count, false);
            $str                    = $data['str'];
            $move_log[$data['pos']] = $data['move_count'];
        }

        //只有起始标签的数据
        foreach ($pre_data as $key => $value) {
            $_tmp_move_count = 0;
            foreach ($move_log as $pos_key => $move_value) {
                if ($pre_pos[$key] >= $pos_key) {
                    $_tmp_move_count += $move_value;
                }
            }

            $data                   = $this->insert_data($str, $value, $pre_pos[$key] + $_tmp_move_count, true);
            $str                    = $data['str'];
            $move_log[$data['pos']] = $data['move_count'];
        }
        return $str;

    }

    //插入数据， $type 表示插入数据的方式
    private function insert_data($str, $insert_data, $pos, $type)
    {
        $isAutoClose = false;
        if (in_array($insert_data, $this->singleTagArray)) {
            $isAutoClose = true;
        }
        $len = strlen($str);
        //起始标签类型
        if ($type == true) {
            if ($isAutoClose) {
                $tempStr = substr($str, $pos, 50);
                $nextTag = strpos($tempStr, '<'); //下一个标签开始位置
                $tagEnd  = strpos($tempStr, '>'); //下一个标签结束位置
                if ($tagEnd < $nextTag) {
                    $pos = $pos + $tagEnd;
                }
                $resStr     = substr_replace($str, "/>", $pos, 1);
                $pre_str    = $resStr;
                $mid_str    = $end_str    = '';
                $move_count = 1;

            } else {
                $move_count = strlen($insert_data) + 3;
                $pos        = $this->confirm_pre_pos($str, $pos);
                $pre_str    = substr($str, 0, $pos);
                $end_str    = substr($str, $pos);
                $mid_str    = "</" . $insert_data . ">";
            }

            //闭合标签类型
        } else {
            $pos        = $this->confirm_err_pos($str, $pos);
            $move_count = strlen($insert_data) + 2;
            $pre_str    = substr($str, 0, $pos);
            $end_str    = substr($str, $pos);
            $mid_str    = "<" . $insert_data . ">";
        }

        $str = $pre_str . $mid_str . $end_str;
        return array('str' => $str, 'pos' => $pos, 'move_count' => $move_count);
    }
}
