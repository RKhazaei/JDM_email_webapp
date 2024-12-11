<?php

namespace App\Controller;

use App\Entity\EmailCampaign;
use App\Repository\EmailRepository; // Use your Email repository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailCampaignController extends AbstractController
{
    #[Route('/campaign/new', name: 'campaign_new', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        EmailRepository $emailRepository // Repository to fetch email addresses
    ): Response {
        if ($request->isMethod('POST')) {
            $subject = $request->request->get('subject');
            $body = $request->request->get('body');

            // Save the campaign in the database
            $campaign = new EmailCampaign();
            $campaign->setSubject($subject)
                ->setBody($body)
                ->setCreatedAt(new \DateTime());

            $em->persist($campaign);
            $em->flush();

            // Fetch all email addresses from the Email entity
            $emails = $emailRepository->findAll();

            // Send the campaign email to each recipient
            foreach ($emails as $emailEntity) {
                $recipient = $emailEntity->getEmail(); // Get email address
                $email = (new Email())
                    ->from('rezakhazaei0405@gmail.com') // Replace with your sender email
                    ->to(trim($recipient))
                    ->subject($subject)
                    ->text($body)
                    ->html(nl2br($body));

                try {
                    $mailer->send($email);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error sending email to ' . $recipient . ': ' . $e->getMessage());
                    continue;
                }
            }

            $this->addFlash('success', 'Email campaign created and emails sent to all recipients!');
            return $this->redirectToRoute('campaign_new');
        }

        return $this->render('campaign/new.html.twig');
    }
}
