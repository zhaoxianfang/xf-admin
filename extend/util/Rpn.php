<?php
// +---------------------------------------------------------------------
// | 逆波兰式算法求解
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace util;

/**
 * $exp        = "(a+b)^(0-1)%2-(c*d)/e+f^3";
 * $exp_values = ["a" => 2, "b" => 2, "c" => 3, "d" => 2, "e" => 3, 'f' => 2];
 * $res        = Rpn::calculate($exp, $exp_values);
 */
//将用户输入的表达式转为逆波兰表达式计算
// 求次方 时候，如果次数为负数 的写法 (...)^(0-n) 例如：(a+b)^(0-2)
class Rpn
{

    //正则表达式，用于将表达式字符串，解析为单独的运算符和操作项
    const PATTERN_EXP    = '/((?:[a-zA-Z0-9_]+)|(?:[\(\)\+\-\*\/\^\%])){1}/';
    const EXP_PRIORITIES = ['+' => 1, '-' => 1, '*' => 2, '/' => 2, "(" => 0, ")" => 0, '^' => 3, '%' => 2];

    /**
     * [calculate 计算]
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-14
     * @param    [type]       $exp        [普通表达式，例如 a+b*(c+d)]
     * @param    [type]       $exp_values [表达式对应数据内容，例如 ['a' => 1, 'b' => 2]]
     * @return   [type]                   [description]
     */
    public static function calculate($exp, $exp_values)
    {
        $exp_arr = self::parse_exp($exp); //将表达式字符串解析为列表
        if (!is_array($exp_arr)) {
            return null;
        }
        $output_queue = self::nifix2rpn($exp_arr);
        return self::calculate_value($output_queue, $exp_values);
    }

    //将字符串中每个操作项和预算符都解析出来
    protected static function parse_exp($exp)
    {
        $match = [];
        preg_match_all(self::PATTERN_EXP, $exp, $match);
        if ($match) {
            return $match[0];
        } else {
            return null;
        }
    }

    //将中缀表达式转为后缀表达式
    protected static function nifix2rpn($input_queue)
    {
        $exp_stack    = [];
        $output_queue = [];
        foreach ($input_queue as $input) {
            if (in_array($input, array_keys(self::EXP_PRIORITIES))) {
                if ($input == "(") {
                    array_push($exp_stack, $input);
                    continue;
                }
                if ($input == ")") {
                    $tmp_exp = array_pop($exp_stack);
                    while ($tmp_exp && $tmp_exp != "(") {
                        array_push($output_queue, $tmp_exp);
                        $tmp_exp = array_pop($exp_stack);
                    }
                    continue;
                }
                foreach (array_reverse($exp_stack) as $exp) {
                    if (self::EXP_PRIORITIES[$input] <= self::EXP_PRIORITIES[$exp]) {
                        array_pop($exp_stack);
                        array_push($output_queue, $exp);
                    } else {
                        break;
                    }
                }
                array_push($exp_stack, $input);
            } else {
                array_push($output_queue, $input);
            }
        }
        foreach (array_reverse($exp_stack) as $exp) {
            array_push($output_queue, $exp);
        }
        return $output_queue;
    }

    //传入后缀表达式队列、各项对应值的数组，计算出结果
    protected static function calculate_value($output_queue, $exp_values)
    {
        $res_stack = [];
        foreach ($output_queue as $out) {
            if (in_array($out, array_keys(self::EXP_PRIORITIES))) {
                $a = array_pop($res_stack);
                $b = array_pop($res_stack);
                switch ($out) {
                    case '+':
                        $res = $b + $a;
                        break;
                    case '-':
                        $res = $b - $a;
                        break;
                    case '*':
                        $res = $b * $a;
                        break;
                    case '/':
                        $res = $b / $a;
                        break;
                    case '^': //$a 次方
                        $accumulate     = 1;
                        $positiveNumber = true; //正次方
                        if ($a < 0) {
                            $a              = -$a;
                            $positiveNumber = false;
                        }
                        for ($i = 0; $i < $a; $i++) {
                            $accumulate = $accumulate * $b;
                        }
                        $res = $positiveNumber ? $accumulate : 1 / $accumulate;
                        break;
                    case '%':
                        $res = $b % $a;
                        break;
                }
                array_push($res_stack, $res);
            } else {
                if (is_numeric($out)) {
                    array_push($res_stack, intval($out));
                } else {
                    array_push($res_stack, $exp_values[$out]);
                }
            }
        }
        return count($res_stack) == 1 ? $res_stack[0] : null;
    }
}
