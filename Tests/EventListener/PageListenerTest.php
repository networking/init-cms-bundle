<?php
namespace Networking\InitCmsBundle\Tests\EventListener;

use Networking\InitCmsBundle\EventListener\PageListener;
use Networking\InitCmsBundle\Helper\PageHelper;

class PageListenerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers PageHelper::getPageRoutePath()
	 */
	public function testGetPageRoutePath()
	{

		$this->assertEquals('/', PageHelper::getPageRoutePath(''), 'empty route is "/"');
		$this->assertEquals('/hallo', PageHelper::getPageRoutePath('hallo'), 'slash at the beginning');
		$this->assertEquals('/some/tree/', PageHelper::getPageRoutePath('/some-2/tree-20/'), 'remove -numbers in path');
	}

	/**
	 * postPersist with Tag
	 * @covers PageListener::postPersist()
	 */
	public function testPostPersist1()
	{
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
		$pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);
		$mockArgs = $this->getMockDoctrineLifeCycleEventArgsPersist('\Networking\InitCmsBundle\Entity\Tag');
		$pageListener->postPersist($mockArgs);
	}

	/**
	 * postPersist with Page
	 */
	public function testPostPersist2()
	{
		$container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
		$pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);
		$mockArgs = $this->getMockDoctrineLifeCycleEventArgsPersist('\Networking\InitCmsBundle\Entity\Page');
		$pageListener->postPersist($mockArgs);
	}

	/**
	 * postUpdate with Tag
	 * @covers PageListener::postUpdate()
	 */
	public function testPostUpdate1()
	{
		$container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
		$pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);

		$mockDoctrineLifeCycleEventArgsTag = $this->getMockDoctrineLifeCycleEventArgs('\Networking\InitCmsBundle\Entity\Tag');
		$pageListener->postUpdate($mockDoctrineLifeCycleEventArgsTag);
	}

	/**
	 * postUpdate with Page and no children
	 *
	 * hä?
	 * Expectation failed for method name is equal to <string:setPath> when invoked 0 time(s).
	 * Mocked method does not exist.
	 */
	public function testPostUpdate2(){
		$container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
		$pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);

		$mockDoctrineLifeCycleEventArgsPage = $this->getMockDoctrineLifeCycleEventArgs('\Networking\InitCmsBundle\Entity\Page');
		$pageListener->postUpdate($mockDoctrineLifeCycleEventArgsPage);
	}

	/**
	 * postUpdate with Page and 10 children
	 */
	public function testPostUpdate3(){
		$container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
		$pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);

		$mockDoctrineLifeCycleEventArgsPageMulti = $this->getMockDoctrineLifeCycleEventArgs('\Networking\InitCmsBundle\Entity\Page', 10);
		$pageListener->postUpdate($mockDoctrineLifeCycleEventArgsPageMulti);
	}

	/**
	 * @return array
	 */
	private function getMockEntityWrongClass()
	{
		// entity
		$entity = $this->getMock('\StdClass');
		$entity->expects($this->never())
				->method('getContentRoute');
		// em
		$em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
				->disableOriginalConstructor()
				->getMock();
		$em->expects($this->never())
				->method('persist');
		$em->expects($this->never())
				->method('flush');
		return array($entity, $em);
	}

	/**
	 * @param $class
	 * @param int $count
	 * @return array
	 */
	private function getMockEntity($class, $count=0)
	{
		// contentRoute
		$contentRoute = $this->getMock('\Networking\InitCmsBundle\Entity\ContentRoute', array('setPath'));
		$contentRoute->expects($this->once())
				->method('setPath')
				->with($this->equalTo('/'));

		// entity
		$entity = $this->getMock($class);
		$entity->expects($this->once())
				->method('getContentRoute')
				->will($this->returnValue($contentRoute));
		$entity->expects($this->once())
			->method('getAllChildren')
			->will($this->returnValue($this->getMockChildren($count)));

		// em
		$em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
				->disableOriginalConstructor()
				->getMock();
		$em->expects($this->exactly($count))
				->method('persist');
		$em->expects($this->once())
				->method('flush');

		return array($entity, $em);
	}

	/**
	 * @param $class
	 * @return array
	 */
	private function getMockEntityPersist($class)
	{
		// contentRoute
		$contentRoute = $this->getMock('\Networking\InitCmsBundle\Entity\ContentRoute', array('setPath'));
		$contentRoute->expects($this->once())
				->method('setPath')
				->with($this->equalTo('/some/random-pi/path'));
		//TODO this is not called ????
//		$contentRoute->expects($this->once())
//				->method('setObjectId')
//				->with($this->equalTo('108'));

		// entity
		$entity = $this->getMock($class);
		$entity->expects($this->once())
				->method('getId')
				->will($this->returnValue('108'));
		$entity->expects($this->once())
				->method('getPath')
				->will($this->returnValue('/some-25465/random-pi/path-4'));
		$entity->expects($this->once())
			->method('getContentRoute')
			->will($this->returnValue($contentRoute));

		// em
		$em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
				->disableOriginalConstructor()
				->getMock();
		$em->expects($this->once())
				->method('persist');
		$em->expects($this->once())
				->method('flush');

		return array($entity, $em);
	}

	/**
	 * @param int $count
	 * @return array
	 */
	private function getMockChildren($count=0)
	{
		$array = array();
		for($i=0;$i<$count;$i++)
		{
			$contentRoute = $this->getMock('\Networking\InitCmsBundle\Entity\ContentRoute', array('setPath'));
			$contentRoute->expects($this->once())
				->method('setPath')
				->with($this->equalTo('/some/path-p/'.$count));

			$mockPage = $this->getMock('\Networking\InitCmsBundle\Entity\Page');
			$mockPage->expects($this->once())
				->method('getContentRoute')
				->will($this->returnValue($contentRoute));
			$mockPage->expects($this->once())
				->method('getPath')
				->will($this->returnValue('/some-234/path-p/'.$count));


			$array[] = $mockPage;
		}
		return $array;
	}


	/**
	 * @param $class
	 * @param int $count
	 * @return mixed
	 */
	private function getMockDoctrineLifeCycleEventArgs($class, $count=0)
	{

		if('\Networking\InitCmsBundle\Entity\Page' == $class) {
			list($entity, $em) = $this->getMockEntity($class, $count);
		} else {
			// easy case: the entity is not a Page
			list($entity, $em) = $this->getMockEntityWrongClass();
		}

		return $this->getMockArgs($entity, $em);

	}


	/**
	 * @param $class
	 * @return mixed
	 */
	private function getMockDoctrineLifeCycleEventArgsPersist($class)
	{
		if('\Networking\InitCmsBundle\Entity\Page' == $class) {
			list($entity, $em) = $this->getMockEntityPersist($class);
		} else {
			list($entity, $em) = $this->getMockEntityWrongClass();
		}

		return $this->getMockArgs($entity, $em);
	}


	private function getMockArgs($entity=null, $em=null)
	{
		$args = $this
				->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
				->disableOriginalConstructor()
				->getMock();
		// args methods:
		// getEntity
		$args->expects($this->once())
				->method('getEntity')
				->will($this->returnValue($entity));
		// getEntityManager
		$args->expects($this->once())
			->method('getEntityManager')
			->will($this->returnValue($em));

		return $args;
	}


}