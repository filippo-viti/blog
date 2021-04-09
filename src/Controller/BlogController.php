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
use App\Repository\CategoryRepository;
use App\Repository\BlogRepository;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Blog;
use App\Form\BlogFormType;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{

    /**
     * @Route("/")
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
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $blog = new Blog();
        $form = $this->createForm(BlogFormType::class, $blog);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $blog = $form->getData();
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Image cannot be saved.');
                }
                $blog->setImage($newFilename);
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
        $this->denyAccessUnlessGranted('ROLE_USER');
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
                    $this->addFlash('error', 'Image cannot be saved.');
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
        $this->denyAccessUnlessGranted('ROLE_USER');
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
     * @Route("/{category}")
     * 
     * @param BlogRepository $blogRepository
     * @param Request $request
     *
     * @return Response
     */
    public function list(Request $request, BlogRepository $blogRepository): Response
    {
        $defaultData = ['category' => null];
        $form = $this->createFormBuilder($defaultData)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function (CategoryRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.name', 'ASC');
                },
                'choice_label' => 'name',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filter'
            ])
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $category = $form->getData();
            if($category['category'] == null) {
                $blogs = $blogRepository->findAll();
            } else {
                $blogs = $blogRepository->findBy(['category' => $category]);
            }
        } else {
            $blogs = $blogRepository->findAll();
        }
        return $this->render('blog/list.html.twig', [
            'blogs' => $blogs,
            'form' => $form->createView()
            ]);
    }
}
