<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use Zsxsoft\W3CValidator\W3CValidator;

class BasicTest extends TestCase
{
    public function testRun()
    {
        $validator = new W3CValidator();
        var_dump($validator->data('<html></html>')->run());
    }
}