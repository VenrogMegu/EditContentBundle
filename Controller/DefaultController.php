<?php

namespace Tgc\EditContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Tgc\EditContentBundle\Entity\TextEditable;
use Tgc\EditContentBundle\Form\TextEditableType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/test", name="indexpage" , requirements={"edit": "|edit|preview"})
 */
class DefaultController extends Controller {

    /**
     * @Route("/{edit}", name="indexpage" , requirements={"edit": "|edit|preview"})
     */
    public function indexAction($edit = "", Request $request) {

        $editContentService = $this->get('edit_content');

        $result = $editContentService->getEditContent(['presentationTitre'], 'indexpage', $edit, $request->getLocale());
        dump($result);
        return $this->render('TgcEditContentBundle:Default:index.html.twig', $result);
    }


    /**
     * @Route("/autrepage/{edit}", name="autrepage" )
     */
    public function autrePageAction($edit = "") {
        //reucuper mon service 'edit_content'
        $editContentService = $this->get('edit_content');

        $result = $editContentService->getEditContent(['autretitre', 'autredescription'], 'autrepage', $edit);


        return $this->render('TgcEditContentBundle:Default:autrepage.html.twig', $result);
    }

    /**
     * @Route("/edit-text/{slug}", name="edittextpage")
     * @param $slug
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function editTextAction($slug, Request $request) {
        $em = $this->getDoctrine()->getManager();

        $textEditable = $em->getRepository(TextEditable::class)->findOneBySlug($slug);

        if (!$textEditable instanceof TextEditable) {
            throw new \Exception('attention ce n\'est pas une entity');
        }

        $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
        $translations = $repository->findTranslations($textEditable);

        $editForm = $this->createFormBuilder()
                ->add('text-en', TextType::class, ['label' => 'Content En', 'data' => $translations['en']['text']])
                ->add('text-fr', TextType::class, ['label' => 'Content Fr', 'data' => $translations['fr']['text']])
                ->add('save', SubmitType::class, array('label' => 'preview'))
                ->getForm();

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $datas = $editForm->getData();
            $datas['slug'] = $slug;

            $session = $this->get('session');
            $session->set('preview', $datas);

            return $this->redirectToRoute($session->get('origin-page'), [
                        'edit' => 'preview']);
        }

        return $this->render('TgcEditContentBundle:Default:edit.html.twig', [
                    'editform' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/validateEdit/{valid}", name="validateEditionpage")
     */
    public function validateEditionAction($valid) {
        $session = $this->get('session');
        if ($valid) {
            $em = $this->getDoctrine()->getManager();
            $textEditPreview = $session->get('preview');
            $textEditable = $em->getRepository(TextEditable::class)->findOneBySlug($textEditPreview['slug']);

            if (!$textEditable instanceof TextEditable) {
                throw new \Exception('attention ce n\'est pas une entity');
            }

            $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');

            $repository
                    ->translate($textEditable, 'text', 'en', $textEditPreview['text-en'])
                    ->translate($textEditable, 'text', 'fr', $textEditPreview['text-fr'])
            ;


            $em->flush();
            // Clear All TextEditable Cache
            $this->container->get('tgc_cache')->deleteAll();
        }

        $session->remove('textEditable');

        return $this->redirectToRoute($session->get('origin-page'), [
                    'edit' => 'edit']);
    }

    /**
     * Locale change
     *
     * @Route("/setlocale/{language}", name="setlocale")
     */
    public function setLocaleAction($language = null, Request $request) {
        if ($language != null) {
            $this->get('session')->set('_locale', $language);
        }

        $url = $request->headers->get('referer');

        if (empty($url)) {
            $url = $this->container->get('router')->generate('index');
        }

        return new RedirectResponse($url);
    }

}
