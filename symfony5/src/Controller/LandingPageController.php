<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="landing_page")
     * @param Environment $twig
     * @return Response
     */
    public function landingPage(Environment $twig)
    {
        return $this->render('base.html.twig');
    }
}
