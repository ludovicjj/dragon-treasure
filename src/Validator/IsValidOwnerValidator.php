<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsValidOwnerValidator extends ConstraintValidator
{
    public function __construct(private Security $security)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsValidOwner) {
            throw new UnexpectedTypeException($constraint, IsValidOwner::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof User) {
            throw new UnexpectedValueException($value, User::class);
        }

        // get logged user
        $user = $this->security->getUser();

        if (!$user) {
            throw new \LogicException('IsOwnerValidator should only be used when a user is logged in.');
        }

        // admin can edit any treasure
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($user !== $value) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
