<?php

namespace verbb\workflow\tests\unit\controllers;

use verbb\workflow\controllers\ReviewsController;
use PHPUnit\Framework\TestCase;

class ReviewsControllerTest extends TestCase
{
    public function testCompareWithLiveRouteExists()
    {
        $controller = new ReviewsController('reviews', null);
        
        // Test that the method exists
        $this->assertTrue(method_exists($controller, 'actionCompareWithLive'));
    }

    public function testCompareSelectorRouteExists()
    {
        $controller = new ReviewsController('reviews', null);
        
        // Test that the method exists
        $this->assertTrue(method_exists($controller, 'actionCompareSelector'));
    }

    public function testCompareCustomRouteExists()
    {
        $controller = new ReviewsController('reviews', null);
        
        // Test that the method exists
        $this->assertTrue(method_exists($controller, 'actionCompareCustom'));
    }
}