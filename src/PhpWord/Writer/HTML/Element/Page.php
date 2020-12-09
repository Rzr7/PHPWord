<?php

namespace PhpOffice\PhpWord\Writer\HTML\Element;

use PhpOffice\PhpWord\Element\Header;
use PhpOffice\PhpWord\Writer\HTML\Element\TextRun as TextRunWriter;
use PhpOffice\PhpWord\Writer\HTML\PageParams;

class Page extends Container
{

    /**
     * @var PageParams $pageParams
     */
    protected $pageParams;

    /**
     * @var int
     */
    protected $pageNumber = 0;

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * @param int $pageNumber
     */
    public function setPageNumber(int $pageNumber): void
    {
        $this->pageNumber = $pageNumber;
    }


    public function write()
    {
        $this->pageParams= $this->parentWriter->getWriterPart('body')->getPageParams();

        $content = '<div class="page" style="width: '.  $this->pageParams->getWidth() .'px;'
            . ' min-height: ' .  $this->pageParams->getHeight()  .'px;position:relative;">';
        $content .= $this->writeHeader();
        $content .= '<div class="content" style="width: '.  $this->pageParams->getContentWidth() .'px; '
            . 'padding-left: ' . $this->pageParams->getMarginLeft(). 'px;'
            . 'padding-right: ' . $this->pageParams->getMarginRight() . 'px;">';
        $writer = new Container($this->parentWriter, $this->element);
        $content .= $writer->write();
        $content .= $this->writeNotes();
        $content .= '</div>';
        $content .= $this->writeFooter();
        $content .= '</div>';

        return $content;
    }

    /**
     * @return string
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    protected function writeHeader(): string
    {
        $headersContent = $this->getFooterHeaderContent();

        return '<div class="header" style="width: '.  $this->pageParams->getContentWidth() .'px; '
            . 'padding-left: ' . $this->pageParams->getMarginLeft(). 'px; '
            . 'padding-right: ' . $this->pageParams->getMarginRight() . 'px;'
            . 'min-height: ' . $this->pageParams->getMarginTop() . 'px;'
            . 'background-color: white;">'
            . PHP_EOL
            . $headersContent
            . '</div>' . PHP_EOL;
    }

    /**
     * @param bool $isHeader
     *
     * @return string
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function getFooterHeaderContent(bool $isHeader = true): string
    {
        $phpWord = $this->parentWriter->getPhpWord();

        if ($isHeader === true) {
            $classPrefix = 'header';
            $parts = $phpWord->getSection(0)->getHeaders();
        } else {
            $classPrefix = 'footer';
            $parts = $phpWord->getSection(0)->getFooters();
        }

        $content = '';

        if (!empty($parts)) {
            foreach ($parts as $part) {
                if (($part->getType() === Header::AUTO)
                    || ((($this->pageNumber + 1) % 2 === 0) && ($part->getType() === Header::EVEN))
                    || ($this->pageNumber === 0 && ($part->getType() === Header::FIRST))
                ) {
                    $partContent = (new Container($this->parentWriter, $part))->write();
                    if (trim(strip_tags($partContent)) !== '') {
                        $content .= '<div class="' . $classPrefix . '-' . $part->getType() . '">'
                            . $partContent . '</div>';
                    }
                }
            }
        }

        return $content;
    }

    protected function writeFooter()
    {
        $footerContent = $this->getFooterHeaderContent(false);

        $footer = '';

        $footerBgClass = '';
        if (!empty($this->parentWriter->backgroundStyles)) {
            $backgroundStyles = $this->parentWriter->getBackgroundStyles('footerBackground');
            $footerBgClass = $backgroundStyles['className'];
            $footer .= $backgroundStyles['style'];
            $this->parentWriter->backgroundStyles = [];
        }

        $footer .= '<div class="footer';

        if (!empty($footerBgClass)) {
            $footer .= ' ' . $footerBgClass;
        }

        $footer .= '"';

        $footer .= ' style="width: '. $this->pageParams->getWidth() .'px; '
            . 'min-height: ' . $this->pageParams->getMarginBottom() . 'px;'
            . 'background-color: white;position:absolute;bottom:-' . $this->pageParams->getMarginBottom() . 'px;">'
            . PHP_EOL;


        $footer .= $footerContent;
        $footer .= '</div>' . PHP_EOL;

        return $footer;
    }

    /**
     * Write footnote/endnote contents as textruns
     *
     * @return string
     */
    private function writeNotes()
    {
        /** @var \PhpOffice\PhpWord\Writer\HTML $parentWriter Type hint */
        $parentWriter = $this->parentWriter;
        $phpWord = $parentWriter->getPhpWord();
        $notes = $parentWriter->getNotes();

        $content = '';

        if (!empty($notes)) {
            $content .= '<hr />' . PHP_EOL;
            foreach ($notes as $noteId => $noteMark) {
                list($noteType, $noteTypeId) = explode('-', $noteMark);
                $method = 'get' . ($noteType == 'endnote' ? 'Endnotes' : 'Footnotes');
                $collection = $phpWord->$method()->getItems();

                if (isset($collection[$noteTypeId])) {
                    $element = $collection[$noteTypeId];
                    $noteAnchor = "<a name=\"note-{$noteId}\" />";
                    $noteAnchor .= "<a href=\"#{$noteMark}\" class=\"NoteRef\"><sup>{$noteId}</sup></a>";

                    $writer = new TextRunWriter($this->parentWriter, $element);
                    $writer->setOpeningText($noteAnchor);
                    $content .= $writer->write();
                }
            }
        }

        return $content;
    }
}