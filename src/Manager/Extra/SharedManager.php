<?php

namespace App\Manager\Extra;

use App\Entity\Extra\File;
use App\Traits\PhotoTrait;
use App\Entity\Admin\User;
use App\Utils\TypeVariable;
use App\Annotation\Searchable;
use App\Traits\SearchableTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SharedManager
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var User */
    private $user;
    /** @var AnnotationReader */
    private $docReader;
    private $groups = ['default'];
    private $type;
    public function __construct(
        EntityManagerInterface $em, 
        TokenStorageInterface $tokenStorage,
        TypeVariable $type
    )
    {
        $this->em = $em;
        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
        $this->docReader = new AnnotationReader();
        $this->type = $type;
    }

    public function search($data)
    {
        return $this->processSearch($this->checkRequirements($data));
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function checkRequirements($data)
    {
        if (!array_key_exists('class', $data)) {
            throw new \Exception('Impossible d\'effectuer une recherche sans le paramètre class.');
        }
        $class = $data['class'];
        $namespace = "App\Entity\\".$data['namespace']."\\";
        $className = $namespace . $class;
        if (!array_key_exists('value', $data)) {
            throw new \Exception('Impossible d\'effectuer une recherche sans le paramètre valeur.');
        }
        $value = $this->type->text($data['value']);
        if (!class_exists($className)) {
            throw new \Exception('Impossible de trouver la classe ' . $className);
        }
        $object = new $className();
        if (!in_array(SearchableTrait::class, class_uses(get_class($object)), true)) {
            throw new \Exception('Impossible d\'effectuer une recherche dans une classe n\'utilisant pas SearchableTrait.');
        }
        if (in_array('groups', $data, true)) {
            if (is_array($data['groups'])) {
                $this->groups = $data['groups'];
            }
        }
        $params = [];
        if (isset($data['params']) && isset($data['params'][0])) {
            if (!array_key_exists('type', $data['params'][0]) && !array_key_exists('etat', $data['params'][0])) {
                throw new \Exception('Impossible d\'effectuer une recherche sans le paramètre type de ou etat.');
            }
            $params = $data['params'][0];
            $typeP = isset($params['type'][0]) ? $params['type'][0] : null;
            $etatP = isset($params['etat'][0]) ? $params['etat'][0] : null;
            if ($typeP && !property_exists($className, array_search(array_values($typeP)[0], $typeP))) {
                throw new \Exception('Impossible d\'effectuer une recherche la variable ' . array_search(array_values($typeP)[0], $typeP) . ' n\'existe pas dans la class '. $className . '.');
            }
            if ($etatP && !property_exists($className, array_search(array_values($etatP)[0], $etatP))) {
                throw new \Exception('Impossible d\'effectuer une recherche la variable ' . array_search(array_values($etatP)[0], $etatP) . ' n\'existe pas dans la class '. $className . '.');
            }
        }
        $reflect = new \ReflectionClass($object);
        $properties = $reflect->getProperties();
        $validProperties = [];
        foreach ($properties as $property) {
            if ($this->isSearchable($property)) {
                $validProperties[] = $property->getName();
            }
        }
        if (count($validProperties) === 0) {
            throw new \Exception('Cette classe ne contient aucune propriété supportant la recherche');
        }

        $results = [
            'class' => $class, 
            'className' => $className, 
            'value' => $value, 
            'groups' => $this->groups, 
            'properties' => $validProperties, 
            'params' => $params,
            'type' => $data['interface']
        ];
        return $results;
    }

    public function processSearch($params)
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select('object')
            ->from($params['className'], 'object');
        if($params['type'] === 'AGENCY' && $params['className'] !== 'App\Entity\Extra\Country') {
            $query->leftJoin('object.agency', 'agency');
        }
        if(($params['type'] === 'AGENCY' or $params['type'] === 'ADMIN') && $params['className'] === 'App\Entity\Admin\Service') {
            $query->leftJoin('object.agency', 'agency');
        }

        $orQuery = '(';
        foreach ($params['properties'] as $key => $property) {
            if ($key !== 0) {
                $orQuery .= ' OR ';
            }
            $orQuery .= 'LOWER(object.' . $property . ') LIKE :property_' . $key;
            $query->setParameter('property_' . $key, '%' . strtolower($params['value']) . '%');
        }
        $orQuery .= ')';
        $query->where($orQuery);
        foreach ($params['params'] as $key => $item) {
            $value = array_values($item[0])[0];
            $property = array_search($value, $item[0]);
            $query->andWhere('object.'. $property . ' = :champs_' . $key)
                ->setParameter('champs_' . $key, $value);
        }
        if($params['type'] === 'AGENCY' && $params['className'] !== 'App\Entity\Extra\Country') {
            $query->andWhere('agency = :agency')
                ->setParameter('agency', $this->user->getAgency());
        }
        if(($params['type'] === 'AGENCY' or $params['type'] === 'ADMIN') && $params['className'] === 'App\Entity\Admin\Service') {
            $query->andWhere('agency IS NULL');
        }

        $query->getParameters();    
        $entities = $query->setMaxResults(3000)->getQuery()->getResult();
        $map = [];
        foreach ($entities as $entity) {
            try {
                $uuid = (string)$entity->getUuid();
            } catch (\Throwable $exception) {
                throw new \Exception('La classe ' . $params['class'] . ' ne contient pas de paramètre uuid');
            }
            $photoSrc = null;
            if (in_array(PhotoTrait::class, class_uses(get_class($entity)), true)) {
                if ($entity->getPhoto() instanceof File) {
                    $photoSrc = $entity->getPhotoSrc();
                }
            }
            $map[] = [
                'id' => $entity->getId(),
                'uuid' => $uuid,
                'title' => $entity->getSearchableTitle(),
                'detail' => $entity->getSearchableDetail(),
                'photoSrc' => $photoSrc
            ];
        }
        return $map;
    }

    public function isSearchable(\ReflectionProperty $property)
    {
        $docInfos = $this->docReader->getPropertyAnnotations($property);
        foreach ($docInfos as $docInfo) {
            if ($docInfo instanceof Searchable) {
                return true;
            }
        }
        return false;
    }
}
