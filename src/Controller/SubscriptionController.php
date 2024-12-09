<?php

namespace App\Controller;

use App\Entity\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubscriptionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/subscribe', name: 'subscribe', methods: ['GET', 'POST'])]
    public function subscribe(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // Validate the email
            $errors = $this->validator->validate($email, [
                new Assert\NotBlank(),
                new Assert\Email(),
            ]);

            if (count($errors) > 0) {
                $this->addFlash('error', "Invalid email address. Please try again.");
            } else {
                $existingEmail = $this->entityManager->getRepository(Email::class)->findOneBy(['email' => $email]);
                
                if ($existingEmail) {
                    $this->addFlash('error', "This email is already subscribed!");
                } else {
                    // Persist email into the database
                    $subscription = new Email();
                    $subscription->setEmail($email);

                    $this->entityManager->persist($subscription);
                    $this->entityManager->flush();

                    $this->addFlash('success', "Thank you for subscribing!");
                }
            }

            return $this->redirectToRoute('subscribe');
        }

        return $this->render('subscription/index.html.twig');
    }
}
