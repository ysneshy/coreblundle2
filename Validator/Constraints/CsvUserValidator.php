<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @DI\Validator("csv_user_validator")
 */
class CsvUserValidator extends ConstraintValidator
{
    private $validator;
    private $translator;
    private $om;

    /**
     * @DI\InjectParams({
     *     "validator"             = @DI\Inject("validator"),
     *     "trans"                 = @DI\Inject("translator"),
     *     "authenticationManager" = @DI\Inject("claroline.common.authentication_manager"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        AuthenticationManager $authenticationManager,
        ObjectManager $om
    )
    {
        $this->validator = $validator;
        $this->translator = $translator;
        $this->authenticationManager = $authenticationManager;
        $this->om = $om;
    }

    public function validate($value, Constraint $constraint)
    {
        $lines = str_getcsv(file_get_contents($value), PHP_EOL);
        $authDrivers = $this->authenticationManager->getDrivers();

        foreach ($lines as $line) {
            $linesTab = explode(';', $line);
            $nbElements = count($linesTab);

            if (trim($line) != '') {
                if ($nbElements < 5) {
                    $this->context->addViolation($constraint->message);
                    return;
                }
            }
        }

        $usernames = array();
        $mails = array();

        foreach ($lines as $i => $line) {
            if (trim($line) != '') {
                $user = explode(';', $line);
                $firstName = $user[0];
                $lastName = $user[1];
                $username = $user[2];
                $pwd = $user[3];
                $email = $user[4];

                if (isset($user[5])) {
                    $code = trim($user[5]) === '' ? null: $user[5];
                } else {
                    $code = null;
                }

                if (isset($user[6])) {
                    $phone = trim($user[6]) === '' ? null: $user[6];
                } else {
                    $phone = null;
                }

                if (isset($user[7])) {
                    $authentication = trim($user[7]) === '' ? null: $user[7];
                } else {
                    $authentication = null;
                }

                if (isset($user[8])) {
                    $modelName = trim($user[7]) === '' ? null: $user[7];
                } else {
                    $modelName = null;
                }

                (!array_key_exists($email, $mails)) ?
                    $mails[$email] = array($i + 1):
                    $mails[$email][] = $i + 1;
                (!array_key_exists($username, $usernames)) ?
                    $usernames[$username] = array($i + 1):
                    $usernames[$username][] = $i + 1;

                $newUser = new User();
                $newUser->setFirstName($firstName);
                $newUser->setLastName($lastName);
                $newUser->setUsername($username);
                $newUser->setPlainPassword($pwd);
                $newUser->setMail($email);
                $newUser->setAdministrativeCode($code);
                $newUser->setPhone($phone);
                $errors = $this->validator->validate($newUser, array('registration', 'Default'));

                if ($authentication) {
                    if (!in_array($authentication, $authDrivers)) {
                        $msg = $this->translator->trans(
                                'authentication_invalid',
                                array('%authentication%' => $authentication, '%line%' => $i + 1),
                                'platform'
                            ) . ' ';

                        $this->context->addViolation($msg);
                    }
                }

                foreach ($errors as $error) {
                    $this->context->addViolation(
                        $this->translator->trans('line_number', array('%line%' => $i + 1), 'platform') . ' ' .
                        $error->getInvalidValue() . ' : ' . $error->getMessage()
                    );
                }
            }
        }

        foreach ($usernames as $username => $lines) {
            if (count($lines) > 1) {
                $msg = $this->translator->trans(
                    'username_found_at',
                    array('%username%' => $username, '%lines%' => $this->getLines($lines)),
                    'platform'
                ) . ' ';
            }
        }

        if ($modelName) {
            $model = $this->om->getRepository('ClarolineCoreBundle:Model\WorkspaceModel')->findOneByName($modelName);

            if (!$model) {
                $msg = $this->translator->trans(
                        'model_invalid',
                        array('%model%' => $modelName, '%line%' => $i + 1),
                        'platform'
                    ) . ' ';

                $this->context->addViolation($msg);
            }
        }

        foreach ($errors as $error) {
            $this->context->addViolation(
                $this->translator->trans('line_number', array('%line%' => $i + 1), 'platform') . ' ' .
                $error->getInvalidValue() . ' : ' . $error->getMessage()
            );
        }

        foreach ($usernames as $username => $lines) {
            if (count($lines) > 1) {
                $msg = $this->translator->trans(
                    'username_found_at',
                    array('%username%' => $username, '%lines%' => $this->getLines($lines)),
                    'platform'
                ) . ' ';

                $this->context->addViolation($msg);
            }

            foreach ($mails as $mail => $lines) {
                if (count($lines) > 1) {
                    $msg = $this->translator->trans(
                        'email_found_at',
                        array('%email%' => $mail, '%lines%' => $this->getLines($lines)),
                        'platform'
                    ) . ' ';
                    $this->context->addViolation($msg);
                }
            }
        }
    }

    private function getLines($lines)
    {
        $countLines = count($lines);
        $l = '';

        foreach ($lines as $i => $line) {
            $l .= $line;

            if ($i < $countLines - 1) {
                $l .= ', ';
            }
        }

        return $l;
    }
}
