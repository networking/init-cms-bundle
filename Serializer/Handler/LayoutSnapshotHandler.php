<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Serializer\Handler;
/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
use Doctrine\Common\Collections\ArrayCollection;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\VisitorInterface;
use Doctrine\Common\Collections\Collection;
use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;

class LayoutSnapshotHandler
{

    public function serializelayoutSnapshotToxml(VisitorInterface $visitor, $data, array $type)
    {
        if (null === $visitor->document) {
                    $visitor->document = $visitor->createDocument(null, null, true);
                }

                return $visitor->document->createElement('entry', json_encode($data));


    }

    public function deserializelayoutSnapshotFromxml(VisitorInterface $visitor, $data, array $type)
    {
        var_dump($data);
        //var_dump($this->xmlObjToArr($data));
    }

    private function xmlObjToArr($obj)
    {


        $namespace = $obj->getDocNamespaces(true);
        $namespace[NULL] = NULL;

        $children = array();
        $attributes = array();
        $name = strtolower((string)$obj->getName());

        $text = trim((string)$obj);
        if (strlen($text) <= 0) {
            $text = NULL;
        }

        // get info for all namespaces
        if (is_object($obj)) {
            foreach ($namespace as $ns => $nsUrl) {
                // atributes
                $objAttributes = $obj->attributes($ns, true);
                foreach ($objAttributes as $attributeName => $attributeValue) {
                    $attribName = strtolower(trim((string)$attributeName));
                    $attribVal = trim((string)$attributeValue);
                    if (!empty($ns)) {
                        $attribName = $ns . ':' . $attribName;
                    }
                    $attributes[$attribName] = $attribVal;
                }

                // children
                $objChildren = $obj->children($ns, true);
                foreach ($objChildren as $childName => $child) {
                    $childName = strtolower((string)$childName);
                    if (!empty($ns)) {
                        $childName = $ns . ':' . $childName;
                    }
                    $children[$childName][] = $this->xmlObjToArr($child);
                }
            }
        }

        return array(
            'name' => $name,
            'text' => $text,
            'attributes' => $attributes,
            'children' => $children
        );
    }
}

