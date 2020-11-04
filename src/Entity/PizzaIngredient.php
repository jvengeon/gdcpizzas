<?php

namespace App\Entity;

use App\Repository\PizzaIngredientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PizzaIngredientRepository::class)
 */
class PizzaIngredient
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Ignore()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pizza::class, inversedBy="ingredients")
     * @ORM\JoinColumn(nullable=false)
     * @Ignore()
     */
    private $pizza;

    /**
     * @ORM\ManyToOne(targetEntity=Ingredient::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"show_pizza", "add_pizza"})
     * @Assert\NotBlank(message="Ingredient cannot be empty or must exist")
     */
    private $ingredient;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"show_pizza", "add_pizza", "update_ingredient"})
     * @Assert\NotBlank(message="Ingredient priority cannot be empty")
     */
    private $priority;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPizza(): ?Pizza
    {
        return $this->pizza;
    }

    public function setPizza(?Pizza $pizza): self
    {
        $this->pizza = $pizza;

        return $this;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
