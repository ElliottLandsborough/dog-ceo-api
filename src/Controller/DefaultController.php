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
     * @Route("/")
     */
    public function index(): ?RedirectResponse
    {
        return $this->redirect('https://dog.ceo/dog-api');
    }

    /**
     * @Route("/breeds/list/all")
     */
    public function getAllBreeds(): ?JsonResponse
    {
        return $this->breedUtil->getAllBreeds()->jsonResponse();
    }

    /**
     * @Route("/breeds/list")
     */
    public function getAllTopLevelBreeds(): ?JsonResponse
    {
        return $this->breedUtil->getAllTopLevelBreeds()->jsonResponse();
    }

    /**
     * @route("/breed/{breed}/list")
     */
    public function getAllSubBreeds(string $breed): ?JsonResponse
    {
        return $this->breedUtil->getAllSubBreeds($breed)->jsonResponse();
    }

    /**
     * @route("/breed/{breed}/images")
     */
    public function getTopLevelImages(string $breed): ?JsonResponse
    {
        return $this->breedUtil->getTopLevelImages($breed)->jsonResponse();
    }
}
