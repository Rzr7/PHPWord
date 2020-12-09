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

namespace PhpOffice\PhpWord\Writer\HTML\Element;

use PhpOffice\PhpWord\Element\Image as ImageElement;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Writer\HTML\Style\Image as ImageStyleWriter;

/**
 * Image element HTML writer
 *
 * @since 0.10.0
 */
class Image extends Text
{

    /**
     * Write image
     *
     * @return string
     */
    public function write()
    {
        if (!$this->element instanceof ImageElement) {
            return '';
        }

        $content = '';
        $imageData = $this->getImageData();
        if ($imageData !== null) {
            $imageStyle = $this->element->getStyle();
            $styleWriter = new ImageStyleWriter($imageStyle);
            $styleWriter->setParentWriter($this->parentWriter);
            $imageData = 'data:' . $this->element->getImageType() . ';base64,' . $imageData;
            if (!$imageStyle->getNoWrapMode() || !$imageStyle->getBehindDoc()) {
                $style = $styleWriter->write();
                $this->expectedHeight = $styleWriter->getExpectedHeight();
                $this->expectedWidth = $styleWriter->getExpectedWidth();

                $content .= $this->writeOpening();
                $content .= "<img data-expected-height=\"{$this->expectedHeight}px\" "
                    ."data-expected-width=\"{$this->expectedWidth}px\" border=\"0\" style=\"{$style}\" src=\"{$imageData}\"/>";
                $content .= $this->writeClosing();
            } else {
                $imageData = str_replace(array("\r","\n"), '', $imageData);

                $left = Converter::emuToPixel($imageStyle->getLeft())?:0;
                if (!$left && $imageStyle->getPosHorizontal() === 'right') {
                    $left = 'right';
                } else {
                    $left = "{$left}px";
                }

                $top = Converter::emuToPixel($imageStyle->getTop()) ?: 0;
                if (!$top && $imageStyle->getPosHorizontal() === 'bottom') {
                    $top = 'bottom';
                } else {
                    $top= "{$top}px";
                }

                $width = Converter::emuToPixel($imageStyle->getWidth());
                $height = Converter::emuToPixel($imageStyle->getHeight());
                $backgroundStyle = [
                    'background' => "url($imageData)",
                    'background-repeat' => 'no-repeat',
                    'background-size'  => "{$width}px {$height}px",
                    'background-position' => "{$left} {$top}"
                ];

                $this->parentWriter->backgroundStyles[] = $backgroundStyle;
            }
        }

        return $content;
    }

    private function getImageData()
    {
        return $this->element->getImageStringData(true);
    }
}
