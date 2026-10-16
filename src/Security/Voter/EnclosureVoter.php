<?php

namespace App\Security\Voter;

use App\Entity\Enclosure;
use App\Entity\Member;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class EnclosureVoter extends AbstractVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Enclosure || 'enclosure' === $subject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (false === $user instanceof Member) {
            return false;
        }

        switch ($attribute) {
            case 'DELETE':
                return $this->security->isGranted('ROLE_ADMIN');
        }

        return true;
    }
}
