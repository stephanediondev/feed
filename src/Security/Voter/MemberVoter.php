<?php

namespace App\Security\Voter;

use App\Entity\Member;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MemberVoter extends AbstractVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Member || 'member' === $subject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'CREATE':
            case 'READ':
            case 'UPDATE':
            case 'DELETE':
                return $this->security->isGranted('ROLE_ADMIN');
        }

        return true;
    }
}