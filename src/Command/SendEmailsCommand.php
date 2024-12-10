<?php

namespace App\Command;

use App\Entity\Email;
use App\Entity\EmailCampaign;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as MimeEmail;

class SendEmailsCommand extends Command
{
    protected static $defaultName = 'app:send-emails';
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send the latest email campaign to all subscribers.')
            ->setHelp('This command allows you to send the most recent email campaign to all subscribers in the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Fetch the latest email campaign
        $campaign = $this->entityManager->getRepository(EmailCampaign::class)->findOneBy([], ['createdAt' => 'DESC']);

        if (!$campaign) {
            $output->writeln('<error>No email campaign found.</error>');
            return Command::FAILURE;
        }

        // Fetch all subscribers
        $subscribers = $this->entityManager->getRepository(Email::class)->findAll();

        if (empty($subscribers)) {
            $output->writeln('<comment>No subscribers found.</comment>');
            return Command::SUCCESS;
        }

        // Send the email to each subscriber
        foreach ($subscribers as $subscriber) {
            $email = (new MimeEmail())
                ->from('rezakhazaei0405@gmail.com') // Replace with your email
                ->to($subscriber->getEmail())
                ->subject($campaign->getSubject())
                ->html($campaign->getBody());

            $this->mailer->send($email);
            $output->writeln('Email sent to ' . $subscriber->getEmail());
        }

        $output->writeln('<info>All emails have been sent successfully.</info>');
        return Command::SUCCESS;
    }
    
    
}
