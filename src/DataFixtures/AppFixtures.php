<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Blog;
use App\Entity\Category;
use App\Entity\User;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $categories = $this->loadCategories($manager);
        $this->loadUsers($manager);
        $this->loadBlogs($manager, $categories);    
        $manager->flush();
    }

    private function loadCategories(ObjectManager $manager)
    {
        $category_names = [
            "Science",
            "Technology",
            "Lifestyle",
            "Sport",
            "Travel"
        ];

        $categories = [];

        foreach($category_names as $name) {
            $category = new Category();
            $category->setName($name);
            $categories[] = $category;
            $manager->persist($category);
        }

        return $categories;
    }

    private function loadUsers(ObjectManager $manager)
    {
        $user_data = [
            "admin" => "admin",
            "pippo" => "pippo",
            "topolino" => "topolino",
            "paperino" => "paperino"
        ];

        $users = [];

        foreach($user_data as $username=>$password) {
            $user = new User();
            $user->setUsername($username);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $password
            ));
            $users[] = $user;
        }
        return $users;
    }

    private function loadBlogs(ObjectManager $manager, $categories)
    {
        for ($i = 0; $i < 10; ++$i) {   
            $blog = new Blog();
            $blog->setTitle('Lorem ipsum');
            $blog->setBody('Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
              Proin sodales, arcu non commodo vulputate, neque lectus luctus metus, 
              ac hendrerit mi erat eu ante. Nullam blandit arcu erat,
              vitae pretium neque suscipit vitae. 
              Pellentesque sit amet lacus in metus placerat posuere. Aliquam hendrerit risus elit, non commodo nulla cursus id. 
              Vivamus tristique felis leo, vitae laoreet sapien eleifend vitae. Etiam varius sollicitudin tincidunt');
            $blog->setShortDescription('Lorem ipsum description');
            $blog->setCategory($categories[$i % sizeof($categories)]);
            $manager->persist($blog);
        }
    }
}
