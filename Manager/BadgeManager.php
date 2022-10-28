<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeClaim;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogBadgeAwardEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.manager.badge")
 */
class BadgeManager
{
    const BADGE_PICKER_MODE_USER      = 'user';
    const BADGE_PICKER_MODE_PLATFORM  = 'platform';
    const BADGE_PICKER_MODE_WORKSPACE = 'workspace';
    const BADGE_PICKER_DEFAULT_MODE    = self::BADGE_PICKER_MODE_PLATFORM;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager   = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param integer $id
     *
     * @return Badge
     */
    public function getById($id)
    {
        /** @var \Claroline\CoreBundle\Entity\Badge\Badge $badge */
        $badge = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Badge')->find($id);

        return $badge;
    }

    /**
     * @param Badge  $badge
     * @param User[] $users
     * @param string $comment
     * @param User|null $issuer
     *
     * @return int
     */
    public function addBadgeToUsers(Badge $badge, $users, $comment = null, $issuer = null)
    {
        $addedBadge = 0;

        foreach ($users as $user) {
            if ($this->addBadgeToUser($badge, $user, $comment, $issuer)) {
                $addedBadge++;
            }
        }

        return $addedBadge;
    }

    /**
     * @param Badge  $badge
     * @param User   $user
     * @param string $comment
     * @param User|null $issuer
     *
     * @throws \Exception
     * @return bool
     */
    public function addBadgeToUser(Badge $badge, User $user, $comment = null, $issuer = null)
    {
        $badgeAwarded = false;

        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository $badgeRepository */
        $badgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Badge');
        $userBadge       = $badgeRepository->findUserBadge($badge, $user);

        if (null === $userBadge) {
            try {
                $userBadge = new UserBadge();
                $userBadge
                    ->setBadge($badge)
                    ->setUser($user)
                    ->setComment($comment)
                    ->setIssuer($issuer);

                if ($badge->isExpiring()) {
                    $userBadge->setExpiredAt($this->generateExpireDate($badge));
                }

                $badge->addUserBadge($userBadge);

                $badgeAwarded = true;

                $this->entityManager->persist($badge);
                $this->entityManager->flush();

                $this->dispatchBadgeAwardingEvent($badge, $user, $issuer);
            } catch(\Exception $exception) {
                throw $exception;
            }
        }

        return $badgeAwarded;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\Badge $badge
     * @param \Claroline\CoreBundle\Entity\User        $receiver
     *
     * @param \Claroline\CoreBundle\Entity\User|null   $doer
     *
     * @return Controller
     */
    protected function dispatchBadgeAwardingEvent(Badge $badge, User $receiver, $doer = null)
    {
        $event = new LogBadgeAwardEvent($badge, $receiver, $doer);

        $this->dispatch($event);
    }

    /**
     * @param LogGenericEvent $event
     */
    protected function dispatch(LogGenericEvent $event)
    {
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @param Badge          $badge
     * @param \DateTime|null $currentDate
     *
     * @return \DateTime
     */
    public function generateExpireDate(Badge $badge, \DateTime $currentDate = null)
    {
        if (null === $currentDate) {
            $currentDate = new \DateTime();
        }

        $modifier = sprintf("+%d %s", $badge->getExpireDuration(), $badge->getExpirePeriodTypeLabel($badge->getExpirePeriod()));
        return $currentDate->modify($modifier);
    }

    /**
     * @param BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $newRules
     * @param BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $originalRules
     *
     * @return bool
     */
    public function isRuleChanged($newRules, $originalRules)
    {
        $isRulesChanged = false;
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSets();

        foreach ($newRules as $newRule) {
            // Check if there are new rules
            if (null === $newRule->getId()) {
                $isRulesChanged = true;
            }
            else {
                // Check if existed rules have been changed
                $changeSet = $unitOfWork->getEntityChangeSet($newRule);
                if (0 < count($changeSet)) {
                    $isRulesChanged = true;
                }
                // Remove rule from original if they were not deleted
                if ($originalRules->contains($newRule)) {
                    $originalRules->removeElement($newRule);
                }
            }
        }

        // Check if they are deleted rules (those who are not in the new but in the originals)
        if (0 < count($originalRules)) {
            $isRulesChanged = true;
        }

        return $isRulesChanged;
    }

    public function makeClaim(Badge $badge, User $user)
    {
        if ($user->hasBadge($badge)) {
            throw new \Exception('badge_already_award_message');
        } elseif ($user->hasClaimedFor($badge)) {
            throw new \Exception('badge_already_claim_message');
        }

        $badgeClaim = new BadgeClaim();
        $badgeClaim
            ->setUser($user)
            ->setBadge($badge);

        try {
            $this->entityManager->persist($badgeClaim);
            $this->entityManager->flush();
        } catch(\Exception $exception){
            throw new \Exception('badge_claim_error_message', 0, $exception);
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param int                                              $limit
     *
     * @return Badge[]
     */
    public function getWorkspaceLastAwardedBadges(Workspace $workspace, $limit = 10)
    {
        /** @var \Claroline\CoreBundle\Repository\Badge\UserBadgeRepository $userBadgeRepository */
        $userBadgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\UserBadge');
        $lastAwardedBadges   = $userBadgeRepository->findWorkspaceLastAwardedBadges($workspace, $limit);

        return $lastAwardedBadges;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param int                                              $limit
     *
     * @return Badge[]
     */
    public function getWorkspaceMostAwardedBadges(Workspace $workspace, $limit = 10)
    {
        /** @var \Claroline\CoreBundle\Repository\Badge\UserBadgeRepository $userBadgeRepository */
        $userBadgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\UserBadge');
        $lastAwardedBadges   = $userBadgeRepository->findWorkspaceMostAwardedBadges($workspace, $limit);

        return $lastAwardedBadges;
    }

    /**
     * @param array $parameters array of : locale (for ordering badge), mode, user, workspace
     *
     * @return array
     */
    public function getForBadgePicker(array $parameters)
    {
        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository $badgeRepository */
        $badgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Badge');

        /** @var QueryBuilder $badgeQueryBuilder */
        $badgeQueryBuilder = $badgeRepository->createQueryBuilder($rootAlias = 'badge');
        $badgeQueryBuilder = $badgeRepository->orderByName($badgeQueryBuilder, $rootAlias, $parameters['locale']);
        $badgeQueryBuilder = $badgeRepository->filterByBlacklist($badgeQueryBuilder, $rootAlias, $parameters['blacklist']);

        switch($parameters['mode']) {
            case self::BADGE_PICKER_MODE_USER:
                $badgeQueryBuilder = $badgeRepository->filterByUser($badgeQueryBuilder, $rootAlias, $parameters['user']);
                break;
            case self::BADGE_PICKER_MODE_PLATFORM:
                $badgeQueryBuilder = $badgeRepository->filterByWorkspace($badgeQueryBuilder, $rootAlias, null);
                break;
            case self::BADGE_PICKER_MODE_WORKSPACE:
                if (null !== $parameters['workspace']) {
                    $badgeQueryBuilder = $badgeRepository->filterByWorkspace($badgeQueryBuilder, $rootAlias, $parameters['workspace']);
                }
                break;
            default:
                throw new \InvalidArgumentException('Unknown mode for opening the badge picker.');
        }

        return $badgeQueryBuilder->getQuery()->getResult();
    }
}
