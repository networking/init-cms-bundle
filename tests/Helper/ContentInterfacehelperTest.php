<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\tests\Helper;

use PHPUnit\Framework\TestCase;
use Networking\InitCmsBundle\Helper\ContentInterfaceHelper;
use Networking\InitCmsBundle\Entity\Text;
use Sonata\AdminBundle\Exception\NoValueException;

class ContentInterfacehelperTest extends TestCase
{
    public $object;
    public $text;

    public function setup(): void
    {
        $this->object = new ContentInterfaceHelper();
        $this->text = new Text();
        $this->text->setText('foo');
    }

    public function testCamelize()
    {
        $camel = $this->object->camelize('foo_bar');
        $this->assertEquals('FooBar', $camel);
        $camel = $this->object->camelize('foo');
        $this->assertEquals('Foo', $camel);
        $camel = $this->object->camelize('foo.bar');
        $this->assertEquals('Foo_Bar', $camel);
        $camel = $this->object->camelize('foo bar');
        $this->assertEquals('FooBar', $camel);
    }

    public function testSetFieldValueUnknown()
    {
        $x = $this->object->setFieldValue($this->text, 'other_field', 'bar');
        $this->assertInstanceOf('Networking\InitCmsBundle\Entity\Text', $x);
        $this->assertNotEquals('bar', $this->text->getText());
        $this->assertEquals('foo', $this->text->getText());
    }

    public function testSetFieldValue()
    {
        $this->object->setFieldValue($this->text, 'text', 'bar');
        $this->assertEquals('bar', $this->text->getText());
    }

    public function testGetFieldValue()
    {
        $x = $this->object->getFieldValue($this->text, 'text');
        $this->assertEquals('foo', $x);
    }


    public function testGetFieldValue_ShouldThrowException()
    {
        $this->expectException(NoValueException::class);
        $this->object->getFieldValue($this->text, 'bar');
    }
}
