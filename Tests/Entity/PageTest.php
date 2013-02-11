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

use \Networking\InitCmsBundle\Entity\Page;

class PageTest extends \PHPUnit_Framework_TestCase
{

    public function testOnPrePersist_ShouldSetDates()
    {
        $obj = new Page();
		$this->assertEquals(null, $obj->getUpdatedAt());
		$obj->onPrePersist();
		$this->assertEquals(new \DateTime('now'), $obj->getUpdatedAt());
		$this->assertEquals(new \DateTime('now'), $obj->getCreatedAt());
    }

	public function testOnPrePersist_ShouldSetMetaTitle()
	{
		$obj = new Page();
        $obj->setpageName('page Name');
        $this->assertEquals('', $obj->getMetaTitle());
		$obj->onPrePersist();
		$this->assertEquals('page Name', $obj->getMetaTitle());
	}

    /*
     * is this test too simple and not useful?
     */
	public function testSetTitle()
	{
		$obj = new Page();
		$title = 'hello page';
		$this->assertNull($obj->getTitle());
		$obj->setpageName($title);
		$this->assertEquals($title, $obj->getTitle());
	}

	/**
	 * @covers getParents() setParents() getParent() getTitle()
	 */
	public function testSetParents()
	{
		$obj = new Page();
		$this->assertEquals(array(), $obj->getParents());

		$parent1 = new Page();
		$parent1->setpageName('parent1');
		$parent2 = new Page();
		$parent2->setpageName('parent2');
		$parent3 = new Page();
		$parent3->setpageName('parent3');
		$parents = array($parent1, $parent2, $parent3);
		$obj->setParents($parents);
		$this->assertContainsOnlyInstancesOf('Networking\InitCmsBundle\Entity\Page', $obj->getParents());
		$this->assertEquals('parent1', $obj->getParent(0)->getTitle());
		$this->assertEquals('parent2', $obj->getParent(1)->getTitle());
		$this->assertEquals('parent3', $obj->getParent(2)->getTitle());
	}


	public function testAddChildren()
	{
		$obj = new Page();
		$obj->setpageName('original page');
		$this->assertEquals(null, $obj->getChildren());

		$child1 = new Page();
		$child1->setpageName('child1');
		$obj->addChildren($child1);
		$this->assertContainsOnlyInstancesOf('Networking\InitCmsBundle\Entity\Page', $obj->getChildren());
		$children = $obj->getChildren();
		$this->assertEquals('child1', $children[0]->getTitle());
		$this->assertEquals('original page', $children[0]->getParent()->getTitle());

		$child2 = new Page();
		$child2->setpageName('child2');
		$obj->addChildren($child2);
		$children = $obj->getAllChildren();
		$this->assertEquals('child2', $children[0]->getTitle()); // new children are first
		$this->assertEquals('original page', $children[0]->getParent()->getTitle());
	}
}
