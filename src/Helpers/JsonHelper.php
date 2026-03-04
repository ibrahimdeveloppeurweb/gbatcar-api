<?php

namespace App\Helpers;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

class JsonHelper
{
    /**
     * @var
     */
    private $status;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $meta;

    /**
     * @var array
     */
    private $errors;
    private $serializer;

    public function __construct($data, $message, $status, int $code, $errors = [], array $meta = [])
    {
        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getUuid();
            },
        ];
        $normalizer = new ObjectNormalizer(
            null,
            null,
            null,
            null,
            null,
            null,
            $defaultContext
        );

        $this->serializer = new Serializer([$normalizer], [$encoder]);

        $this->code = $code;
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->meta = $meta;
        if ($data instanceof Paginator) {
            $this->data = $data->getIterator()->getArrayCopy();
            $limit = $data->getQuery()->getMaxResults();
            $first = $data->getQuery()->getFirstResult();
            $currentPage = ceil($first / $limit) + 1;
            $total = $data->count();
            $pages = ceil($total / $limit) ?: 1;
            $this->meta = [
                'current_page' => $currentPage,
                'first_page' => 1,
                'last_page' => $pages,
                'total' => $data->count()
            ];
        }
        $this->errors = $errors;
    }

    static function getSerializedEntity(object $entity, array $groups)
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $encoders = [new JsonEncoder()];
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([new DateTimeNormalizer(), $normalizer], $encoders);

        $data = $serializer->serialize($entity, 'json', $groups);
        return $data;
    }

    public function serialize()
    {
        $serialize = [
            'data' => $this->data,
            'status' => $this->status,
            'code' => $this->code,
            'message' => $this->message
        ];

        if (!empty($this->meta)) {
            $serialize['meta'] = $this->meta;
        }

        if (!empty($this->errors)) {
            $serialize['errors'] = $this->errors;
        }
        return $serialize;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCode()
    {
        return $this->code;
    }
}
