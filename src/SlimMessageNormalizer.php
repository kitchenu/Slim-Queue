<?php

namespace SlimQueue;

use Assert\Assertion;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SlimMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'name' => $object->getName(),
            'class' => $object->getClass(),
            'arguments' => $object->all(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        Assertion::notEmptyKey($data, 'name');
        Assertion::notEmptyKey($data, 'class');
        Assertion::keyExists($data, 'arguments');
        Assertion::isArray($data['arguments']);

        return new SlimMessage($data['name'], $data['class'], $data['arguments']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === SlimMessage::class;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SlimMessage;
    }
}
