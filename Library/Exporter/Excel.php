<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Exporter;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.exporter.xls")
 */
class Excel implements ExporterInterface
{
    /**
     * http://www.the-art-of-web.com/php/dataexport/
     */
    public function export(array $titles, array $data)
    {
        //titles row
        $excel = implode("\t", $titles)  . "\r\n";

        foreach ($data as $row) {
            array_walk($row, function(&$str) {
                 $str = preg_replace("/\t/", "\\t", $str);
                 $str = preg_replace("/\r?\n/", "\\n", $str);
                 if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
            });


            $excel .= implode("\t", $row) . "\r\n";
        }

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "file.xls";
        file_put_contents($tmpFile, $excel);

        return $tmpFile;
    }
}
