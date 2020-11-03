<?php

namespace App\Controller;

use App\Constant\ErrorMessage;
use App\Constant\SerializeFormat;
use App\Entity\Ingredient;
use App\Factory\ErrorModel as ErrorFactory;
use App\Formatter\ConstraintViolationListErrorMessageInterface;
use App\Model\Error;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/v1", name="api_ingredient_")
 */
class IngredientController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var IngredientRepository
     */
    private $ingredientRepository;


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
        IngredientRepository $ingredientRepository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        ErrorFactory $errorFactory,
        ConstraintViolationListErrorMessageInterface $constraintViolationListErrorMessageFormatter
    ) {
        $this->serializer = $serializer;
        $this->ingredientRepository = $ingredientRepository;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->errorFactory = $errorFactory;
        $this->constraintViolationListErrorMessageFormatter = $constraintViolationListErrorMessageFormatter;
    }

    /**
     * @Cache(smaxage="3600")
     * @Route("/ingredient", name="list", methods="GET")
     * @OA\Get(
     *     summary="Retrieve the collection of ingredients",
     *     tags={"Ingredient"}
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return collection of ingredients",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Ingredient::class))
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
            $ingredients = $this->ingredientRepository->findAll();
            $ingredientsJson = $this->serializer->serialize(
                $ingredients,
                SerializeFormat::JSON_FORMAT
            );

            return new JsonResponse($ingredientsJson, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Cache(smaxage="3600")
     * @Route("/ingredient/{id}", name="show", methods="GET")
     * @OA\Get(
     *     summary="Retrieve an ingredient",
     *     tags={"Ingredient"}
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return ingredient",
     *     @Model(type=Ingredient::class)
     * )
     *  @OA\Response(
     *     response=404,
     *     description="Returned when ingredient not found"
     * )
     * @OA\Response (
     *     response=500,
     *     description="Returned when server error occured",
     *     @Model(type=Error::class)
     * )
     */
    public function show(Ingredient $ingredient): Response
    {
        try {
            $ingredientJson = $this->serializer->serialize(
                $ingredient,
                SerializeFormat::JSON_FORMAT,
                ['groups' => 'show_pizza']
            );

            return new JsonResponse($ingredientJson, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/ingredient", name="create", methods="POST")
     * @OA\Post(
     *     summary="Create an ingredient",
     *     tags={"Ingredient"}
     * )
     * @OA\Response(
     *     response=201,
     *     description="Returned when ingredient created successfully"
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
     *     description="Ingredient to add",
     *     required=true,
     *     @Model(type=Ingredient::class,  groups={"add_ingredient"})
     * )
     */
    public function create(Request $request): Response
    {
        try {
            $ingredient = $this->serializer->deserialize($request->getContent(), Ingredient::class, SerializeFormat::JSON_FORMAT);

            //validate datas
            $errors = $this->validator->validate($ingredient);
            if (count($errors) > 0) {
                $errorList = $this->constraintViolationListErrorMessageFormatter->format($errors);
                $error = $this->errorFactory->createError($errorList);
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($ingredient);
            $this->entityManager->flush($ingredient);

            return new JsonResponse([], JsonResponse::HTTP_CREATED);
        } catch (NotEncodableValueException $e) {
            $error = $this->errorFactory->createError(
                'An error occured with your datas. Please check datas and json format.'
            );
            return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/ingredient/{id}", name="update", methods="PUT")
     * @OA\Put(
     *     summary="Update an ingredient",
     *     description="Update existing ingredient",
     *     tags={"Ingredient"}
     * )
     * @OA\Response(
     *     response=204,
     *     description="Returned when ingredient updated successfully"
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
     * @OA\RequestBody(
     *     description="Ingredient to update",
     *     required=true,
     *     @Model(type=Ingredient::class, groups={"add_ingredient"})
     * )
     */
    public function update(Request $request, Ingredient $ingredient): Response
    {
        try {
            $this->serializer->deserialize($request->getContent(), Ingredient::class, SerializeFormat::JSON_FORMAT, ['object_to_populate' => $ingredient]);

            //validate datas
            $errors = $this->validator->validate($ingredient);
            if (count($errors) > 0) {
                $errorList = $this->constraintViolationListErrorMessageFormatter->format($errors);
                $error = $this->errorFactory->createError($errorList);
                return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($ingredient);
            $this->entityManager->flush($ingredient);

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);

        } catch (NotEncodableValueException $e) {
            $error = $this->errorFactory->createError(
                'An error occured with your datas. Please check datas and json format.'
            );
            return new JsonResponse($error, JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * @Route("/ingredient/{id}", name="delete", methods="DELETE")
     * @OA\Delete(
     *     summary="Delete an ingredient",
     *     tags={"Ingredient"}
     * )
     * @OA\Response(
     *     response=204,
     *     description="Returned when ingredient deleted successfully"
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
    public function delete(Ingredient $ingredient): Response
    {
        try {
            $this->entityManager->remove($ingredient);
            $this->entityManager->flush();

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            $error = $this->errorFactory->createError(ErrorMessage::DEFAULT_MESSAGE);
            return new JsonResponse($error, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
