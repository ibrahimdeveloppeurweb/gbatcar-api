<?php

namespace App\Traits;

/*
 * @ExclusionPolicy("all")
*/
use Doctrine\ORM\EntityManagerInterface;

trait EntityTrait
{
    public function hydrate(array $data, EntityManagerInterface $em = null)
    {
        foreach ($data[0] as $key => $value) {
            $method = str_replace('_', '', ucwords('set'.ucfirst($key), '_'));
            $method[0] = strtolower($method[0]);
            if (method_exists($this, $method) && $value != null) {
                if($em && is_object($value) && property_exists(get_class($value), 'id')){
                    $value = $em->find(get_class($value), $value->getId()) ?: $value;
                }
                $this->$method($value);
            }
        }
    }

    public function getField($field) {
        // on récupère le nom des setters correspondants
        // si la clef est placesTotales son setter est setPlacesTotales
        // il suffit de mettre la 1ere lettre de key en Maj et de le préfixer par set
        $method = 'get'.ucwords($field);
        // on vérifie que le setter correspondant existe
        $result = $this;
        if (method_exists($this, $method)) {
            // si il existe, on l'appelle
            $result = $this->$method();
        }
        return $result;
    }

    public function getValueByAttribute($field) {
        $field = str_replace('_', '', ucwords('get'.ucfirst($field), '_'));
        $method = $field;
        $method[0] = strtolower($method[0]);
        $result = $this;
        if (method_exists($this, $method)) {
            $result = $this->$method();
        }
        return $result;
    }
}
