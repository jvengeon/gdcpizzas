<?php

namespace App\Repository;

use App\Entity\PizzaIngredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PizzaIngredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method PizzaIngredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method PizzaIngredient[]    findAll()
 * @method PizzaIngredient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PizzaIngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PizzaIngredient::class);
    }

    public function findOneByPizzaIdAndIngredientId(int $pizzaId, int $ingredientId): ?PizzaIngredient
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.pizza = :pizzaId')
            ->andWhere('p.ingredient = :ingredientId')
            ->setParameter('pizzaId', $pizzaId)
            ->setParameter('ingredientId', $ingredientId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
