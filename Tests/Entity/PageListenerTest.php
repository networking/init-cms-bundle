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

use Networking\InitCmsBundle\Entity\PageListener;
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
    public function testPostPersist_WithTag()
    {
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
        $pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);

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

        $args = $this
            ->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        // args methods:
        // getObject
        $args->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($entity));
        // getObjectManager
        $args->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($em));

        $pageListener->postPersist($args);
    }

    /**
     * postPersist with Page
     */
    public function testPostPersist_WithPage()
    {
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
        $pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);
        $contentRoute = $this->getMock('\Networking\InitCmsBundle\Model\ContentRoute', array('setPath', 'setObjectId'));
        $contentRoute->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo('/some/random-pi/path'));
        $contentRoute->expects($this->once())
            ->method('setObjectId')
            ->with($this->equalTo(108));

        // entity
        $entity = $this->getMock('\Networking\InitCmsBundle\Model\Page');

        $entity->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(108));
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

        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $classMetaData = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetaData));


        $em->expects($this->once())
            ->method('persist');

        $args = $this
            ->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        // args methods:
        // getObject
        $args->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($entity));
        // getObjectManager
        $args->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($em));

        $pageListener->postPersist($args);
    }

    /**
     * postUpdate with Tag
     * @covers PageListener::postUpdate()
     */
    public function testPostUpdate_WithStdClass()
    {
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
        $pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);

        // entity
        $entity = $this->getMock('\StdClass');
        $entity->expects($this->never())
            ->method('getContentRoute');
        // em
        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array($entity)));

        $em->expects($this->never())
            ->method('persist');
        $em->expects($this->never())
            ->method('flush');

        $args = $this
            ->getMockBuilder('\Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        // args methods:

        // getObjectManager
        $args->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        $pageListener->onFlush($args);
    }

    /**
     * postUpdate with Page and no children
     *
     */
    public function testPostUpdate_WithPage()
    {
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
        $pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);

        // contentRoute
        $contentRoute = $this->getMock('\Networking\InitCmsBundle\Model\ContentRoute', array('setPath'));
        $contentRoute->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo('/'));

        // entity
        $entity = $this->getMock('\Networking\InitCmsBundle\Model\Page');
        $entity->expects($this->once())
            ->method('getContentRoute')
            ->will($this->returnValue($contentRoute));
        $entity->expects($this->exactly(1))
            ->method('getAllChildren')
            ->will($this->returnValue($this->getMockChildren(0)));

        // em
        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $classMetaData = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetaData));

        $em->expects($this->exactly(1))
            ->method('persist');

        //UnitOfWork tests

        $uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array($entity)));

        $uow->expects($this->exactly(1))
            ->method('computeChangeSet');

        $args = $this
            ->getMockBuilder('\Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        // args methods:

        // getObjectManager
        $args->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        $pageListener->onFlush($args);
    }

    /**
     * postUpdate with Page and 10 children
     */
    public function testPostUpdate_WithPageAndTenChildren()
    {
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
        $pageListener = new PageListener(new \Symfony\Component\HttpFoundation\Session\Session(), $container);

        // contentRoute
        $contentRoute = $this->getMock('\Networking\InitCmsBundle\Model\ContentRoute', array('setPath'));
        $contentRoute->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo('/'));

        // entity
        $entity = $this->getMock('\Networking\InitCmsBundle\Model\Page');
        $entity->expects($this->once())
            ->method('getContentRoute')
            ->will($this->returnValue($contentRoute));
        $entity->expects($this->once())
            ->method('getAllChildren')
            ->will($this->returnValue($this->getMockChildren(10)));

        // em
        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $classMetaData = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->exactly(11))
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetaData));

        $em->expects($this->exactly(11))
            ->method('persist');


        $uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array($entity)));

        $uow->expects($this->exactly(11))
            ->method('computeChangeSet');

        $args = $this
            ->getMockBuilder('\Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        // args methods:

        // getObjectManager
        $args->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        $pageListener->onFlush($args);
    }


    /**
     * @param int $count
     * @return array
     */
    private function getMockChildren($count = 0)
    {
        $array = array();
        for ($i = 0; $i < $count; $i++) {
            $contentRoute = $this->getMock('\Networking\InitCmsBundle\Model\ContentRoute', array('setPath'));
            $contentRoute->expects($this->once())
                ->method('setPath')
                ->with($this->equalTo('/some/path-p/' . $count));

            $mockPage = $this->getMock('\Networking\InitCmsBundle\Model\Page');
            $mockPage->expects($this->once())
                ->method('getContentRoute')
                ->will($this->returnValue($contentRoute));
            $mockPage->expects($this->once())
                ->method('getPath')
                ->will($this->returnValue('/some-234/path-p/' . $count));


            $array[] = $mockPage;

        }

        return $array;
    }

}