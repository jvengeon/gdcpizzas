<?php

namespace App\Entity;

use App\Repository\PizzaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PizzaRepository::class)
 */
class Pizza
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"show_pizza"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"show_pizza", "add_pizza"})
     * @Assert\NotBlank(message="Pizza name cannot be empty")
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * @Groups({"show_pizza"})
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=PizzaIngredient::class, mappedBy="pizza", orphanRemoval=true, cascade={"persist"})
     * @Groups({"show_pizza", "add_pizza"})
     * @Assert\Valid()
     */
    private $ingredients;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|PizzaIngredient[]
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(PizzaIngredient $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients[] = $ingredient;
            $ingredient->setPizza($this);
        }

        return $this;
    }

    public function removeIngredient(PizzaIngredient $ingredient): self
    {
        if ($this->ingredients->removeElement($ingredient)) {
            // set the owning side to null (unless already changed)
            if ($ingredient->getPizza() === $this) {
                $ingredient->setPizza(null);
            }
        }

        return $this;
    }

    public function calculatePrice(): float
    {
        $price = 0;
        foreach($this->getIngredients() as $ingredient) {
            $price += $ingredient->getIngredient()->getCost();
        }

        return $price + ($price / 2);
    }
}
