<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HomeController extends AbstractController
{
    #[Route('/')]
    public function home(NormalizerInterface $normalizer, #[CurrentUser] ?User $user = null): Response
    {
        $userData = $normalizer->normalize($user, 'jsonld', [
           'groups' => ['user:read']
        ]);

        return $this->render('ui/home.html.twig', [
            'userData' => $userData
        ]);
    }
}