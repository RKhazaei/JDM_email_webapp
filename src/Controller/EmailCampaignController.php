<?php

namespace App\Controller;

use App\Entity\EmailCampaign;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailCampaignController extends AbstractController
{
    #[Route('/campaign/new', name: 'campaign_new', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $subject = $request->request->get('subject');
            $body = $request->request->get('body');

            $campaign = new EmailCampaign();
            $campaign->setSubject($subject)
                ->setBody($body)
                ->setCreatedAt(new \DateTime());

            $em->persist($campaign);
            $em->flush();
            
            $this->addFlash('success', 'Email campaign created successfully!');
            return $this->redirectToRoute('campaign_new');
        }
       

        return $this->render('campaign/new.html.twig');
    }
   
}
