<?php

namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class EntityNormalizer
 * @package App\Normalizer
 * This class allow to deserialize entity relation from his id
 */
class EntityNormalizer extends ObjectNormalizer
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
        ?NameConverterInterface $nameConverter = null,
        ?PropertyAccessorInterface $propertyAccessor = null,
        ?PropertyTypeExtractorInterface $propertyTypeExtractor = null
    ) {
        $this->entityManager = $entityManager;

        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return (strpos($type, 'App\\Entity\\') === 0) &&
            (is_numeric($data) || is_string($data) || (is_array($data) && isset($data['id'])));
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->entityManager->find($type, $data);
    }
}
