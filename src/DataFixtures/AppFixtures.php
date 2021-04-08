<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Blog;
use App\Entity\Category;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        for ($i = 0; $i < 10; ++$i) {
            $category = new Category();
            $category->setName("Cat" . $i);
            $manager->persist($category);
            $blog = new Blog();
            $blog->setTitle('Lorem ipsum');
            $blog->setBody('Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
              Proin sodales, arcu non commodo vulputate, neque lectus luctus metus, 
              ac hendrerit mi erat eu ante. Nullam blandit arcu erat,
              vitae pretium neque suscipit vitae. 
              Pellentesque sit amet lacus in metus placerat posuere. Aliquam hendrerit risus elit, non commodo nulla cursus id. 
              Vivamus tristique felis leo, vitae laoreet sapien eleifend vitae. Etiam varius sollicitudin tincidunt');
            $blog->setShortDescription('Lorem ipsum description');
            $blog->setCategory($category);
            $manager->persist($blog);
        }
        $manager->flush();
    }
}
