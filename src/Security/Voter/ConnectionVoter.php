<?php

namespace App\Security\Voter;

use App\Entity\Connection;
use App\Entity\Member;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConnectionVoter extends AbstractVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Connection || 'connection' === $subject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (false === $user instanceof Member) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }
}
