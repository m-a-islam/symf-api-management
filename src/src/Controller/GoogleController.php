<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    /**
     * This route is the starting point. It redirects the user to Google's login page.
     */
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {

        // This will fetch the 'google' client we configured in our knp_oauth2_client.yaml file
        $client = $clientRegistry->getClient('google');
        // This creates the redirect response and sends the user to Google.
        // The 'profile' and 'email' scopes ask for permission to view the user's basic profile and email.
        return $client->redirect(['profile', 'email']);
    }

    /**
     * This is the route Google redirects the user back to after they have authenticated.
     * For now, it will be empty. We will build an authenticator to handle the logic in the next step.
     */
    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        // This action will be intercepted by our security authenticator.
        // We don't need to write any code here yet.
    }
}
