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

class PageTest extends \PHPUnit_Framework_TestCase
{

    public function testOnPrePersist_ShouldSetDates()
    {
        $obj = $this->getPage();
		$this->assertEquals(null, $obj->getUpdatedAt());
		$obj->prePersist();
		$this->assertEquals(new \DateTime('now'), $obj->getUpdatedAt());
		$this->assertEquals(new \DateTime('now'), $obj->getCreatedAt());
    }

	public function testOnPrePersist_ShouldSetMetaTitle()
	{
		$obj = $this->getPage();
        $obj->setPageName('page Name');
        $this->assertEquals('', $obj->getMetaTitle());
		$obj->prePersist();
		$this->assertEquals('page Name', $obj->getMetaTitle());
	}

    /*
     * is this test too simple and not useful?
     */
	public function testSetTitle()
	{
		$obj = $this->getPage();
		$title = 'hello page';
		$this->assertNull($obj->getTitle());
		$obj->setPageName($title);
		$this->assertEquals($title, $obj->getTitle());
	}

	/**
	 * @covers getParents() setParents() getParent() getTitle()
	 */
	public function testSetParents()
	{
		$obj = $this->getPage();
		$this->assertEquals(array(), $obj->getParents());

		$parent1 = $this->getPage();
		$parent1->setPageName('parent1');
		$parent2 = $this->getPage();
		$parent2->setPageName('parent2');
		$parent3 = $this->getPage();
		$parent3->setPageName('parent3');
		$parents = array($parent1, $parent2, $parent3);
		$obj->setParents($parents);
		$this->assertContainsOnlyInstancesOf('Networking\InitCmsBundle\Model\PageInterface', $obj->getParents());
		$this->assertEquals('parent1', $obj->getParent(0)->getTitle());
		$this->assertEquals('parent2', $obj->getParent(1)->getTitle());
		$this->assertEquals('parent3', $obj->getParent(2)->getTitle());
	}


	public function testAddChildren()
	{
		$obj = $this->getPage();
		$obj->setPageName('original page');
		$this->assertEquals(null, $obj->getChildren());

		$child1 = $this->getPage();
		$child1->setPageName('child1');
		$obj->addChildren($child1);
		$this->assertContainsOnlyInstancesOf('Networking\InitCmsBundle\Model\PageInterface', $obj->getChildren());
		$children = $obj->getChildren();
		$this->assertEquals('child1', $children[0]->getTitle());
		$this->assertEquals('original page', $children[0]->getParent()->getTitle());

		$child2 = $this->getPage();
		$child2->setPageName('child2');
		$obj->addChildren($child2);
		$children = $obj->getAllChildren();
		$this->assertEquals('child2', $children[0]->getTitle()); // new children are first
		$this->assertEquals('original page', $children[0]->getParent()->getTitle());
	}

    /**
     * @return \Networking\InitCmsBundle\Model\Page
     */
    public function getPage()
    {
        return $this->getMockForAbstractClass('Networking\InitCmsBundle\Model\Page');
    }
}
