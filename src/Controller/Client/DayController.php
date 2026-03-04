<?php

namespace App\Controller\Client;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(path="/api/private/agency/day")
 */
class DayController extends AbstractController
{
    private $dayManager;
    private $dayRepository;
    private $treasuryRepository;
    public function __construct(
       
    )
    {
      
    }

    /**
     * @Route("/", name="index_day", methods={"GET"}, 
     * options={"description"="Liste des journées", "permission"="DAY:LIST"})
     */
    public function index(Request $request)
    {
        dd($this->getUser());
      
    }


}