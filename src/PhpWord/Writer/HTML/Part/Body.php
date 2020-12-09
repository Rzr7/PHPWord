<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2018 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\HTML\Part;

use PhpOffice\PhpWord\Element\PageBreak;
use PhpOffice\PhpWord\Writer\HTML\Element\Page;
use PhpOffice\PhpWord\Writer\HTML\PageParams;

/**
 * RTF body part writer
 *
 * @since 0.11.0
 */
class Body extends AbstractPart
{

    /**
     * @var PageParams $pageParams
     */
    protected $pageParams;

    /**
     * Write part
     *
     * @return string
     */
    public function write()
    {
        $phpWord = $this->getParentWriter()->getPhpWord();

        $content = '';
        $sections = $phpWord->getSections();
        $section = reset($sections);

        $this->pageParams = new PageParams($section->getStyle());

        $content .= '<body>' . PHP_EOL;
        $content .= '<div class="wrapper" style="width: '. $this->pageParams->getWidth() .'px; '
                 . ' margin-left: auto; margin-right: auto;">' . PHP_EOL;

        foreach ($sections as $section) {
            $elementsPages = [];
            $page = 0;
            foreach ($section->getElements() as $element) {
                $elementsPages[$page][] = $element;
                if ($element instanceof PageBreak) {
                    $page++;
                }
            }

            foreach ($elementsPages as $pageNumber => $elements) {
                $pageSection = clone $section;
                $pageSection->setElements($elements);
                $writer = new Page($this->getParentWriter(), $pageSection);
                $writer->setPageNumber($pageNumber);
                $content .= $writer->write();
            }

        }

        $content .= '</div></body>' . PHP_EOL;

        return $content;
    }

    /**
     * @return PageParams
     */
    public function getPageParams(): PageParams
    {
        return $this->pageParams;
    }
}
