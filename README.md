# Bundle Install

## Bundle import
``` json
//composer.json du nouveau Projet
"require": {
  "...,"
  "tgc/edit-content": "dev-master",
  "..."
},

"repositories": [
  "...",
	{
		"type": "vcs",
		"url": "git@os1119.octey.me:Alexis/EditBundle.git"
	},
  "..."
],

"config": {
  "...",
	"secure-http": false,
  "..."
},
```

```
composer update
```



## Add route of the bundle
``` yml
//app/config/routing.yml
edit-content:
    resource: '@TgcEditContentBundle/Controller'
    type: annotation
```

## Manage local langage
``` yml
//app/config/services.yml
services:
  Tgc\EditContentBundle\EventSubscriber\LocaleSubscriber:
      arguments: ['%kernel.default_locale%']
```

## Activate the bundle:
``` php
<?php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Tgc\EditContentBundle\TgcEditContentBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
        );

        if (//...) {
            //....
            if (//...) {
                //...
                $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            }
        }
    }
}
```

## Add translatable extension to your mapping
``` yml
// app/config/config.yml
doctrine:
    orm:
        mappings:
            translatable:
                type: annotation
                is_bundle: false
                prefix: Gedmo\Translatable\Entity
                dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity"
                alias: GedmoTranslatable
```

## Enable translatable extension
``` yml
// app/config/config.yml
stof_doctrine_extensions:
    default_locale: fr
    persist_default_translation: true
    orm:
        default:
            translatable: true
```

# Fixtures Install

## Fixtures creation
``` php
<?php
//src/AppBundle/DataFixtures/ORM/

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tgc\EditContentBundle\Entity\TextEditable;

class TextData implements FixtureInterface {

    public function load(ObjectManager $manager) {
        $contents = [
            [
              'slug' => 'presentationTitre',
              'text-fr' => 'Présentation fr',
              'text-en' => 'Presentation en'
            ]
          ];

        foreach ($contents as $content) {
            $repo = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
            $textEditable = new TextEditable();

            $textEditable->setSlug($content['slug']);

            $repo
                    ->translate($textEditable,'text', 'en', $content['text-en'])
                    ->translate($textEditable, 'text', 'fr', $content['text-fr'])
            ;

            $manager->persist($textEditable);
        }


        $manager->flush();
    }

}
```
## Update database
```
php bin/console doctrine:schema:update
```

## Add data in fixtures in $contents
 ``` php
 <?php
 class TextData implements FixtureInterface {
     public function load(ObjectManager $manager) {
         $contents = [
             [
               'slug' => 'presentationChambres',
               'text-fr' => 'chambres fr',
               'text-en' => 'rooms en'
             ]
           ];
 ```

 ## Load fixtures
 ```
 php bin/console doctrine:fixtures:load
 ```
## Add code in your method Controller
``` php
<?php
//src/AppBundle/Controller/DefaultController
/**
 * @Route("/{edit}", name="homepage", requirements={"edit": "|edit|preview"})
 */
public function indexAction($edit = "", Request $request)
{
		$editContentService = $this->get('edit_content');

		$result = $editContentService->getEditContent(['presentationTitre'], 'homepage', $edit, $request->getLocale());

		return $this->render('default/index.html.twig', $result);
}
```

## Add slug in your function getEditContent() in your method controller
``` php
<?php
 $editContentService->getEditContent(['presentationTitre','SlugToInsert'], 'homepage', '', $request->getLocale());
}
```
## in your view add :
``` twig
{{ include('TgcEditContentBundle:Default:validateTextEdit.html.twig') }}
{{ include('TgcEditContentBundle:Default:textEditable.html.twig', { 'content': presentationTitre }) }}
```

## Add Cache Configuration
``` yml
// app/config/config.yml
doctrine_cache:
    aliases:
      tgc_cache: tgc_cache
    providers:
        tgc_cache:
        ## See http://symfony.com/doc/master/bundles/DoctrineCacheBundle/reference.html
        ## For more configuration options
            type: file_system
            file_system:
                umask: 0113
                extension: 'cache'
```
