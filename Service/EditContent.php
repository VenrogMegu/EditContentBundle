<?php

namespace Tgc\EditContentBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tgc\EditContentBundle\Entity\TextEditable;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Cache\Cache;

class EditContent {

    private $session;
    private $em;
    private $cache;

    public function __construct(SessionInterface $session, EntityManagerInterface $em, Cache $cache) {
        $this->session = $session;

        $this->em = $em;
        $this->cache = $cache;
    }

    public function getEditContent($slugs, $currentRout, $mode, $locale) {

        $textEditables = [];

        $cacheKey = md5(implode($slugs).$locale);
        // if cached get from cache else doctrine query
        if ($this->cache->contains($cacheKey)) {
          $textEditables = $this->cache->fetch($cacheKey);
        } else {
          $textEditables = $this->em->getRepository(TextEditable::class)
            ->findBySlugs($slugs);
          $this->cache->save($cacheKey, $textEditables);
        }

        if ($mode == "edit") {
          $this->session->set("origin-page", $currentRout);
        }
        if ($mode == "preview") {
            $textEditablepreview = $this->session->get('preview');

            if ($locale == 'en') {
                $textEditablepreview['text'] = $textEditablepreview['text-en'];
            } else {
                $textEditablepreview['text'] = $textEditablepreview['text-fr'];
            };

            $slug = $textEditablepreview['slug'];

            if (array_key_exists($slug, $textEditables)) {

                $textEditables[$slug] = $textEditablepreview;
            }
        }

        $modes = [
            'edit' => ($mode == 'edit'),
            'preview' => ($mode == 'preview'),
        ];

        return array_merge($textEditables, $modes);
    }

}
