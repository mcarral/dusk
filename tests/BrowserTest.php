<?php

use Laravel\Dusk\Page;
use Laravel\Dusk\Browser;
use Mockery as m;

class BrowserTest extends PHPUnit_Framework_TestCase
{
    public function test_visit()
    {
        $driver = m::mock(StdClass::class);
        $driver->shouldReceive('navigate->to')->with('http://laravel.dev/login');
        $browser = new Browser($driver);
        Browser::$baseUrl = 'http://laravel.dev';

        $browser->visit('/login');
    }

    public function test_visit_with_page_object()
    {
        $driver = m::mock(StdClass::class);
        $driver->shouldReceive('navigate->to')->with('http://laravel.dev/login');
        $browser = new Browser($driver);
        Browser::$baseUrl = 'http://laravel.dev';

        $browser->visit($page = new BrowserTestPage);

        $this->assertEquals(['@modal' => '#modal'], $browser->resolver->elements);
        $this->assertEquals($page, $browser->page);
        $this->assertTrue($page->asserted);
    }

    public function test_on_method_sets_current_page()
    {
        $driver = m::mock(StdClass::class);
        $browser = new Browser($driver);
        Browser::$baseUrl = 'http://laravel.dev';

        $page = m::mock(BrowserTestPage::class)->makePartial();
        $page->shouldReceive('assert')->with(Mockery::on(function(Browser $browser){
            $this->assertEquals(['@modal' => '#modal'], $browser->resolver->elements);
        }))->once();

        $browser->on($page);

        $this->assertEquals(['@modal' => '#modal'], $browser->resolver->elements);
        $this->assertEquals($page, $browser->page);
        $this->assertTrue($page->asserted);
    }

    public function test_refresh_method()
    {
        $driver = m::mock(StdClass::class);
        $driver->shouldReceive('navigate->refresh')->once();
        $browser = new Browser($driver);

        $browser->refresh();
    }

    public function test_with_method()
    {
        $driver = m::mock(StdClass::class);
        $browser = new Browser($driver);

        $browser->with('prefix', function ($browser) {
            $this->assertInstanceof(Browser::class, $browser);
            $this->assertEquals('body prefix', $browser->resolver->prefix);
        });
    }

    public function test_with_method_with_page()
    {
        $driver = m::mock(StdClass::class);
        $driver->shouldReceive('navigate->to')->with('http://laravel.dev/login');
        $browser = new Browser($driver);
        Browser::$baseUrl = 'http://laravel.dev';

        $browser->visit($page = new BrowserTestPage);

        $browser->with('prefix', function ($browser) use ($page) {
            $this->assertInstanceof(Browser::class, $browser);
            $this->assertEquals('body prefix', $browser->resolver->prefix);
            $this->assertEquals($page, $browser->page);
        });
    }

    public function test_page_macros()
    {
        $driver = m::mock(StdClass::class);
        $driver->shouldReceive('navigate->to')->with('http://laravel.dev/login');
        $browser = new Browser($driver);
        Browser::$baseUrl = 'http://laravel.dev';

        $browser->visit($page = new BrowserTestPage);
        $browser->doSomething();

        $this->assertTrue($browser->page->macroed);
    }
}


class BrowserTestPage extends Page
{
    public $asserted = false;
    public $macroed = false;

    public function assert(Browser $browser)
    {
        $this->asserted = true;
    }

    public function url()
    {
        return '/login';
    }

    public function doSomething()
    {
        $this->macroed = true;
    }

    public static function siteElements()
    {
        return ['@modal' => '#modal'];
    }
}
