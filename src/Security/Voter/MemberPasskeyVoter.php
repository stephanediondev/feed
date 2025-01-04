<?php

namespace App\Security\Voter;

use App\Entity\MemberPasskey;
use App\Entity\Member;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MemberPasskeyVoter extends AbstractVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof MemberPasskey || 'member_passkey' === $subject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (false === $user instanceof Member) {
            return false;
        }

        switch ($attribute) {
            case 'DELETE':
                if ($user->getId() !== $subject->getMember()->getId()) {
                    return false;
                }
        }

        return true;
    }
}
