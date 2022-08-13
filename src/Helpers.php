<?php

declare(strict_types=1);

/*
 * This file is part of the szwtdl/icloud.
 *
 * (c) pengjian <szpengjian@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if (!function_exists('dd')) {
    /**
     * print.
     *
     * @param $arr
     */
    function dd($arr)
    {
        echo '<pre>';
        print_r($arr);
        exit;
    }
}

if (!function_exists('format_size')) {
    function format_size($file_size)
    {
        $file_size = $file_size - 1;
        if ($file_size >= 1099511627776) {
            $show_filesize = number_format($file_size / 1099511627776, 2).' TB';
        } elseif ($file_size >= 1073741824) {
            $show_filesize = number_format($file_size / 1073741824, 2).' GB';
        } elseif ($file_size >= 1048576) {
            $show_filesize = number_format($file_size / 1048576, 2).' MB';
        } elseif ($file_size >= 1024) {
            $show_filesize = number_format($file_size / 1024, 2).' KB';
        } elseif ($file_size > 0) {
            $show_filesize = $file_size.' b';
        } elseif (0 == $file_size || -1 == $file_size) {
            $show_filesize = '0 b';
        }

        return $show_filesize;
    }
}
