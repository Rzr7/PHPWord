<?php

namespace PhpOffice\PhpWord\Writer\HTML\Style;


use PhpOffice\PhpWord\Shared\Converter;

class Table extends AbstractStyle
{
    public function write()
    {
        $style = $this->getStyle();
        if (!$style instanceof \PhpOffice\PhpWord\Style\Table) {
            return '';
        }

        $borderTopColor = $style->getBorderTopColor() == 'auto' ? null : $style->getBorderTopColor();
        $borderBottomColor = $style->getBorderBottomColor() == 'auto' ? null : $style->getBorderBottomColor();
        $borderLeftColor = $style->getBorderLeftColor() == 'auto' ? null : $style->getBorderLeftColor();
        $borderRightColor = $style->getBorderRightColor() == 'auto' ? null : $style->getBorderRightColor();

        $backgroundColor = $style->getBgColor() == 'auto' ? null : $style->getBgColor();

        $css = [
            'border-collapse' => 'collapse',
            'background-color' => $this->getValueIf(!is_null($backgroundColor), '#' . $backgroundColor),
            'border-top-color' => $this->getValueIf(!is_null($borderTopColor) , '#' . $borderTopColor),
            'border-top-width' => Converter::twipToPixel($style->getBorderTopSize()) . 'px',
            'border-top-style' =>  $this->getValueIf(!is_null($style->getBorderTopStyle()), $style->getBorderTopStyle()),
            'border-bottom-color' => $this->getValueIf(!is_null($borderBottomColor), '#' . $borderBottomColor),
            'border-bottom-width' => Converter::twipToPixel($style->getBorderBottomSize()) . 'px',
            'border-bottom-style' =>  $this->getValueIf(!is_null($style->getBorderBottomStyle()), $style->getBorderBottomStyle()),
            'border-left-color' => $this->getValueIf(!is_null($borderLeftColor), '#' . $borderLeftColor),
            'border-left-width' => Converter::twipToPixel($style->getBorderLeftSize()) . 'px',
            'border-left-style' =>  $this->getValueIf(!is_null($style->getBorderLeftStyle()), $style->getBorderLeftStyle()),
            'border-right-color' => $this->getValueIf(!is_null($borderRightColor), '#' . $borderRightColor),
            'border-right-width' => Converter::twipToPixel($style->getBorderRightSize()) . 'px',
            'border-right-style' =>  $this->getValueIf(!is_null($style->getBorderRightStyle()), $style->getBorderRightStyle()),
        ];

        return $this->assembleCss($css);
    }
}