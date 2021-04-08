<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class MainController extends AbstractController
{
    /**
     * @Route("/")
     *
     * @return Response
     */
    public function index()
    {
        return $this->redirectToRoute('app_blog_index');
    }
}