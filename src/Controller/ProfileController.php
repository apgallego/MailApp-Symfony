<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProfileFormType;

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
        return new Response('Well hi there ' . $user->getName());
    }

    #[Route('/profile', name: 'profile')]
    public function customizeProfile(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $entityManager = $doctrine->getManager();

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

            // if($_SERVER["REQUEST_METHOD"] == "POST"){
        if ($form->isSubmitted() && $form->isValid()) {
            // profile pic management
            $pfp = $form->get('pfp')->getData();
            if ($pfp) {
                $originalFilename = pathinfo($pfp->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pfp->guessExtension();
                try {
                    $pfp->move(
                        $this->getParameter('uploads'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    echo ("Could not upload file");
                }

                // updates the 'pfpname' property to store the image file name
                $user->setPfp($newFilename);
                // }else{
                    //     $user->setPfp($this->getParameter('default'));
            }
                
            $username = $form->get('username')->getData();
            $telephone = $form->get('telephone')->getData();

        /*  $username = $_POST['custom-username'];
            $telephone = $_POST['custome-telephone'];

            if(isset($_FILES['pfp'])){
                $filename = $_FILES['pfp']['name'];
                $file_tmp =$_FILES['pfp']['tmp_name'];
                try {
                    move_uploaded_file($file_tmp,$this->getParameter('uploads').$filename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    echo("Could not upload file");
                }
                // updates the 'pfpname' property to store the image file name
                $user->setPfp($filename);
            } */

            $user->setUsername($username);
            $user->setTelephone($telephone);

            $entityManager->persist($user);
            $entityManager->flush();
    
            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/profile.html.twig', [
            'profileForm' => $form->createView(),
            'userPfp' => $user->getPfp()
        ]);
    }
}
