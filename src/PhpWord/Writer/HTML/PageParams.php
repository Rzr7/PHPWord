<?php

namespace PhpOffice\PhpWord\Writer\HTML;


use PhpOffice\PhpWord\Style\Section;
use PhpOffice\PhpWord\Shared\Converter;

class PageParams
{

    private $width;
    private $height;

    private $marginLeft;
    private $marginRight;
    private $marginTop;
    private $marginBottom;

    public function __construct(Section $style)
    {
        $this->width = Converter::twipToPixel($style->getPageSizeW());
        $this->height = Converter::twipToPixel($style->getPageSizeH());
        $this->marginLeft = Converter::twipToPixel($style->getMarginLeft());
        $this->marginRight = Converter::twipToPixel($style->getMarginRight());
        $this->marginTop = Converter::twipToPixel($style->getMarginTop());
        $this->marginBottom = Converter::twipToPixel($style->getMarginBottom());
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMarginLeft()
    {
        return $this->marginLeft;
    }

    public function getMarginRight()
    {
        return $this->marginRight;
    }

    public function getMarginTop()
    {
        return $this->marginTop;
    }

    public function getMarginBottom()
    {
        return $this->marginBottom;
    }

    public function getContentWidth()
    {
        return $this->getWidth() - $this->getMarginLeft() - $this->getMarginRight();
    }

    public function getContentHeight()
    {
        return $this->getHeight() - $this->getMarginTop() - $this->getMarginBottom();
    }


}