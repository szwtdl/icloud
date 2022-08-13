<?php

if (!function_exists('dd')) {
    /**
     * print
     * @param $arr
     * @return void
     */
    function dd($arr)
    {
        echo "<pre>";
        print_r($arr);
        exit();
    }

}