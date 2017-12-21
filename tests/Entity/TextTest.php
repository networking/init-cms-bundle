<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Tests\Entity;

use \Networking\InitCmsBundle\Entity\Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
	public function testOnPrePersist_ShouldSetDates()
	{
		$text = new Text();
		$this->assertEquals(null, $text->getUpdatedAt());
		$text->prePersist();
		$this->assertEquals(new \DateTime('now'), $text->getUpdatedAt());
		$this->assertEquals(new \DateTime('now'), $text->getCreatedAt());
	}

	public function testOnPreUpdate_ShouldSetUpdatedAt()
	{
		$text = new Text();
		$this->assertEquals(null, $text->getUpdatedAt());
		$text->preUpdate();
		$this->assertEquals(new \DateTime('now'), $text->getUpdatedAt());
		$this->assertEquals(null, $text->getCreatedAt());
	}



	public function testGetTemplateOptions()
	{
		$text = new Text();
		$randomText = '<strong>Some random text.</strong>';
		$text->setText($randomText);
		$result = $text->getTemplateOptions();
		$this->assertArrayHasKey('text', $result);
		$this->assertContains($randomText, $result);
	}

	public function testGetAdminContent()
	{
		$text = new Text();
		$randomText = '<strong>Some random text.</strong>';
		$text->setText($randomText);
		$result = $text->getAdminContent();
		$this->assertArrayHasKey('content', $result);
		$this->assertArrayHasKey('template', $result);
	}

}
