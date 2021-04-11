<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\BlogRepository;
use App\Entity\Blog;
use App\Form\BlogFormType;
use App\Repository\CategoryRepository;
use App\Service\ImageHelper;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{

    /**
     * @Route("s/")
     */
    public function index()
    {
        return $this->redirectToRoute('app_blog_list', ['category' => 'all']);
    }

     /**
     * @Route("/create")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $entityManager, ImageHelper $imageHelper)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $blog = new Blog();
        $form = $this->createForm(BlogFormType::class, $blog);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $blog = $form->getData();
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                try {
                    $dir = $this->getParameter('image_directory') . '/blogs';
                    $newFilename = $imageHelper->upload($imageFile, $dir);
                    $blog->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Image cannot be saved.');
                }
            }
            $blog->setCreator($this->getUser());
            $entityManager->persist($blog);
            $entityManager->flush();
            $this->addFlash('success', 'Blog was created!');
            return $this->redirectToRoute('app_main_index');
        }	

        return $this->render('blog/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}")
     *
     * @ParamConverter("blog", class="App:Blog")
     *
     * @return Response
     */
    public function edit(Blog $blog, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        if ($blog->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($blog->getImage()){
            $blog->setImage(new File(sprintf('%s/%s', $this->getParameter('image_directory'), $blog->getImage())));
        }
        $form = $this->createForm(BlogFormType::class, $blog);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $blog      = $form->getData();
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename  = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                            $this->getParameter('image_directory'),
                            $newFilename
                        );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Image cannot be saved.');
                }
                $blog->setImage($newFilename);
            }

            $entityManager->persist($blog);
            $entityManager->flush();
            $this->addFlash('success', 'Blog was edited!');
            return $this->redirectToRoute('app_main_index');
        }

        return $this->render('blog/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}")
     *
     * @param Blog                   $blog
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function delete(Blog $blog, EntityManagerInterface $em): RedirectResponse
    {
        if ($blog->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $em->remove($blog);
        $em->flush();
        $this->addFlash('success', 'Blog was edited!');

        return $this->redirectToRoute('app_main_index');
    }

    /**
     * @Route("/read/{id}")
     * 
     * @param Blog $blog
     * 
     * @return Response
     */
    public function read(Blog $blog): Response
    {
        return $this->render('blog/read.html.twig', ['blog' => $blog]);
    }

    /**
     * @Route("s/{category}")
     * 
     * @param BlogRepository $blogRepository
     *
     * @return Response
     */
    public function list(BlogRepository $blogRepository, CategoryRepository $categoryRepository, $category): Response
    {
        if ($category == 'all') {
            $blogs = $blogRepository->findAll();
        } else {
            $blogs = $categoryRepository->findOneBy(['name' => $category])->getBlogs();
        }
        return $this->render('blog/list.html.twig', [
            'blogs' => $blogs,
            ]);
    }
}
