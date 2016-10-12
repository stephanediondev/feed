<?php
namespace Readerself\CoreBundle\Manager;

use Symfony\Component\Routing\RouterInterface;

use Readerself\CoreBundle\Manager\AbstractManager;
use Readerself\CoreBundle\Entity\Item;
use Readerself\CoreBundle\Event\ItemEvent;

class ItemManager extends AbstractManager
{
    public $enclosureManager;

    private $router;

    public function __construct(
        EnclosureManager $enclosureManager,
        RouterInterface $router
    ) {
        $this->enclosureManager = $enclosureManager;
        $this->router = $router;
    }

    public function getOne($paremeters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Item')->getOne($paremeters);
    }

    public function getList($parameters = [])
    {
        return $this->em->getRepository('ReaderselfCoreBundle:Item')->getList($parameters);
    }

    public function init()
    {
        return new Item();
    }

    public function persist($data)
    {
        if($data->getDateCreated() == null) {
            $mode = 'insert';
            $data->setDateCreated(new \Datetime());
        } else {
            $mode = 'update';
        }
        $data->setDateModified(new \Datetime());

        $this->em->persist($data);
        $this->em->flush();

        $event = new ItemEvent($data, $mode);
        $this->eventDispatcher->dispatch('Item.after_persist', $event);

        $this->clearCache();

        return $data->getId();
    }

    public function remove($data)
    {
        $event = new ItemEvent($data, 'delete');
        $this->eventDispatcher->dispatch('Item.before_remove', $event);

        $this->em->remove($data);
        $this->em->flush();

        $this->clearCache();
    }

    public function readAll($parameters = [])
    {
        foreach($this->em->getRepository('ReaderselfCoreBundle:Item')->getList($parameters)->getResult() as $result) {
            $sql = 'SELECT id FROM action_item_member WHERE member_id = :member_id AND item_id = :item_id AND action_id = :action_id';
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('member_id', $parameters['member']->getId());
            $stmt->bindValue('item_id', $result['id']);
            $stmt->bindValue('action_id', 1);
            $stmt->execute();
            $test = $stmt->fetch();

            if($test) {
            } else {
                $insertActionItemMember = [
                    'member_id' => $parameters['member']->getId(),
                    'item_id' => $result['id'],
                    'action_id' => 1,
                    'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
                ];
                $this->insert('action_item_member', $insertActionItemMember);

                $sql = 'DELETE FROM action_item_member WHERE action_id = :action_id AND item_id = :item_id AND member_id = :member_id';
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue('action_id', 12);
                $stmt->bindValue('item_id', $result['id']);
                $stmt->bindValue('member_id', $parameters['member']->getId());
                $stmt->execute();
            }
        }
    }
    public function cleanContent($content) {
        if(class_exists('DOMDocument') && $content != '') {
            try {
                libxml_use_internal_errors(true);

                $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

                $dom = new \DOMDocument();
                $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOENT | LIBXML_NOWARNING);

                $xpath = new \DOMXPath($dom);

                $nodes = $xpath->query('//*[@src]');
                foreach($nodes as $node) {
                    $src = $node->getAttribute('src');

                    if($node->tagName == 'iframe') {
                        $parse_src = parse_url($src);
                        //keep iframes from instagram, youtube, vimeo and dailymotion
                        if(isset($parse_src['host']) && (stristr($parse_src['host'], 'instagram.com') || stristr($parse_src['host'], 'youtube.com') || stristr($parse_src['host'], 'vimeo.com') || stristr($parse_src['host'], 'dailymotion.com') )) {
                            $node->setAttribute('src', str_replace('http://', 'https://', $src));
                            $node->setAttribute('src', str_replace('autoplay=1', 'autoplay=0', $src));
                        } else {
                            $node->parentNode->removeChild($node);
                        }
                    }

                    if($node->tagName == 'img') {
                        if(substr($src, 0, 5) == 'http:') {// && $request->server->get('HTTPS') == 'on'
                            $src = urlencode(base64_encode($src));
                            $node->setAttribute('src', 'app/icons/icon-32x32.png');
                            $node->setAttribute('data-src', $this->router->generate('readerself_api_proxy', ['token' => $src], 0));
                        }

                        $node->removeAttribute('srcset');
                    }
                }

                $nodes = $xpath->query('//*[@class]');
                foreach($nodes as $node) {
                    $class = $node->getAttribute('class');

                    if($node->tagName == 'div') {
                        if($class == 'feedflare') {
                            $node->parentNode->removeChild($node);
                        }
                    }

                    if($node->tagName == 'blockquote') {
                        if($class == 'instagram-media') {
                            $links = $node->getElementsByTagName('a');
                            if($links) {
                                foreach($links as $link) {
                                    $nodeReplace = $dom->createElement('a');
                                    $domAttribute = $dom->createAttribute('href');
                                    $domAttribute->value = $link->getAttribute('href');
                                    $nodeReplace->appendChild($domAttribute);

                                    $img = $dom->createElement('img');
                                    $domAttribute = $dom->createAttribute('src');
                                    $domAttribute->value = $link->getAttribute('href').'media/?size=m';
                                    $img->appendChild($domAttribute);

                                    $nodeReplace->appendChild($img);

                                    $node->parentNode->replaceChild($nodeReplace, $node);
                                    break;
                                }
                            }
                        }
                    }
                }

                $content = $dom->saveHTML();

                libxml_clear_errors();
            } catch (Exception $e) {
            }
        }
        return $content;
    }
}
