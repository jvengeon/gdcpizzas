<?php

namespace App\Controller;

use App\Constant\ErrorMessage;
use App\Constant\SerializeFormat;
use App\Entity\Ingredient;
use App\Entity\Pizza;
use App\Entity\PizzaIngredient;
use App\Exception\PizzaException;
use App\Factory\ErrorModel as ErrorFactory;
use App\Formatter\ConstraintViolationListErrorMessageInterface;
use App\Model\Error;
use App\Repository\IngredientRepository;
use App\Repository\PizzaIngredientRepository;
use App\Repository\PizzaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/v1", name="api_pizza_")
 */
class PizzaController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PizzaRepository
     */
    private $pizzaRepository;

    /**
     * @var IngredientRepository
     */
    private $ingredientRepository;

    /**
     * @var PizzaIngredientRepository
     */
    private $pizzaIngredientRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ErrorFactory
     */
    private $errorFactory;

    /**
     * @var ConstraintViolationListErrorMessageInterface
     */
    private $constraintViolationListErrorMessageFormatter;


    public function __construct(
        SerializerInterface $serializer,
        PizzaRepository $pizzaRepository,
        IngredientRepository $ingredientRepository,
        PizzaIngredientRepository $pizzaIngredientRepository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        ErrorFactory $errorFactory,
        ConstraintViolationListErrorMessageInterface $constraintViolationListErrorMessageFormatter
    ) {
        $this->serializer = $serializer;
        $this->pizzaRepository = $pizzaRepository;
        $this->ingredientRepository = $ingredientRepository;
        $this->pizzaIngredientRepository = $pizzaIngredientRepository;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->errorFactory = $errorFactory;
        $this->constraintViolationListErrorMessageFormatter = $constraintViolationListErrorMessageFormatter;
    }

    /**
     * @Cache(smaxage="3600")
     * @Route("/pizza", name="list", methods="GET")
     * @OA\Get(
     *     summary="Retrieve the collection of pizzas",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return collection of pizzas",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Pizza::class, groups={"show_pizza"}))
     *     )
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     */
    public function list(): Response
    {
        try {
            $pizzas = $this->pizzaRepository->findAll();
            $pizzasJson = $this->serializer->serialize(
                $pizzas,
                SerializeFormat::JSON_FORMAT,
                ['groups' => 'show_pizza']
            );

            return new JsonResponse($pizzasJson, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Cache(smaxage="3600")
     * @Route("/pizza/{id}", name="show", methods="GET")
     * @OA\Get(
     *     summary="Retrieve a pizza",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return pizza",
     *     @Model(type=Pizza::class, groups={"show_pizza"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="Returned when ingredient not found"
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     */
    public function show(Pizza $pizza): Response
    {
        try {
            $pizzaJson = $this->serializer->serialize(
                $pizza,
                SerializeFormat::JSON_FORMAT,
                ['groups' => 'show_pizza']
            );

            return new JsonResponse($pizzaJson, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/pizza", name="create", methods="POST")
     * @OA\Post(
     *     summary="Create a pizza",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=201,
     *     description="Returned when pizza created successfully"
     * )
     * @OA\Response (
     *     response=400,
     *     description="Returned when request information are malformed or invalid",
     *     @Model(type=Error::class)
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     * @OA\RequestBody(
     *     description="Pizza to add",
     *     required=true,
     *     @Model(type=Pizza::class, groups={"add_pizza"})
     * )
     */
    public function create(Request $request): Response
    {
        try {
            $pizza = $this->serializer->deserialize($request->getContent(), Pizza::class, SerializeFormat::JSON_FORMAT);

            //validate datas
            $errors = $this->validator->validate($pizza);
            if (count($errors) > 0) {
                $errorList = $this->constraintViolationListErrorMessageFormatter->format($errors);
                $error = $this->errorFactory->createError($errorList);
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($pizza);
            $this->entityManager->flush($pizza);

            return new JsonResponse([], JsonResponse::HTTP_CREATED);
        } catch (NotEncodableValueException $e) {
            $error = $this->errorFactory->createError(
                'An error occured with your datas. Please check datas and json format.'
            );
            return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/pizza/{id}", name="update", methods="PUT")
     * @OA\Put(
     *     summary="Update a pizza",
     *     description="Update existing pizza. Ingredients are replaced by those provided in body ",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=204,
     *     description="Returned when pizza updated successfully"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Returned when pizza not found"
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     * @OA\RequestBody(
     *     description="Pizza to update",
     *     required=true,
     *     @Model(type=Pizza::class, groups={"add_pizza"})
     * )
     */
    public function update(Request $request, Pizza $pizza): Response
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Pizza::class,
                SerializeFormat::JSON_FORMAT,
                ['object_to_populate' => $pizza]
            );

            //validate datas
            $errors = $this->validator->validate($pizza);
            if (count($errors) > 0) {
                $errorList = $this->constraintViolationListErrorMessageFormatter->format($errors);
                $error = $this->errorFactory->createError($errorList);
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($pizza);
            $this->entityManager->flush($pizza);

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
        } catch (NotEncodableValueException $e) {
            $error = $this->errorFactory->createError(
                'An error occured with your datas. Please check datas and json format.'
            );
            return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/pizza/{id}", name="delete", methods="DELETE")
     * @OA\Delete(
     *     summary="Delete a pizza",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=204,
     *     description="Returned when pizza deleted successfully"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Returned when pizza not found"
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     */
    public function delete(Pizza $pizza): Response
    {
        try {
            $this->entityManager->remove($pizza);
            $this->entityManager->flush();

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/pizza/{id}/ingredient", name="add_ingredient", methods="POST")
     * @OA\Post(
     *     summary="Add ingredient to a pizza",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=204,
     *     description="Returned when ingredient added successfully"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Returned when pizza not found"
     * )
     * @OA\Response (
     *     response=400,
     *     description="Returned when request information are malformed or invalid or if ingredient already exist for this pizza",
     *     @Model(type=Error::class)
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     * @OA\RequestBody(
     *     description="Ingredient to add",
     *     required=true,
     *     @Model(type=PizzaIngredient::class, groups={"add_pizza"})
     * )
     */
    public function addIngredient(Request $request, Pizza $pizza): Response
    {
        try {
            $pizzaIngredient = $this->serializer->deserialize(
                $request->getContent(),
                PizzaIngredient::class,
                SerializeFormat::JSON_FORMAT
            );

            //validate datas
            $errors = $this->validator->validate($pizzaIngredient);
            if (count($errors) > 0) {
                $errorList = $this->constraintViolationListErrorMessageFormatter->format($errors);
                $error = $this->errorFactory->createError($errorList);
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            //check if this ingredient already exist for this pizza
            $pizzaIngredientEntity = $this->pizzaIngredientRepository->findOneByPizzaIdAndIngredientId(
                $pizza->getId(),
                $pizzaIngredient->getIngredient()->getId()
            );
            if ($pizzaIngredientEntity instanceof PizzaIngredient) {
                $error = $this->errorFactory->createError(
                    'This ingredient already exist for this pizza. Use PUT method if you want to change priority'
                );
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            $pizzaIngredient->setPizza($pizza);
            $this->entityManager->persist($pizzaIngredient);

            $pizza->addIngredient($pizzaIngredient);

            $this->entityManager->persist($pizza);
            $this->entityManager->flush($pizza);

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
        } catch (NotEncodableValueException $e) {
            $error = $this->errorFactory->createError(
                'An error occured with your datas. Please check datas and json format.'
            );
            return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/pizza/{id}/ingredient/{ingredient_id}", name="remove_ingredient", methods="DELETE")
     * @ParamConverter("ingredient", options={"mapping": {"ingredient_id": "id"}})
     * @OA\Delete(
     *     summary="Remove ingredient from a pizza",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=204,
     *     description="Returned when ingredient removed successfully"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Returned when pizza or ingredient not found"
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     */
    public function removeIngredient(Pizza $pizza, Ingredient $ingredient): Response
    {
        try {
            $pizzaIngredient = $this->pizzaIngredientRepository->findOneByPizzaIdAndIngredientId(
                $pizza->getId(),
                $ingredient->getId()
            );
            $pizza->removeIngredient($pizzaIngredient);

            $this->entityManager->persist($pizza);
            $this->entityManager->flush($pizza);

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/pizza/{id}/ingredient/{ingredient_id}", name="update_ingredient", methods="PUT")
     * @ParamConverter("ingredient", options={"mapping": {"ingredient_id": "id"}})
     * @OA\Put(
     *     summary="Update ingredient priority from a pizza",
     *     tags={"Pizza"}
     * )
     * @OA\Response(
     *     response=204,
     *     description="Returned when ingredient priority updated successfully"
     * )
     * @OA\Response (
     *     response=400,
     *     description="Returned when request information are malformed or invalid or if ingredient does not exist for this pizza",
     *     @Model(type=Error::class)
     * )
     * @OA\Response(
     *     response=404,
     *     description="Returned when pizza or ingredient not found"
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     * @OA\RequestBody(
     *     description="New priority value",
     *     required=true,
     *     @Model(type=PizzaIngredient::class, groups={"update_ingredient"})
     * )
     */
    public function updateIngredientPriority(Request $request, Pizza $pizza, Ingredient $ingredient): Response
    {
        try {
            $pizzaIngredient = $this->serializer->deserialize(
                $request->getContent(),
                PizzaIngredient::class,
                SerializeFormat::JSON_FORMAT
            );

            //validate datas
            if (empty($pizzaIngredient->getPriority())) {
                $error = $this->errorFactory->createError("Priority cannot be empty or superior to 0");
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            //check if this ingredient already exist for this pizza
            $pizzaIngredientEntity = $this->pizzaIngredientRepository->findOneByPizzaIdAndIngredientId(
                $pizza->getId(),
                $ingredient->getId()
            );

            if (!$pizzaIngredientEntity instanceof PizzaIngredient) {
                $error = $this->errorFactory->createError('This ingredient does not exist for this pizza.');
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            $pizzaIngredientEntity->setPriority($pizzaIngredient->getPriority());
            $this->entityManager->persist($pizzaIngredientEntity);
            $this->entityManager->flush($pizzaIngredientEntity);

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
