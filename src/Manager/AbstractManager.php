<?php
declare(strict_types=1);

namespace App\Manager;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractManager
{
    protected EntityManagerInterface $entityManager;

    protected Connection $connection;

    protected EventDispatcherInterface $eventDispatcher;

    protected RouterInterface $router;

    /**
     * @required
     */
    public function setRequired(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, RouterInterface $router): void
    {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    public function clearCache(): void
    {
        if (function_exists('apcu_clear_cache')) {
            //apcu_clear_cache();
        }
    }

    public function count(string $table): int
    {
        $sql = 'SELECT COUNT(id) AS count FROM '.$table;
        $stmt = $this->connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAssociative();
        return $result['count'] ?? 0;
    }

    /**
     * @param array<mixed> $fields
     */
    public function insert(string $table, array $fields): int
    {
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', array_keys($fields)).') VALUES ('.implode(', ', array_map(function ($n) {
            return ':'.$n;
        }, array_keys($fields))).')';
        $stmt = $this->connection->prepare($sql);
        foreach ($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->executeQuery();
        return intval($this->connection->lastInsertId());
    }

    /**
     * @param array<mixed> $fields
     */
    public function update(string $table, array $fields, int $id): void
    {
        $sql = 'UPDATE '.$table.' SET '.implode(', ', array_map(function ($n) {
            return $n.' = :'.$n;
        }, array_keys($fields))).' WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        foreach ($fields as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('id', $id);
        $stmt->executeQuery();
    }

    public function setCategory(string $title): int
    {
        $sql = 'SELECT id FROM category WHERE title = :title';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('title', $title);
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAssociative();

        if ($result) {
            $category_id = $result['id'];
        } else {
            $insertCategory = [
                'title' => $title,
                'date_created' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];
            $category_id = $this->insert('category', $insertCategory);
        }

        return $category_id;
    }

    public function cleanWebsite(string $website): string
    {
        $website = str_replace('&amp;', '&', $website);
        $website = mb_substr($website, 0, 255, 'UTF-8');

        return $website;
    }

    public function cleanLink(string $link): string
    {
        $link = str_replace('&amp;', '&', $link);
        $link = mb_substr($link, 0, 255, 'UTF-8');

        return $link;
    }

    public function cleanTitle(string $title): string
    {
        $title = str_replace('&amp;', '&', $title);
        $title = trim(strip_tags(html_entity_decode($title)));
        $title = mb_substr($title, 0, 255, 'UTF-8');

        return $title;
    }

    public function cleanContent(mixed $content, string $case): string
    {
        if (class_exists('DOMDocument') && $content != '') {
            try {
                libxml_use_internal_errors(true);

                if ($iconv = iconv('UTF-8', 'ISO-8859-1', htmlentities($content, ENT_COMPAT, 'UTF-8'))) {
                    $content = htmlspecialchars_decode($iconv, ENT_QUOTES);
                }

                if ($content && is_string($content)) {
                    $dom = new \DOMDocument();
                    $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOENT | LIBXML_NOWARNING);

                    $xpath = new \DOMXPath($dom);

                    if ($case == 'store') {
                        $disallowedAttributes = ['id', 'style', 'width', 'height', 'onclick', 'ondblclick', 'onmouseover', 'onmouseout', 'accesskey', 'data', 'dynsrc', 'tabindex'];
                        foreach ($disallowedAttributes as $attribute) {
                            $nodes = $xpath->query('//*[@'.$attribute.']');
                            if ($nodes) {
                                foreach ($nodes as $node) {
                                    if ($node instanceof \DOMElement) {
                                        //don't remove style, width and height if iframe
                                        if (($attribute == 'style' || $attribute == 'width' || $attribute == 'height') && $node->tagName == 'iframe') {
                                            continue;
                                        }

                                        $node->removeAttribute($attribute);
                                    }
                                }
                            }
                        }
                    }

                    $nodes = $xpath->query('//*[@src]');
                    if ($nodes) {
                        foreach ($nodes as $node) {
                            if ($node instanceof \DOMElement) {
                                $node->setAttribute('loading', 'lazy');

                                $src = $node->getAttribute('src');

                                if ($node->tagName == 'iframe') {
                                    $parse_src = parse_url($src);
                                    //keep iframes from instagram, youtube, vimeo and dailymotion
                                    if (isset($parse_src['host']) && (stristr($parse_src['host'], 'instagram.com') || stristr($parse_src['host'], 'youtube.com') || stristr($parse_src['host'], 'vimeo.com') || stristr($parse_src['host'], 'dailymotion.com'))) {
                                        $src = str_replace('http://', 'https://', $src);
                                        $src = str_replace('autoplay=1', 'autoplay=0', $src);
                                        $node->setAttribute('src', $src);
                                        $node->setAttribute('frameborder', '0');
                                        $node->removeAttribute('sandbox');
                                    } else {
                                        if ($node->parentNode) {
                                            $node->parentNode->removeChild($node);
                                        }
                                    }
                                }

                                if ($node->tagName == 'img' && $case == 'display') {
                                    if (str_starts_with($src, 'http://')) {
                                        $token = urlencode(base64_encode($src));
                                        $node->setAttribute('src', 'app/icons/icon-32x32.png');
                                        $node->setAttribute('data-src', $this->router->generate('api_proxy', ['token' => $token], 0));
                                        $node->setAttribute('data-src-origin', $src);
                                        $node->setAttribute('class', 'proxy');
                                    }

                                    $node->removeAttribute('srcset');
                                }
                            }
                        }
                    }

                    if ($case == 'display') {
                        $nodes = $xpath->query('//*[@class]');
                        if ($nodes) {
                            foreach ($nodes as $node) {
                                if ($node instanceof \DOMElement) {
                                    $class = $node->getAttribute('class');

                                    if ($node->tagName == 'div') {
                                        if ($class == 'feedflare') {
                                            if ($node->parentNode) {
                                                $node->parentNode->removeChild($node);
                                            }
                                        }
                                    }

                                    if ($node->tagName == 'blockquote') {
                                        if ($class == 'instagram-media') {
                                            $links = $node->getElementsByTagName('a');
                                            if (0 < count($links)) {
                                                foreach ($links as $link) {
                                                    $nodeReplace = $dom->createElement('a');
                                                    $domAttribute = $dom->createAttribute('href');
                                                    $domAttribute->value = $link->getAttribute('href');
                                                    $nodeReplace->appendChild($domAttribute);

                                                    $img = $dom->createElement('img');
                                                    $domAttribute = $dom->createAttribute('src');
                                                    $domAttribute->value = $link->getAttribute('href').'media/?size=m';
                                                    $img->appendChild($domAttribute);

                                                    $nodeReplace->appendChild($img);

                                                    if ($node->parentNode) {
                                                        $node->parentNode->replaceChild($nodeReplace, $node);
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $content = $dom->saveHTML();

                    libxml_clear_errors();
                }
            } catch (\Exception $e) {
            }
        }

        return $content;
    }
}
