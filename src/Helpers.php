<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/icloud
 * @link     https://www.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/icloud/blob/master/LICENSE
 */
if (! function_exists('dd')) {
    function dd($arr)
    {
        echo '<pre>';
        print_r($arr);
        exit;
    }
}

if (! function_exists('analyzePhones')) {
    function analyzePhones(string $text): array
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($text);
        $text = $crawler->filterXPath('//script[7]')->html();
        $text = json_decode($text, true);
        $phoneInfo = $text['direct']['twoSV']['phoneNumberVerification']['trustedPhoneNumbers'];
        $result = [];
        foreach ($phoneInfo as $value) {
            $result[] = [
                'id' => $value['id'],
                'last' => $value['lastTwoDigits'],
                'value' => html_entity_decode($value['numberWithDialCode']),
            ];
        }
        return $result;
    }
}

if (! function_exists('getEscape')) {
    function getEscape($filename)
    {
        if (empty($filename)) {
            return '';
        }
        $dirname = dirname($filename);
        $filename = basename($filename);
        $arr = [
            '#' => '%23',
        ];
        foreach ($arr as $key => $item) {
            $filename = str_replace($key, $item, $filename);
        }
        return str_replace('\\', '/', $dirname . DIRECTORY_SEPARATOR . $filename);
    }
}

if (! function_exists('getExtension')) {
    function getExtension(string $name): string
    {
        $extensions = ['zip', 'xlsx', 'xls', 'wps', 'txt', 'tif', 'tar', 'swf', 'rp', 'rm', 'rar', 'psd', 'psb', 'ppt', 'png', 'pdf', 'mp4', 'mp3', 'mov', 'keynote', 'jpg', 'jpeg', 'html', 'gif', 'folder', 'flv', 'ext', 'eps', 'docx', 'doc', 'csv', 'bmp', 'avi', 'ai', '7z'];
        if (in_array(\strtolower($name), $extensions)) {
            return $name;
        }
        return 'empty';
    }
}
if (! function_exists('format_size')) {
    function format_size($file_size): string
    {
        $file_size = $file_size - 1;
        if ($file_size >= 1099511627776) {
            $show_filesize = number_format($file_size / 1099511627776, 2) . ' TB';
        } elseif ($file_size >= 1073741824) {
            $show_filesize = number_format($file_size / 1073741824, 2) . ' GB';
        } elseif ($file_size >= 1048576) {
            $show_filesize = number_format($file_size / 1048576, 2) . ' MB';
        } elseif ($file_size >= 1024) {
            $show_filesize = number_format($file_size / 1024, 2) . ' KB';
        } elseif ($file_size > 0) {
            $show_filesize = $file_size . ' b';
        } elseif ($file_size == 0 || $file_size == -1) {
            $show_filesize = '0 b';
        }
        return $show_filesize;
    }
}
