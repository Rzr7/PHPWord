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

use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style;
use PhpOffice\PhpWord\Writer\HTML\Style\Cell as CellStyleWriter;
use PhpOffice\PhpWord\Writer\HTML\Style\Table as TableStyleWriter;

/**
 * Table element HTML writer
 *
 * @since 0.10.0
 */
class Table extends AbstractElement
{
    /**
     * @var \PhpOffice\PhpWord\Writer\HTML\Style\Table
     */
    private $style;


    /**
     * @var array|\PhpOffice\PhpWord\Element\Row[]
     */
    private $rows;

    /**
     * @var int
     */
    private $rowCount;


    private $maxWidth = 0;

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        if ($this->rows === null) {
            return null;
        }

        if ($this->rowCount === null) {
            $this->rowCount = \count($this->rows);
        }

        return $this->rowCount;
    }

    /**
     * Write table
     *
     * @return string
     */
    public function write(): string
    {
        if (!$this->element instanceof \PhpOffice\PhpWord\Element\Table) {
            return '';
        }
        $content = '';
        $this->rows = $this->element->getRows();
        if (!empty($this->rows)) {
            $this->style = $this->element->getStyle();
            if (\is_string($this->style)) {
                $this->style = Style::getStyle($this->style);
            }
            $rowsContent = $this->writeRows();

            $tableStart = '<table style="' . (new TableStyleWriter($this->style))->write();
            $tableWidth = array_sum($this->element->columnWidths);

            if ($tableWidth < $this->maxWidth) {
                $tableWidth = $this->maxWidth;
            }

            $tableWidth = Converter::twipToPixel($tableWidth);

            if ($tableWidth > 0) {
                $tableStart .= "width: {$tableWidth}px;";
            }

            $tableStart .= '">' . PHP_EOL;

            $content = $tableStart . $rowsContent . '</table>' . PHP_EOL;
        }

        return $content;
    }

    /**
     * @return string
     */
    private function writeRows(): string
    {
        $rowsContent = '';
        foreach ($this->rows as $i => $row) {
            $row->number = $i;
            $rowsContent .= $this->writeRow($row);
            if ($row->width > $this->maxWidth) {
                $this->maxWidth = $row->width;
            }
        }

        return $rowsContent;
    }

    /**
     * @param \PhpOffice\PhpWord\Element\Row $row
     *
     * @return string
     */
    private function writeRow(\PhpOffice\PhpWord\Element\Row $row): string
    {
        $row->width = 0;
        $rowContent = '';
        $isHeader = $row->getStyle()->isTblHeader();
        $rowContent .= '<tr>' . PHP_EOL;
        $rowCells = $row->getCells();

        foreach ($rowCells as $k => $rowCell) {
            $rowCell->number = $k;
            $rowCell->row = $row;
            $rowContent .= $this->writeCell($rowCell, $isHeader);
        }

        $rowContent .= '</tr>';

        return $rowContent;
    }

    /**
     * @param Cell $cell
     * @param bool $isHeader
     *
     * @return string
     */
    private function writeCell(Cell $cell, bool $isHeader): string
    {
        $cellContent = '';
        $cellStyle = $cell->getStyle();
        $cellStyle->mergeBorderStyles($this->style);
        $cell->row->width += $cellStyle->getWidth() ?: 0;
        $cellColSpan = $cellStyle->getGridSpan();
        $cellRowSpan = 1;
        $cellVMerge = $cellStyle->getVMerge();
        // If this is the first cell of the vertical merge, find out how man rows it spans
        if ($cellVMerge === 'restart') {
            for ($k = $cell->row->number + 1; $k < $this->getRowCount(); $k++) {
                $kRowCells = $this->rows[$k]->getCells();
                if (isset($kRowCells[$cell->number])
                    && $kRowCells[$cell->number]->getStyle()->getVMerge() === 'continue'
                ) {
                    $cellRowSpan++;
                } else {
                    break;
                }
            }
        }
        // Ignore cells that are merged vertically with previous rows
        if ($cellVMerge !== 'continue') {
            $cellTag = $isHeader ? 'th' : 'td';
            $cellColSpanAttr = (is_numeric($cellColSpan) && ($cellColSpan > 1) ? " colspan=\"{$cellColSpan}\""
                : '');
            $cellRowSpanAttr = ($cellRowSpan > 1 ? " rowspan=\"{$cellRowSpan}\"" : '');

            $styleWriter = new CellStyleWriter($cellStyle);
            $cellContent .= "<{$cellTag } style=\"" . $styleWriter->write()
                . "overflow: hidden;\" {$cellColSpanAttr}{$cellRowSpanAttr}>" . PHP_EOL;
            $writer = new Container($this->parentWriter, $cell);
            $cellContent .= $writer->write();
            if ($cellRowSpan > 1) {
                // There shouldn't be any content in the subsequent merged cells, but lets check anyway
                for ($k = $cell->row->number + 1; $k < $this->getRowCount(); $k++) {
                    $kRowCells = $this->rows[$k]->getCells();
                    if (isset($kRowCells[$cell->number])
                        && $kRowCells[$cell->number]->getStyle()->getVMerge() === 'continue'
                    ) {
                        $writer = new Container($this->parentWriter, $kRowCells[$cell->number]);
                        $cellContent .= $writer->write();
                    } else {
                        break;
                    }
                }
            }

            $cellContent .= "</{$cellTag}>" . PHP_EOL;
        }

        return $cellContent;
    }
}
