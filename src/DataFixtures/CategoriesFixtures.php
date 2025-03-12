<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
   private $counter=1;
    public function __construct(private SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {
         $parent = $this->CreateCategory('Informatique',null,$manager);
         $this->CreateCategory('Ordinateurs portables',$parent,$manager);
         $this->CreateCategory('Ecrans',$parent,$manager);
         $this->CreateCategory('Souris',$parent,$manager);

         $parent = $this->CreateCategory('Mode',null,$manager);
         $this->CreateCategory('Hommme',$parent,$manager);
         $this->CreateCategory('Femme',$parent,$manager);
         $this->CreateCategory('Enfant',$parent,$manager);
        $manager->flush();
    }
    public function CreateCategory(string $name,Categories $parent=null ,ObjectManager $manager)
    {
         $category = new Categories();
         $category->setName($name);
         $category->setSlug($this->slugger->slug($category->getName() )->lower());
         $category->setParent($parent);
         $manager->persist($category);
         $this->addReference('cat-'.$this->counter,$category);
         $this->counter++;
         return $category;
    }
}
