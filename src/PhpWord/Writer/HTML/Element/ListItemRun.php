<?php

namespace PhpOffice\PhpWord\Writer\HTML\Element;

use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style;

class ListItemRun extends Container
{
    /**
     * Write container
     *
     * @return string
     */
    public function write()
    {
        /** @var \PhpOffice\PhpWord\Element\ListItemRun $container */
        $container = $this->element;
        if (!$container instanceof AbstractContainer) {
            return '';
        }
        $content = '';
        $elements = $container->getElements();
        foreach ($elements as $element) {
            $containerClass = substr(get_class($element), strrpos(get_class($element), '\\') + 1);
            $withoutP = in_array($containerClass, ['Text', 'TextRun', 'Link']) ? true : false;
            $elementClass = get_class($element);
            $writerClass = str_replace('PhpOffice\\PhpWord\\Element', $this->namespace, $elementClass);
            if (class_exists($writerClass)) {
                /** @var \PhpOffice\PhpWord\Writer\HTML\Element\AbstractElement $writer Type hint */
                $writer = new $writerClass($this->parentWriter, $element, $withoutP);
                $content .= $writer->write();
            }
        }

        /**
         * @var $style \PhpOffice\PhpWord\Style\ListItem
         */
        $style = $container->getStyle();

        $numStyleName = $style->getNumStyle();
        $styles = Style::getStyles();
        /** @var Style\NumberingLevel $totalStyle */
        $totalStyle = &$styles[$numStyleName]->getLevels()[(int)$container->getDepth()];

        if ($totalStyle === null) {
            $totalStyle = array_values($styles[$numStyleName]->getLevels())[0];
        }

        $text = $totalStyle->getText();
        $alphabet = range('A', 'Z');

        $currentDepth = (int)$container->getDepth();
        if ($currentDepth > $this->lastDepth) {
            $totalStyle->setCurrentValue(null);
        }

        for ($i = $container->getDepth(); $i >= 0; $i--) {
            if ($i == $container->getDepth()) {
                $totalStyle->setCurrentValue($totalStyle->getCurrentValue() + 1);
            }
            $tempStyle = &$styles[$numStyleName]->getLevels()[$i];

            if ($tempStyle === null) {
                $tempStyle = array_values($styles[$numStyleName]->getLevels())[0];
            }

            $modifier = 1;
            if ($tempStyle->getCurrentValue() != ($tempStyle->getStart() + 1)) {
                $modifier = 2;
            }
            $currentValue = $tempStyle->getCurrentValue() - $modifier;

            switch ($tempStyle->getFormat()) {
                case 'decimal':
                    $text = str_replace('%' . ($i + 1), $currentValue, $text);
                    break;
                case 'lowerLetter':
                    $text = str_replace('%' . ($i + 1), strtolower($alphabet[$currentValue-1]), $text);
                    break;
                case 'upperLetter':
                    $text = str_replace('%' . ($i + 1), $alphabet[$currentValue-1], $text);
                    break;
                case 'upperRoman':
                    $text = str_replace('%' . ($i + 1), $this->numberToRomanRepresentation($currentValue), $text);
                    break;
                case 'lowerRoman':
                    $text = str_replace('%' . ($i + 1), strtolower($this->numberToRomanRepresentation($currentValue)), $text);
                    break;
            }
        }

        if ($text === '' || strpos($text, '%') !== false) {
            $text = '•';
        } elseif ($text === '') {
            $text = '▪';
        } elseif ($text === 'o') {
            $text = '○';
        }

        $text .= ' ';
        $hanging = Converter::twipToPixel($totalStyle->getHanging());
        $padding = Converter::twipToPixel($totalStyle->getLeft()) - $hanging;

        //return '<p style="text-align: ' . $totalStyle->getAlign() . '; padding-left: ' . Converter::twipToPixel($totalStyle->getHanging() * $level) . 'px;"><span>' . $text . '</span>' . $content . '</p>';
        //return "<div style=\"text-align: $parentAlignment; padding-left:{$padding}px;margin-top: 0;float: left;width: 100%;\"><div style='float:left;width: 1%;white-space:nowrap;'>$text</div><div style='float: left;width: 95%;'>$content</div></div>";
        $parentParagraphStyle = $this->element->getParagraphStyle();
        $parentAlignment = $parentParagraphStyle->getAlignment() ?: 'left';
        return "<table style='text-align: $parentAlignment;padding-left:{$padding}px;width: 100%;margin-top: 0;vertical-align: top;border: none;'><tr><td style='vertical-align: top;border: none;width: 1%;white-space:nowrap;'>$text</td><td style='vertical-align: top;border: none;'>$content</td></tr></table>";
    }

    /**
     * @param int $number
     *
     * @return string
     */
    private function numberToRomanRepresentation($number)
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }
}