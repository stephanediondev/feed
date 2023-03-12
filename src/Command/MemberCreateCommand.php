<?php

namespace App\Command;

use App\Entity\Member;
use App\Manager\MemberManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:member:create', description: 'Create a new member')]
class MemberCreateCommand extends Command
{
    private MemberManager $memberManager;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(MemberManager $memberManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->memberManager = $memberManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = new QuestionHelper();

        $question = new Question('Email (required)? ');
        $email = $helper->ask($input, $output, $question);

        if ($email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $member = $this->memberManager->getOne(['email' => $email]);

                if ($member) {
                    $output->writeln('<comment>Email already recorded</comment>');
                } else {
                    $question = new Question('Password (required)? ');
                    $password = $helper->ask($input, $output, $question);

                    if ($password) {
                        $member = new Member();
                        $member->setEmail($email);
                        $member->setPassword($this->passwordHasher->hashPassword($member, $password));
                        $member->setAdministrator(true);

                        $this->memberManager->persist($member);

                        $output->writeln('<info>Member created</info>');
                    } else {
                        $output->writeln('<error>Password required</error>');
                    }
                }
            } else {
                $output->writeln('<error>Email not valid</error>');
            }
        } else {
            $output->writeln('<error>Email required</error>');
        }

        return Command::SUCCESS;
    }
}
