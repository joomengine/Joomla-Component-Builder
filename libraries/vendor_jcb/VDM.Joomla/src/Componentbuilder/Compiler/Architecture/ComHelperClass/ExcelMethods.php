<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    15th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\ComHelperClass;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\ComHelperClass\ExcelMethodsInterface;


/**
 * Component Helper Class Excel Methods Class.
 * 
 * Generates the component helper `xls()` export method, the
 * `getFileHeaders()` import-header reader, and the phpspreadsheet
 * composer loader used by the import/export feature. Joomla-target
 * variants supply the target-specific user and import-session lookups.
 * 
 * @since  6.1.7
 */
class ExcelMethods implements ExcelMethodsInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * Constructor.
	 *
	 * @param Config       $config       The Config Class.
	 * @param ContentOne   $contentone   The ContentOne Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config, ContentOne $contentone)
	{
		$this->config = $config;
		$this->contentone = $contentone;
	}

	/**
	 * Get the helper spreadsheet method code.
	 *
	 * When the import/export option is inactive an empty string is
	 * returned.
	 *
	 * @return  string  The generated helper methods, or an empty string.
	 *
	 * @since   6.1.7
	 */
	public function get(): string
	{
		if ($this->config->get('add_eximport', false))
		{
			// we use the company name set in the GUI
			$company_name = $this->contentone->get('COMPANYNAME');
			// start building the xml function
			$exel   = [];
			$exel[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
			$exel[] = Indent::_(1) . "* Prepares the xml document";
			$exel[] = Indent::_(1) . "*/";
			$exel[] = Indent::_(1)
				. "public static function xls(\$rows, \$fileName = null, \$title = null, \$subjectTab = null, \$creator = '$company_name', \$description = null, \$category = null,\$keywords = null, \$modified = null)";
			$exel[] = Indent::_(1) . "{";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set the user";
			foreach ($this->getUserLines() as $line)
			{
				$exel[] = $line;
			}
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set fileName if not set";
			$exel[] = Indent::_(2) . "if (!\$fileName)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3)
				. "\$fileName = 'exported_' . Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getDate()->format('jS_F_Y');";
			$exel[] = Indent::_(2) . "}";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set modified if not set";
			$exel[] = Indent::_(2) . "if (!\$modified)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3) . "\$modified = \$user->name;";
			$exel[] = Indent::_(2) . "}";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set title if not set";
			$exel[] = Indent::_(2) . "if (!\$title)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3) . "\$title = 'Book1';";
			$exel[] = Indent::_(2) . "}";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set tab name if not set";
			$exel[] = Indent::_(2) . "if (!\$subjectTab)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3) . "\$subjectTab = 'Sheet1';";
			$exel[] = Indent::_(2) . "}";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " make sure we have the composer classes loaded";
			$exel[] = Indent::_(2)
				. "self::composerAutoload('phpspreadsheet');";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Create new Spreadsheet object";
			$exel[] = Indent::_(2) . "\$spreadsheet = new Spreadsheet();";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Set document properties";
			$exel[] = Indent::_(2) . "\$spreadsheet->getProperties()";
			$exel[] = Indent::_(3) . "->setCreator(\$creator)";
			$exel[] = Indent::_(3) . "->setCompany('$company_name')";
			$exel[] = Indent::_(3) . "->setLastModifiedBy(\$modified)";
			$exel[] = Indent::_(3) . "->setTitle(\$title)";
			$exel[] = Indent::_(3) . "->setSubject(\$subjectTab);";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " The file type";
			$exel[] = Indent::_(2) . "\$file_type = 'Xls';";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set description";
			$exel[] = Indent::_(2) . "if (\$description)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3)
				. "\$spreadsheet->getProperties()->setDescription(\$description);";
			$exel[] = Indent::_(2) . "}";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set keywords";
			$exel[] = Indent::_(2) . "if (\$keywords)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3)
				. "\$spreadsheet->getProperties()->setKeywords(\$keywords);";
			$exel[] = Indent::_(2) . "}";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set category";
			$exel[] = Indent::_(2) . "if (\$category)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3)
				. "\$spreadsheet->getProperties()->setCategory(\$category);";
			$exel[] = Indent::_(2) . "}";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Some styles";
			$exel[] = Indent::_(2) . "\$headerStyles = array(";
			$exel[] = Indent::_(3) . "'font'  => array(";
			$exel[] = Indent::_(4) . "'bold'  => true,";
			$exel[] = Indent::_(4) . "'color' => array('rgb' => '1171A3'),";
			$exel[] = Indent::_(4) . "'size'  => 12,";
			$exel[] = Indent::_(4) . "'name'  => 'Verdana'";
			$exel[] = Indent::_(2) . "));";
			$exel[] = Indent::_(2) . "\$sideStyles = array(";
			$exel[] = Indent::_(3) . "'font'  => array(";
			$exel[] = Indent::_(4) . "'bold'  => true,";
			$exel[] = Indent::_(4) . "'color' => array('rgb' => '444444'),";
			$exel[] = Indent::_(4) . "'size'  => 11,";
			$exel[] = Indent::_(4) . "'name'  => 'Verdana'";
			$exel[] = Indent::_(2) . "));";
			$exel[] = Indent::_(2) . "\$normalStyles = array(";
			$exel[] = Indent::_(3) . "'font'  => array(";
			$exel[] = Indent::_(4) . "'color' => array('rgb' => '444444'),";
			$exel[] = Indent::_(4) . "'size'  => 11,";
			$exel[] = Indent::_(4) . "'name'  => 'Verdana'";
			$exel[] = Indent::_(2) . "));";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Add some data";
			$exel[] = Indent::_(2)
				. "if ((\$size = Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$rows)) !== false)";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3) . "\$i = 1;";
			$exel[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Based on data size we adapt the behaviour.";
			$exel[] = Indent::_(3) . "\$xls_mode = 1;";
			$exel[] = Indent::_(3) . "if (\$size > 3000)";
			$exel[] = Indent::_(3) . "{";
			$exel[] = Indent::_(4) . "\$xls_mode = 3;";
			$exel[] = Indent::_(4) . "\$file_type = 'Csv';";
			$exel[] = Indent::_(3) . "}";
			$exel[] = Indent::_(3) . "elseif (\$size > 2000)";
			$exel[] = Indent::_(3) . "{";
			$exel[] = Indent::_(4) . "\$xls_mode = 2;";
			$exel[] = Indent::_(3) . "}";
			$exel[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Set active sheet and get it.";
			$exel[] = Indent::_(3)
				. "\$active_sheet = \$spreadsheet->setActiveSheetIndex(0);";
			$exel[] = Indent::_(3) . "foreach (\$rows as \$array)";
			$exel[] = Indent::_(3) . "{";
			$exel[] = Indent::_(4) . "\$a = 'A';";
			$exel[] = Indent::_(4) . "foreach (\$array as \$value)";
			$exel[] = Indent::_(4) . "{";
			$exel[] = Indent::_(5)
				. "\$active_sheet->setCellValue(\$a.\$i, \$value);";
			$exel[] = Indent::_(5) . "if (\$xls_mode != 3)";
			$exel[] = Indent::_(5) . "{";
			$exel[] = Indent::_(6) . "if (\$i == 1)";
			$exel[] = Indent::_(6) . "{";
			$exel[] = Indent::_(7)
				. "\$active_sheet->getColumnDimension(\$a)->setAutoSize(true);";
			$exel[] = Indent::_(7)
				. "\$active_sheet->getStyle(\$a.\$i)->applyFromArray(\$headerStyles);";
			$exel[] = Indent::_(7)
				. "\$active_sheet->getStyle(\$a.\$i)->getAlignment()->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);";
			$exel[] = Indent::_(6) . "}";
			$exel[] = Indent::_(6) . "elseif (\$a === 'A')";
			$exel[] = Indent::_(6) . "{";
			$exel[] = Indent::_(7)
				. "\$active_sheet->getStyle(\$a.\$i)->applyFromArray(\$sideStyles);";
			$exel[] = Indent::_(6) . "}";
			$exel[] = Indent::_(6) . "elseif (\$xls_mode == 1)";
			$exel[] = Indent::_(6) . "{";
			$exel[] = Indent::_(7)
				. "\$active_sheet->getStyle(\$a.\$i)->applyFromArray(\$normalStyles);";
			$exel[] = Indent::_(6) . "}";
			$exel[] = Indent::_(5) . "}";
			$exel[] = Indent::_(5) . "\$a++;";
			$exel[] = Indent::_(4) . "}";
			$exel[] = Indent::_(4) . "\$i++;";
			$exel[] = Indent::_(3) . "}";
			$exel[] = Indent::_(2) . "}";
			$exel[] = Indent::_(2) . "else";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3) . "return false;";
			$exel[] = Indent::_(2) . "}";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Rename worksheet";
			$exel[] = Indent::_(2)
				. "\$spreadsheet->getActiveSheet()->setTitle(\$subjectTab);";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Set active sheet index to the first sheet, so Excel opens this as the first sheet";
			$exel[] = Indent::_(2) . "\$spreadsheet->setActiveSheetIndex(0);";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Redirect output to a client's web browser (Excel5)";
			$exel[] = Indent::_(2)
				. "header('Content-Type: application/vnd.ms-excel');";
			$exel[] = Indent::_(2)
				. "header('Content-Disposition: attachment;filename=\"' . \$fileName . '.' . strtolower(\$file_type) .'\"');";
			$exel[] = Indent::_(2) . "header('Cache-Control: max-age=0');";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " If you're serving to IE 9, then the following may be needed";
			$exel[] = Indent::_(2) . "header('Cache-Control: max-age=1');";
			$exel[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " If you're serving to IE over SSL, then the following may be needed";
			$exel[] = Indent::_(2)
				. "header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); //"
				. Line::_(__Line__, __Class__) . " Date in the past";
			$exel[] = Indent::_(2)
				. "header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); //"
				. Line::_(__Line__, __Class__) . " always modified";
			$exel[] = Indent::_(2)
				. "header ('Cache-Control: cache, must-revalidate'); //"
				. Line::_(__Line__, __Class__) . " HTTP/1.1";
			$exel[] = Indent::_(2) . "header ('Pragma: public'); //"
				. Line::_(__Line__, __Class__) . " HTTP/1.0";
			$exel[] = PHP_EOL . Indent::_(2)
				. "\$writer = IOFactory::createWriter(\$spreadsheet, \$file_type);";
			$exel[] = Indent::_(2) . "\$writer->save('php://output');";
			$exel[] = Indent::_(2) . "jexit();";
			$exel[] = Indent::_(1) . "}";
			$exel[] = PHP_EOL . Indent::_(1) . "/**";
			$exel[] = Indent::_(1) . "* Get CSV Headers";
			$exel[] = Indent::_(1) . "*/";
			$exel[] = Indent::_(1)
				. "public static function getFileHeaders(\$dataType)";
			$exel[] = Indent::_(1) . "{";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " make sure we have the composer classes loaded";
			$exel[] = Indent::_(2)
				. "self::composerAutoload('phpspreadsheet');";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " get session object";
			$exel[] = $this->getSessionObject();
			$exel[] = Indent::_(2)
				. "\$package = \$session->get('package', null);";
			$exel[] = Indent::_(2)
				. "\$package = json_decode(\$package, true);";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " set the headers";
			$exel[] = Indent::_(2) . "if(isset(\$package['dir']))";
			$exel[] = Indent::_(2) . "{";
			$exel[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " only load first three rows";
			$exel[] = Indent::_(3)
				. "\$chunkFilter = new PhpOffice\PhpSpreadsheet\Reader\chunkReadFilter(2,1);";
			$exel[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " identify the file type";
			$exel[] = Indent::_(3)
				. "\$inputFileType = IOFactory::identify(\$package['dir']);";
			$exel[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " create the reader for this file type";
			$exel[] = Indent::_(3)
				. "\$excelReader = IOFactory::createReader(\$inputFileType);";
			$exel[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " load the limiting filter";
			$exel[] = Indent::_(3)
				. "\$excelReader->setReadFilter(\$chunkFilter);";
			$exel[] = Indent::_(3) . "\$excelReader->setReadDataOnly(true);";
			$exel[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " load the rows (only first three)";
			$exel[] = Indent::_(3)
				. "\$excelObj = \$excelReader->load(\$package['dir']);";
			$exel[] = Indent::_(3) . "\$headers = [];";
			$exel[] = Indent::_(3)
				. "foreach (\$excelObj->getActiveSheet()->getRowIterator() as \$row)";
			$exel[] = Indent::_(3) . "{";
			$exel[] = Indent::_(4) . "if(\$row->getRowIndex() == 1)";
			$exel[] = Indent::_(4) . "{";
			$exel[] = Indent::_(5)
				. "\$cellIterator = \$row->getCellIterator();";
			$exel[] = Indent::_(5)
				. "\$cellIterator->setIterateOnlyExistingCells(false);";
			$exel[] = Indent::_(5) . "foreach (\$cellIterator as \$cell)";
			$exel[] = Indent::_(5) . "{";
			$exel[] = Indent::_(6) . "if (!is_null(\$cell))";
			$exel[] = Indent::_(6) . "{";
			$exel[] = Indent::_(7)
				. "\$headers[\$cell->getColumn()] = \$cell->getValue();";
			$exel[] = Indent::_(6) . "}";
			$exel[] = Indent::_(5) . "}";
			$exel[] = Indent::_(5) . "\$excelObj->disconnectWorksheets();";
			$exel[] = Indent::_(5) . "unset(\$excelObj);";
			$exel[] = Indent::_(5) . "break;";
			$exel[] = Indent::_(4) . "}";
			$exel[] = Indent::_(3) . "}";
			$exel[] = Indent::_(3) . "return \$headers;";
			$exel[] = Indent::_(2) . "}";
			$exel[] = Indent::_(2) . "return false;";
			$exel[] = Indent::_(1) . "}";
			$exel[] = PHP_EOL . Indent::_(1) . "/**";
			$exel[] = Indent::_(1)
				. "* Load the Composer Vendor phpspreadsheet";
			$exel[] = Indent::_(1) . "*/";
			$exel[] = Indent::_(1)
				. "protected static function composephpspreadsheet()";
			$exel[] = Indent::_(1) . "{";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " load the autoloader for phpspreadsheet";
			$exel[] = Indent::_(2)
				. "require_once JPATH_SITE . '/libraries/phpspreadsheet/vendor/autoload.php';";
			$exel[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " do not load again";
			$exel[] = Indent::_(2)
				. "self::\$composer['phpspreadsheet'] = true;";
			$exel[] = PHP_EOL . Indent::_(2) . "return  true;";
			$exel[] = Indent::_(1) . "}";

			// return the help methods
			return implode(PHP_EOL, $exel);
		}

		return '';
	}

	/**
	 * Get the generated user-lookup lines of the `xls()` method.
	 *
	 * The legacy Joomla 4+ generator assigned its identity lookup to an
	 * unused buffer, so no user assignment reached the generated method.
	 * That current behavior is preserved here; the Joomla 3 variant
	 * supplies its own lookup line.
	 *
	 * @return  array<int, string>  The generated user-lookup lines.
	 *
	 * @since   6.1.7
	 */
	protected function getUserLines(): array
	{
		return [];
	}

	/**
	 * Get the statement that loads the import-header reader's session.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getSessionObject(): string
	{
		return Indent::_(2) . "\$session = Joomla__"
			. "_39403062_84fb_46e0_bac4_0023f766e827___Power::getSession();";
	}
}

