<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Pizza;
use App\Entity\PizzaIngredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->loadIngredients($manager);
        $this->loadPizzas($manager);
    }

    private function loadIngredients(ObjectManager $manager)
    {

        $ingredients = [
            [
                'name' => 'Sliced mushrooms',
                'cost' => 0.5,
                'reference' => 'sliced_mushrooms',
            ],
            [
                'name' => 'Feta cheese',
                'cost' => 1,
                'reference' => 'feta_cheese',
            ],
            [
                'name' => 'Sausages',
                'cost' => 1,
                'reference' => 'sausages',
            ],
            [
                'name' => 'Sliced onion',
                'cost' => 0.5,
                'reference' => 'sliced_onion',
            ],
            [
                'name' => 'Mozzarella cheese',
                'cost' => 0.5,
                'reference' => 'mozzarella_cheese',
            ],
            [
                'name' => 'Oregano',
                'cost' => 1,
                'reference' => 'oregano',
            ],
            [
                'name' => 'Bacon',
                'cost' => 1,
                'reference' => 'bacon',
            ],
        ];


        foreach($ingredients as $ingredient) {
            $ingredientEntity = new Ingredient();
            $ingredientEntity->setName($ingredient['name']);
            $ingredientEntity->setCost($ingredient['cost']);
            $this->addReference($ingredient['reference'], $ingredientEntity);
            $manager->persist($ingredientEntity);
        }

        $manager->flush();

    }

    private function loadPizzas(ObjectManager $manager)
    {

        $funPizza = new Pizza();
        $funPizza->setName('Fun Pizza');
        $funPizza->setPrice(7.5);

        $funPizzaIngredientReferences = [
            'sliced_mushrooms',
            'feta_cheese',
            'sausages',
            'sliced_onion',
            'mozzarella_cheese',
            'oregano',
        ];

        foreach($funPizzaIngredientReferences as $index => $reference) {
            $funPizzaIngredient= new PizzaIngredient();
            $funPizzaIngredient->setPizza($funPizza);
            $funPizzaIngredient->setIngredient($this->getReference($reference));
            $funPizzaIngredient->setPriority($index + 1);

            $funPizza->addIngredient($funPizzaIngredient);

            $manager->persist($funPizzaIngredient);
        }

        $manager->persist($funPizza);
        $manager->flush();

    }
}
