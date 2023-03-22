<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function indexTest(TranslatorInterface $translator): Response
    {
        $translatedText = $translator->trans('hello.world');
        return new Response($translatedText);
    }

    #[Route('/test-view', name: 'app_test_view')]
    public function indexTestView(): Response
    {
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
