<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\DataTransformer;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Manager\BadgeManager;
use Doctrine\Common\Collections\Collection;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @DI\Service("claroline.transformer.badge_picker")
 */
class BadgePickerTransformer implements DataTransformerInterface
{
    /**
     * @var \Claroline\CoreBundle\Manager\BadgeManager
     */
    private $badgeManager;

    /**
     * @DI\InjectParams({
     *     "badgeManager" = @DI\Inject("claroline.manager.badge")
     * })
     */
    public function __construct(BadgeManager $badgeManager)
    {
        $this->badgeManager = $badgeManager;
    }

    /**
     * @param Badge[]|Badge $value
     *
     * @return int|string
     */
    public function transform($value)
    {
        if (is_array($value) || $value instanceof Collection) {
            $transformedData = array();

            foreach ($value as $entity) {
                $transformedData[] = array(
                    'id'          => $entity->getId(),
                    'text'        => $entity->getName(),
                    'icon'        => $entity->getWebPath(),
                    'description' => $entity->getDescription()
                );
            }

            return $transformedData;
        }

        if ($value instanceof Badge) {
            return array(
                'id'          => $value->getId(),
                'text'        => $value->getName(),
                'icon'        => $value->getWebPath(),
                'description' => $value->getDescription()
            );
        }

        return null;
    }

    /**
     * @param integer $badgeId
     *
     * @return Badge|null
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform($badgeId)
    {
        if (!$badgeId) {
            return null;
        }

        $badge = $this->badgeManager->getById($badgeId);

        if (null === $badge) {
            throw new TransformationFailedException();
        }

        return $badge;
    }
}
