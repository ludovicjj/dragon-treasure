<?php

namespace App\Validator;

use App\Entity\DragonTreasure;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TreasureAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TreasureAllowedOwnerChange) {
            throw new UnexpectedTypeException($constraint, TreasureAllowedOwnerChange::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedValueException($value, Collection::class);
        }

        // unitOfWork permet de suivre l'évolution d'une entité
        $unitOfWork = $this->entityManager->getUnitOfWork();

        // Parcours chaque dragonTreasure dans la collection
        foreach ($value as $dragonTreasure) {
            assert($dragonTreasure instanceof DragonTreasure);

            // Récupère le dragon Treasure avant modification
            $originalData = $unitOfWork->getOriginalEntityData($dragonTreasure);
            $originalOwnerId = $originalData['owner_id'];
            $newOwnerId = $dragonTreasure->getOwner()->getId();

            // Vérifie si le treasure avait un propriétaire
            // si $originalOwnerId est null, il s'agit d'une création, l'utilisateur n'essaye pas de voler le trésor d'un autre utilisateur
            // si $originalOwnerId est différent de $newOwnerId, l'utilisateur essaye de voler le trésor d'un autre utilisateur
            if (!$originalOwnerId || $originalOwnerId === $newOwnerId) {
                return;
            }

            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
