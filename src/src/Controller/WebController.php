<?php

namespace App\Controller;

use App\Entity\Key;
use App\Repository\KeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WebController extends AbstractController
{
    /**
     * This is the homepage of the web application.
     * It fetches all keys and renders the main HTML page.
     */
    #[Route('/', name: 'web_key_index', methods: ['GET'])]
    public function index(KeyRepository $keyRepository): Response
    {
        // Fetch all keys from the database
        $keys = $keyRepository->findAll();

        // Render the Twig template and pass the 'keys' variable to it.
        // We will need to create this template.
        return $this->render('web/normalIndex.html.twig',
            [
                'keys' => $keys,
                'controller_name' => 'WebController'
            ]);

        /*
        return $this->render('web/index.html.twig', [
            'keys' => $keys,
            'controller_name' => 'WebController',
        ]); */
    }

    /**
     * Handles the form submission for creating a new key.
     */
    #[Route('/create', name: 'web_key_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // Get the submitted form data from the 'keyIdentifier' input field
        $keyIdentifier = $request->request->get('keyIdentifier');

        // Basic validation: only create if the identifier is not empty
        if ($keyIdentifier) {
            $key = new Key();
            $key->setKeyIdentifier($keyIdentifier);

            $em->persist($key);
            $em->flush();
        }

        // Redirect back to the homepage after creating the key
        return $this->redirectToRoute('web_key_index');
    }

    /**
     * Toggles the status of a key from 'active' to 'inactive' and vice-versa.
     */
    #[Route('/key/{id}/toggle-status', name: 'web_key_toggle_status', methods: ['POST'])]
    public function toggleStatus(Key $key, EntityManagerInterface $em): Response
    {
        // Symfony's ParamConverter finds the key for us by its {id}
        $newStatus = 'active' === $key->getStatus() ? 'inactive' : 'active';
        $key->setStatus($newStatus);

        $em->flush(); // Save the change to the database

        // Redirect back to the homepage
        return $this->redirectToRoute('web_key_index');
    }

    /**
     * Deletes a key.
     */
    #[Route('/key/{id}/delete', name: 'web_key_delete', methods: ['POST'])]
    public function delete(Key $key, EntityManagerInterface $em): Response
    {
        // ParamConverter finds the key for us
        $em->remove($key);
        $em->flush();

        // Redirect back to the homepage
        return $this->redirectToRoute('web_key_index');
    }
}
