<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Util\BreedUtil;

class DefaultController extends AbstractController
{
    protected $breedUtil;

    public function __construct(BreedUtil $breedUtil)
    {
        $this->breedUtil = $breedUtil;
    }

    /**
     * @Route("/", methods={"GET"})
     */
    public function index(): ?RedirectResponse
    {
        return $this->redirect('https://dog.ceo/dog-api');
    }

    /**
     * @Route("/breeds/list/all", methods={"GET","HEAD"})
     */
    public function getAllBreeds(): ?object
    {
        return $this->breedUtil->getAllBreeds()->getResponse();
    }

    /**
     * @Route("/breeds/list", methods={"GET","HEAD"})
     */
    public function getAllTopLevelBreeds(): ?object
    {
        return $this->breedUtil->getAllTopLevelBreeds()->getResponse();
    }

    /**
     * @route("/breed/{breed}/list", methods={"GET","HEAD"})
     */
    public function getAllSubBreeds(string $breed): ?object
    {
        return $this->breedUtil->getAllSubBreeds($breed)->getResponse();
    }

    /**
     * @route("/breed/{breed}/images", methods={"GET","HEAD"})
     */
    public function getTopLevelImages(string $breed): ?object
    {
        return $this->breedUtil->getTopLevelImages($breed)->getResponse();
    }

    /**
     * @route("/breed/{breed}/images/random", methods={"GET","HEAD"})
     */
    public function getRandomTopLevelImage(string $breed): ?object
    {
        return $this->breedUtil->getRandomTopLevelImage($breed)->getResponse();
    }

    /**
     * @route("/breed/{breed}/images/random/{amount}", methods={"GET","HEAD"})
     */
    public function getRandomTopLevelImages(string $breed, int $amount): ?object
    {
        return $this->breedUtil->getRandomTopLevelImages($breed, $amount)->getResponse();
    }

    /**
     * @route("/breed/{breed1}/{breed2}/images", methods={"GET","HEAD"})
     */
    public function getSubLevelImages(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->getSubLevelImages($breed1, $breed2)->getResponse();
    }

    /**
     * @route("/breed/{breed1}/{breed2}/images/random", methods={"GET","HEAD"})
     */
    public function getRandomSubLevelImage(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->getRandomSubLevelImage($breed1, $breed2)->getResponse();
    }

    /**
     * @route("/breed/{breed1}/{breed2}/images/random/{amount}", methods={"GET","HEAD"})
     */
    public function getRandomSubLevelImages(string $breed1, string $breed2, int $amount): ?object
    {
        return $this->breedUtil->getRandomSubLevelImages($breed1, $breed2, $amount)->getResponse();
    }

    /**
     * @route("/breeds/image/random", methods={"GET","HEAD"})
     */
    public function getRandomImage(): ?object
    {
        return $this->breedUtil->getRandomImage()->getResponse();
    }
    
    /**
     * @route("/breeds/image/random/{amount}", methods={"GET","HEAD"})
     */
    public function getRandomImages(int $amount): ?object
    {
        return $this->breedUtil->getRandomImages($amount)->getResponse();
    }

    /**
     * @route("/breed/{breed}", methods={"GET","HEAD"})
     */
    public function masterText(string $breed): ?object
    {
        return $this->breedUtil->masterText($breed)->getResponse();
    }

    /**
     * @route("/breed/{breed1}/{breed2}", methods={"GET","HEAD"})
     */
    public function subText(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->subText($breed1, $breed2)->getResponse();
    }

    /**
     * @route("/breed/{breed}/images/alt", methods={"GET","HEAD"})
     */
    public function getTopLevelImagesWithAltTags(string $breed): ?object
    {
        return $this->breedUtil->getTopLevelImagesWithAltTags($breed)->getResponse();
    }

    /**
     * @route("/breed/{breed}/images/random/{amount}/alt", methods={"GET","HEAD"})
     */
    public function getRandomTopLevelImagesWithAltTags(string $breed, int $amount): ?object
    {
        return $this->breedUtil->getRandomTopLevelImagesWithAltTags($breed, $amount)->getResponse();
    }

    /**
     * @route("/breed/{breed1}/{breed2}/images/alt", methods={"GET","HEAD"})
     */
    public function getSubLevelImagesWithAltTags(string $breed1, string $breed2): ?object
    {
        return $this->breedUtil->getSubLevelImagesWithAltTags($breed1, $breed2)->getResponse();
    }

    /**
     * @route("/breed/{breed1}/{breed2}/images/random/{amount}/alt", methods={"GET","HEAD"})
     */
    public function getRandomSubLevelImagesWithAltTags(string $breed1, string $breed2, int $amount): ?object
    {
        return $this->breedUtil->getRandomSubLevelImagesWithAltTags($breed1, $breed2, $amount)->getResponse();
    }

    /**
     * @route("/breeds/image/random/{amount}/alt", methods={"GET","HEAD"})
     */
    public function getRandomImagesWithAltTags(int $amount): ?object
    {
        return $this->breedUtil->getRandomImagesWithAltTags($amount)->getResponse();
    }
}
