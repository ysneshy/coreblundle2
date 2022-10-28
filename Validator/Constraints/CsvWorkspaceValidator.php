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
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Validator("csv_workspace_validator")
 */
class CsvWorkspaceValidator extends ConstraintValidator
{
    private $translator;
    private $om;
    private $sc;

    /**
     * @DI\InjectParams({
     *     "trans" = @DI\Inject("translator"),
     *     "om"    = @DI\Inject("claroline.persistence.object_manager"),
     *     "sc"    = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        TranslatorInterface $translator,
        ObjectManager $om,
        SecurityContextInterface $sc
    )
    {
        $this->translator = $translator;
        $this->om = $om;
        $this->sc = $sc;
    }

    public function validate($value, Constraint $constraint)
    {
        $lines = str_getcsv(file_get_contents($value), PHP_EOL);
        $codes = [];

        foreach ($lines as $line) {
            $linesTab = explode(';', $line);
            $nbElements = count($linesTab);

            if (trim($line) != '') {
                if ($nbElements < 6) {
                    $msg = $this->translator->trans('6_parameters_min_msg');
                    $this->context->addViolation($msg);
                    return;
                }

                if (php_sapi_name() === 'cli' && $nbElements < 7) {
                    $msg = $this->translator->trans('7_parameters_min_msg');
                    $this->context->addViolation($msg);
                    return;
                }
            }
        }

        foreach ($lines as $i => $line) {
            if (trim($line) != '') {
                $workspace = explode(';', $line);
                $code = $workspace[1];

                if (isset($workspace[6])) {
                    $username = trim($workspace[6]) === '' ? null: $workspace[6];
                } else {
                    $username = null;
                }

                if (isset($workspace[7])) {
                    $modelName = trim($workspace[7]) === '' ? null: $workspace[7];
                } else {
                    $modelName = null;
                }

                //find codes duplicatas
                (!array_key_exists($code, $codes)) ?
                    $codes[$code] = array($i + 1):
                    $codes[$code][] = $i + 1;

                if ($this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code)) {
                    $msg = $this->translator->trans(
                            'workspace_code_invalid',
                            array('%code%' => $code, '%line%' => $i + 1),
                            'platform'
                        ) . ' ';

                    $this->context->addViolation($msg);
                }

                if ($modelName) {
                    if (!$this->om->getRepository('ClarolineCoreBundle:Model\WorkspaceModel')->findOneByName($modelName)) {
                        $msg = $this->translator->trans(
                                'workspace_model_invalid',
                                array('%model%' => $modelName, '%line%' => $i + 1),
                                'platform'
                            ) . ' ';

                        $this->context->addViolation($msg);
                    }
                }

                if ($username) {
                    if (!$this->om->getRepository('ClarolineCoreBundle:User')->findOneByUsername($username)) {
                        $msg = $this->translator->trans(
                                'workspace_user_invalid',
                                array('%username%' => $username, '%line%' => $i + 1),
                                'platform'
                            ) . ' ';

                        $this->context->addViolation($msg);
                    }
                }
            }
        }

        foreach ($codes as $code => $lines) {
            if (count($lines) > 1) {
                $msg = $this->translator->trans(
                'code_found_at',
                array('%code%' => $code, '%lines%' => $this->getLines($lines)),
                'platform'
                ) . ' ';
                $this->context->addViolation($msg);
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
