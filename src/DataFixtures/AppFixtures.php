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
        $manager->flush();
    }
}
