<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FacetRepository extends EntityRepository
{
    public function findVisibleFacets(TokenInterface $token)
    {
        $roleNames = array();

        foreach ($token->getRoles() as $role) {
            $roleNames[] = $role->getRole();
        }

        //the mighty admin can do anything in our world
        if (in_array('ROLE_ADMIN', $roleNames)) {
            return $this->findAll();
        }

        $dql = "
            SELECT facet FROM Claroline\CoreBundle\Entity\Facet\Facet facet
            JOIN facet.roles role
            WHERE role.name IN (:rolenames)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('rolenames', $roleNames);

        return $query->getResult();
    }
} 