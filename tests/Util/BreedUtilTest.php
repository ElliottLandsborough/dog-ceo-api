<?php
// tests/Util/BreedUtilTest.php
namespace App\Tests\Util;

use App\Util\BreedUtil;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function __construct()
    {
        $this->util = new BreedUtil();
        $this->util->disableCache();
    }

    /*
    public function testCollapseArrayWithString()
    {
        $twoDimensionArray = [
            'item1' => [],
            'item2' => [],
            'item3' => [
                'item4',
                'item5',
            ],
            'item6' => [],
        ];
        $collapsedArray = $this->util->collapseArrayWithString($array);
    }
    */
}