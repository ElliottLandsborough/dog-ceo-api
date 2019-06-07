<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Util\BreedUtil;

class DefaultController extends AbstractController
{
    protected $breedUtil;

    public function __construct(BreedUtil $breedUtil)
    {
        $this->breedUtil = $breedUtil;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(): ?RedirectResponse
    {
        return $this->redirect('https://dog.ceo/dog-api');
    }

    /**
     * @Route("/breeds/list/all")
     */
    public function listAllBreeds()
    {
        $response = new JsonResponse($this->breedUtil->getAllBreeds());
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /**
     * @Route("/breeds/list")
     */
    public function listAllTopLevelBreeds()
    {
        $response = new JsonResponse($this->breedUtil->getTopLevelBreeds());
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
