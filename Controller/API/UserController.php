<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

use FOS\RestBundle\Util\Codes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class UserController extends Controller
{
    /**
     * @Route("/connected_user")
     */
    public function connectedUserAction()
    {
        /** @var \Symfony\Component\Security\Core\SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');
        $securityToken   = $securityContext->getToken();

        if (null !== $securityToken) {
            /** @var \Claroline\CoreBundle\Entity\User $user */
            $user = $securityToken->getUser();

            if($user) {
                return new JsonResponse(array(
                        'id'       => $user->getId(),
                        'username' => $user->getUsername(),
                        'user_id'  => $user->getUsername() . $user->getId()
                    ));
            }
        }

        return new JsonResponse(array(
            'message' => 'User is not identified'
        ), Codes::HTTP_NOT_FOUND);
    }
}
 