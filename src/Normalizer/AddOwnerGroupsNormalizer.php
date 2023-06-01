<?php

namespace App\Normalizer;

use App\Entity\DragonTreasure;
use ArrayObject;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
class AddOwnerGroupsNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    public function __construct(
        private NormalizerInterface $decorated,
        private Security $security
    )
    {
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array|ArrayObject|bool|float|int|null|string
    {
        // si l'objet a normalizer est une instance de DragonTreasure
        // et que l'utilisateur authentifié est le propriétaire du DragonTreasure
        // ajoute le groupe "owner:read" avant la normalization (object to array)
        if ($object instanceof DragonTreasure && $this->security->getUser() === $object->getOwner()) {
            $context['groups'][] = 'owner:read';
        }

        $normalized = $this->decorated->normalize($object, $format, $context);

        // si l'objet a normalizer est une instance de DragonTreasure
        // et que l'utilisateur authentifié est le propriétaire du DragonTreasure
        // ajoute une clé "isMine" au tableau obtenu apres la normalization
        if ($object instanceof DragonTreasure && $this->security->getUser() === $object->getOwner()) {
            $normalized['isMine'] = true;
        }

        return $normalized;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}