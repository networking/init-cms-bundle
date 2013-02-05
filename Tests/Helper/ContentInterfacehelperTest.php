<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Tests\Helper;

use Networking\InitCmsBundle\Helper\ContentInterfaceHelper;
use Networking\InitCmsBundle\Entity\Text;

class ContentInterfaceHelperTest extends \PHPUnit_Framework_TestCase
{
	public $object;
	public $text;

	public function setup()
	{
		$this->object = new ContentInterfaceHelper();
		$this->text = new Text();
		$this->text->setText('Hey');
	}

	public function testCamelize()
	{
		$camel = $this->object->camelize('aha_bebe_tsetse');
		$this->assertEquals('AhaBebeTsetse', $camel);
		$camel = $this->object->camelize('text');
		$this->assertEquals('Text', $camel);
	}

	public function testSetFieldValueUnknown()
	{
		$x = $this->object->setFieldValue($this->text, 'meeb_moob', 'blubb');
		$this->assertInstanceOf('Networking\InitCmsBundle\Entity\Text', $x);
		$this->assertNotEquals('blubb', $this->text->getText());
	}

	public function testSetFieldValue()
	{
		$x = $this->object->setFieldValue($this->text, 'text', 'blubb');
		$this->assertInstanceOf('Networking\InitCmsBundle\Entity\Text', $x);
		$this->assertEquals('blubb', $this->text->getText());
	}

	public function testGetFieldValue()
	{
		$x = $this->object->getFieldValue($this->text, 'text');
//		$this->assertInstanceOf('Networking\InitCmsBundle\Entity\Text', $x);
		$this->assertEquals('Hey', $x);
	}

	/**
	 * @expectedException Sonata\AdminBundle\Exception\NoValueException
	 */
	public function testGetFieldValueException()
	{
		$x = $this->object->getFieldValue($this->text, 'miib');
		$this->assertEquals('Hey', $x);
	}

}