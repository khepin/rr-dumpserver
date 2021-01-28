<?php declare(strict_types=1);

use Khepin\RRDumpServer\RRDumper;

if (!function_exists('rrdump')) {
    function rrdump($variable) : string {
        return RRDumper::i()->dump($variable);
    }
}
