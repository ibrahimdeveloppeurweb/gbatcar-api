<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Searchable
{
    /**
     * @var boolean
     */
    public $searchable;

}


