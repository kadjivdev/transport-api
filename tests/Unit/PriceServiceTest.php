<?php

use App\Services\PriceService;
use Tests\TestCase;

class PriceServiceTest extends TestCase
{
    public  function test_discount_is_applied_correctly()
    {
        $priceService = new PriceService();
        $result = $priceService->applayAccompt(100, 10);

        $this->assertEquals(90, $result, "The discount is applied successfuly...");
    }
}
