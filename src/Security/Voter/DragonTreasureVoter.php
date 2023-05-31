<?php

namespace App\Security\Voter;

use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class DragonTreasureVoter extends Voter
{
    public function __construct(private Security $security)
    {
    }

    public const EDIT = 'EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT])
            && $subject instanceof DragonTreasure;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        assert($subject instanceof DragonTreasure);

        // ... (check conditions and return true to grant permission) ...
        return match($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canEdit(DragonTreasure $subject, UserInterface $user): bool
    {
        if (!$this->security->isGranted('ROLE_TREASURE_EDIT')) {
            return false;
        }

        if ($subject->getOwner() === $user) {
            return true;
        }

        return false;
    }
}
