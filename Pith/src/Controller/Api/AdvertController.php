<?php

namespace App\Controller\Api;

use App\Entity\Advert;
use App\Form\AdvertType;
use App\Repository\AdvertRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/advert", name="api_advert_")
 */
class AdvertController extends AbstractController
{
    /**
     * @Route("", name="browse", methods={"GET"})
     * @Route("/{tag}", name="browse_by_tag", methods={"GET"}, requirements={"tag"="\D+"})
     */
    public function browse(AdvertRepository $advertRepository, string $tag = null): Response
    {
        // If a tag is specified in the route parameters, then only the results that matches the tag are retrieved.
        // The condition can be replaced by " $request->attributes->get('_route') == 'api_advert_browse_by_tag' " instead.
        if($tag) {
            $adverts = $advertRepository->findByTag($tag);
        } else {
            $adverts = $advertRepository->findAll();
        }

        return $this->json($adverts, Response::HTTP_OK, []);
    }

    /**
     * @Route("/{id}", name="read", methods={"GET"})
     */
    public function read(Advert $advert): Response
    {
        return $this->json($advert, Response::HTTP_OK, []);
    }

    /**
     * @Route("", name="add", methods={"POST"})
     */
    public function add(Request $request): Response
    {
        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert, ['csrf_protection' => false]);

        $json = $request->getContent();
        $jsonArray = json_decode($json, true);

        $form->submit($jsonArray);

        if($form->isValid()) {

            $advert->setDateOfLoss(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();
            
            return $this->json($advert, Response::HTTP_CREATED, []);
        }

        $errorsString = (string) $form->getErrors(true);
        return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/{id}", name="edit", methods={"POST"})
     */
    public function edit(Advert $advert, Request $request): Response
    {
        $form = $this->createForm(AdvertType::class, $advert, ['csrf_protection' => false]);

        $json = $request->getContent();
        $jsonArray = json_decode($json, true);

        $form->submit($jsonArray);

        if($form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            
            return $this->json($advert, Response::HTTP_OK, []);
        }

        $errorsString = (string) $form->getErrors(true);
        return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Advert $advert)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($advert);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}