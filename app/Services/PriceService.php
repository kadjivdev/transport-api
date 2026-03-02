<?php

namespace App\Services;

class PriceService
{
    public function applayAccompt($price, $percent)
    {
        return $price - ($price * $percent/100);
    }
}
