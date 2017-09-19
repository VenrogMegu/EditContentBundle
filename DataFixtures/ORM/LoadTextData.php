<?php

namespace Tgc\EditContentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tgc\EditContentBundle\Entity\TextEditable;

class LoadUserData implements FixtureInterface {

    public function load(ObjectManager $manager) {
        $contents = [
            // ['slug' => 'titre', 'text-fr' => 'mon premier titre Fixtures', 'text-en' => 'My first fixtures'],
            // ['slug' => 'description', 'text-fr' => 'une description tres simple from Fixtures', 'text-en' => 'A very simple description from Fixtures'],
            // ['slug' => 'autretitre', 'text-fr' => 'autre titre Fixtures', 'text-en' => 'Another Fixtures title'],
            // ['slug' => 'autredescription', 'text-fr' => 'autre description tres simple from Fixtures', 'text-en' => 'Another very simple description from Fixtures'],
        ];

        foreach ($contents as $content) {
            $repo = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
            $textEditable = new TextEditable();

            $textEditable->setSlug($content['slug']);

            $repo
                    ->translate($textEditable, 'text', 'en', $content['text-en'])
                    ->translate($textEditable, 'text', 'fr', $content['text-fr'])
            ;

            $manager->persist($textEditable);
        }


        $manager->flush();
    }

}
