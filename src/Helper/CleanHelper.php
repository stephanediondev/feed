<?php

namespace App\Helper;

final class CleanHelper
{
    public static function cleanWebsite(string $website): string
    {
        $website = str_replace('&amp;', '&', $website);
        $website = mb_substr($website, 0, 255, 'UTF-8');

        return $website;
    }

    public static function cleanLink(string $link): string
    {
        $link = str_replace('&amp;', '&', $link);
        $link = mb_substr($link, 0, 255, 'UTF-8');

        return $link;
    }

    public static function cleanTitle(string $title): string
    {
        $title = str_replace('&amp;', '&', $title);
        $title = trim(strip_tags(html_entity_decode($title)));
        $title = mb_substr($title, 0, 255, 'UTF-8');

        return $title;
    }

    public static function cleanContent(mixed $content, string $case): string
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
                                        $node->setAttribute('src', '/proxy?token='.$token);
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
