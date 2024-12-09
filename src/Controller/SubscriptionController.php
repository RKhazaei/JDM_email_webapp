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
use Symfony\Component\Mailer\MailerInterface; // Import the mailer interface
use Symfony\Component\Mime\Email as MimeEmail;// Import the email class

class SubscriptionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private MailerInterface $mailer; // Add MailerInterface to your dependencies

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->mailer = $mailer; // Initialize the mailer service
    }

    #[Route('/subscribe', name: 'subscribe', methods: ['GET', 'POST'])]
    public function subscribe(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $emailAddress = $request->request->get('email');

            // Validate the email
            $errors = $this->validator->validate($emailAddress, [
                new Assert\NotBlank(),
                new Assert\Email(),
            ]);

            if (count($errors) > 0) {
                $this->addFlash('error', "Invalid email address. Please try again.");
            } else {
                $existingEmail = $this->entityManager->getRepository(Email::class)->findOneBy(['email' => $emailAddress]);
                
                if ($existingEmail) {
                    $this->addFlash('error', "This email is already subscribed!");
                } else {
                    // Persist email into the database
                    $subscription = new Email();
                    $subscription->setEmail($emailAddress);

                    $this->entityManager->persist($subscription);
                    $this->entityManager->flush();

                    $this->addFlash('success', "Thank you for subscribing!");

                    // Send confirmation email
                    $this->sendConfirmationEmail($emailAddress);

                }
            }

            return $this->redirectToRoute('subscribe');
        }

        return $this->render('subscription/index.html.twig');
    }

    private function sendConfirmationEmail(string $emailAddress): void
    {
            $email = (new MimeEmail())
                ->from('rezakhazaei0405@gmail.com') // Replace with your email
                ->to($emailAddress)
                ->subject('Subscription Confirmation')
                ->html(
                    $this->renderView(
                        'emails/confirmation.html.twig',
                        ['email' => $emailAddress]
                    )
                );

            $this->mailer->send($email);
    }
}
