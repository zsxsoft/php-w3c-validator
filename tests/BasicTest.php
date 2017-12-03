<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use Zsxsoft\W3CValidator\ValidatorException;
use Zsxsoft\W3CValidator\W3CValidator;

class BasicTest extends TestCase
{

    public function testRun()
    {
        $validator = new W3CValidator();
        $data = $validator->data('<html></html>')->run();
        $this->assertCount(2, $data);
        $this->assertNull($data['normal']);
        $this->assertCount(2, $data['error']->messages);
    }

    public function testArguments()
    {
        $validator = new W3CValidator(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vnu.jar');
        $data = $validator->javaArgument('-Xss10M')->format('text')->fileName('-')->data('<html></html>')->run();
        $this->assertEquals('text', $validator->format());
        $this->assertEquals('-Xss10M', $validator->javaArgument());
        $this->assertEquals('<html></html>', $validator->data());
        $this->assertCount(2, $data);
        $this->assertStringStartsWith("Error:", $data[1]);
    }

    public function testAPIs()
    {
        $validator = new W3CValidator();
        $validator->filterfile('test')->exit_zero_always();
        $this->assertEquals('test', $validator->filterfile());
    }

    public function testExec()
    {
        $validator = new W3CValidator();
        $validator->exec('-', function ($pipes) {
            fwrite($pipes[0], 'Hello');
            fclose($pipes[0]);
            $stderr = stream_get_contents($pipes[2]);
            $this->assertStringStartsWith(':1.1-1.4:', $stderr);
        });
    }


    /**
     * @expectedException \BadMethodCallException
     */
    public function testWhenCallNonExistMethod()
    {
        (new W3CValidator())->notExist();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWhenCallInvalidArgument ()
    {
        (new W3CValidator())->exit_zero_always(true, true);
    }


}