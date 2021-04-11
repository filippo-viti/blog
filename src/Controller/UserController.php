<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
/**
 * @Route("/user")
 */
class UserController extends AbstractController 
{
    /**
     * @Route("/{id}/blogs")
     * 
     * @param User $user
     */
    public function blogs(User $user): Response 
    {
        $blogs = $user->getBlogs();
        return $this->render('blog/list.html.twig', [
            'blogs' => $blogs
        ]);
    }
}
