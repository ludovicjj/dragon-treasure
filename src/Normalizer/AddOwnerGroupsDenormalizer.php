<?php

namespace App\Normalizer;

use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AsDecorator('api_platform.serializer.normalizer.item')]
class AddOwnerGroupsDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private DenormalizerInterface $decorated,
        private Security $security
    )
    {
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (
            $type === DragonTreasure::class
            && $context['operation']->getMethod() === 'PATCH'
            && $this->security->getUser() === $context['object_to_populate']->getOwner()
        ) {
            $context['groups'][] = 'owner:write';
        }

        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }
}