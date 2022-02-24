<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\UserRepository;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends AbstractController
{
    public function index(): Response
    {
        // usually you'll want to make sure the user is authenticated first,
        // see "Authorization" below
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // returns your User object, or null if the user is not authenticated
        // use inline documentation to tell your editor your exact User class
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Call whatever methods you've added to your User class
        // For example, if you added a getFirstName() method, you can use that.
        return new Response('Well hi there '.$user->getFirstName());
    }

    #[Route('/profile', name: 'profile')]
    public function customizeProfile(ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $entityManager = $doctrine->getManager();

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            //DATA FROM FORM
            $user->setUsername($_POST['custom-username']);
            $user->setTelephone($_POST['custom-telephone']);
            if(isset($_POST['custom-pfp']) && $_POST['custom-pfp'] !== '') $user->setPfp($_POST['custom-pfp']);
            else $user->setPfp('pfp_default.jpg');

            //Save changes in database
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('profile');
        }
        return $this->render('profile/profile.html.twig', [
            'userPfp' => $user->getPfp()
        ]);
    }
}