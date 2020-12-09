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

namespace PhpOffice\PhpWord\Writer\HTML\Style;

use PhpOffice\PhpWord\Shared\Converter;
use \PhpOffice\PhpWord\Style\Image as ImageStyle;
use PhpOffice\PhpWord\Writer\HTML\PageParams;

/**
 * Paragraph style HTML writer
 *
 * @since 0.10.0
 */
class Image extends AbstractStyle
{
    protected $expectedHeight = 0;
    protected $expectedWidth = 0;

    protected $align = ImageStyle::POS_LEFT;

    /**
     * Write style
     *
     * @return string
     */
    public function write()
    {
        $style = $this->getStyle();
        if (!$style instanceof \PhpOffice\PhpWord\Style\Image) {
            return '';
        }
        $css = array();

        $width = Converter::emuToPixel($style->getWidth());
        $height = Converter::emuToPixel($style->getHeight());
        //$css['position'] = $this->getValueIf($style->getNoWrapMode(), 'absolute');
        $css['width'] = $width . 'px';
        $css['height'] =  $height . 'px';

        if (!$style->getNoWrapMode()) {
            $this->expectedHeight = $height;
            $this->expectedWidth = $width;
        }

        if (!$style->getInline()) {
            $align = $this->getAlign($style);
            if ($align == ImageStyle::POS_CENTER) {
                $css['display'] = 'block';
                $css['margin-left'] = 'auto';
                $css['margin-right'] = 'auto';
            } else {
                $css['float'] = $align;
            }

            if (!$style->getNoWrapMode() && ($align == ImageStyle::POS_CENTER || $align == ImageStyle::POS_RIGHT)) {
                $this->expectedWidth = $this
                    ->getParentWriter()
                    ->getWriterPart('body')
                    ->getPageParams()
                    ->getContentWidth();
            }
        }

        $css = $this->applyBorder($style, $css);
        return $this->assembleCss($css);
    }

    protected function getAlign(ImageStyle $style)
    {
        if (!empty($style->getHPos())) {
            return $style->getHPos();
        }

        /**
         * @var PageParams $pageParams
         */
        $pageParams = $this->getParentWriter()->getWriterPart('body')->getPageParams();

        $left = Converter::emuToPixel($style->getLeft());
        $width = Converter::emuToPixel($style->getWidth());

        if ($left + $width > ($pageParams->getContentWidth() - 30)) {
            return  ImageStyle::POS_RIGHT;
        } elseif ($left < 30) {
            return  ImageStyle::POS_LEFT;
        }

        return ImageStyle::POS_CENTER;
    }

    protected function applyBorder(ImageStyle $style, $css)
    {
        if ($width = $style->getBorderWidth() > 0) {
            $color = $style->getBorderColor() ? '#' . $style->getBorderColor() : 'black';
            $width = ceil(Converter::twipToPixel($width));
            $css['border'] = $width . 'px solid ' . $color;

            if (!$style->getNoWrapMode()) {
                $this->expectedHeight += $style->getBorderWidth() * 2;
                $this->expectedWidth += $style->getBorderWidth() * 2;
            }
        }

        return $css;
    }

    public function getExpectedHeight()
    {
        return $this->expectedHeight;
    }

    public function getExpectedWidth()
    {
        return $this->expectedWidth;
    }
}
