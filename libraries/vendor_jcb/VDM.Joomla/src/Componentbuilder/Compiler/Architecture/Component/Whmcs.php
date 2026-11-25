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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Component;


use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplicationInterface as CMSApplication;
use Joomla\CMS\Language\Text;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\FOF\Encrypt\AES;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * WHMCS Encryption Class Generation.
 * 
 * Generates the body of the `whmcs.php` file: the WHMCS runtime class that
 * validates a component's WHMCS license key against the configured WHMCS
 * server. The component's stored key is decrypted with the JCB basic key
 * on the host before the connection details are embedded in the generated
 * class.
 * 
 * @since  6.1.7
 */
final class Whmcs
{
	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 6.1.7
	 */
	protected Component $component;

	/**
	 * The CMS Application.
	 *
	 * @var   CMSApplication
	 * @since 6.1.7
	 */
	protected CMSApplication $app;

	/**
	 * Constructor.
	 *
	 * @param Component             $component   The Component Class.
	 * @param CMSApplication|null   $app         The CMS Application object.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Component $component, ?CMSApplication $app = null)
	{
		$this->component = $component;
		$this->app = $app ?: Factory::getApplication();
	}

	/**
	 * Get the WHMCS encryption class code.
	 *
	 * When the component has no stored WHMCS key, or the key cannot be
	 * decrypted with the JCB basic key, error notices are enqueued and a
	 * comment fragment is returned instead of the class body.
	 *
	 * @return  string  The generated WHMCS class code, or the fallback comment.
	 *
	 * @since   6.1.7
	 */
	public function get(): string
	{
		// make sure we have the correct file
		if ($this->component->isString('whmcs_key'))
		{
			// Get the basic encryption.
			$basickey = ComponentbuilderHelper::getCryptKey('basic');
			$key = $this->component->get('whmcs_key');

			// Get the encryption object.
			$basic = new AES($basickey);
			if ($basickey && $key === base64_encode(
					base64_decode((string) $key, true)
				))
			{
				// basic decrypt data whmcs_key.
				$key = rtrim(
					(string) $basic->decryptString($key), "\0"
				);
				// set the needed string to connect to whmcs
				$key["kasier"] = $this->component->get('whmcs_url', '');
				$key["geheim"] = $key;
				$key["onthou"] = 1;
				// prep the call info
				$theKey = base64_encode(serialize($key));
				// set the script
				$encrypt = [];
				$encrypt[] = "/**";
				$encrypt[] = "* " . Line::_(__Line__, __Class__) . "WHMCS Class ";
				$encrypt[] = "**/";
				$encrypt[] = "class WHMCS";
				$encrypt[] = "{";
				$encrypt[] = Indent::_(1) . "public \$_key = false;";
				$encrypt[] = Indent::_(1) . "public \$_is = false;";
				$encrypt[] = PHP_EOL . Indent::_(1)
					. "public function __construct(\$Vk5smi0wjnjb)";
				$encrypt[] = Indent::_(1) . "{";
				$encrypt[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " get the session";
				$encrypt[] = Indent::_(2)
					. "\$session = Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getSession();";
				$encrypt[] = Indent::_(2)
					. "\$V2uekt2wcgwk = \$session->get(\$Vk5smi0wjnjb, null);";
				$encrypt[] = Indent::_(2)
					. "\$h4sgrGsqq = \$this->get(\$Vk5smi0wjnjb,\$V2uekt2wcgwk);";
				$encrypt[] = Indent::_(2)
					. "if (isset(\$h4sgrGsqq['nuut']) && \$h4sgrGsqq['nuut'] && (isset(\$h4sgrGsqq['status']) && 'Active' === \$h4sgrGsqq['status']) && isset(\$h4sgrGsqq['eiegrendel']) && strlen(\$h4sgrGsqq['eiegrendel']) > 300)";
				$encrypt[] = Indent::_(2) . "{";
				$encrypt[] = Indent::_(3)
					. "\$session->set(\$Vk5smi0wjnjb, \$h4sgrGsqq['eiegrendel']);";
				$encrypt[] = Indent::_(2) . "}";
				$encrypt[] = Indent::_(2)
					. "if ((isset(\$h4sgrGsqq['status']) && 'Active' === \$h4sgrGsqq['status']) && isset(\$h4sgrGsqq['md5hash']) && strlen(\$h4sgrGsqq['md5hash']) == 32 && isset(\$h4sgrGsqq['customfields']) && strlen(\$h4sgrGsqq['customfields']) > 4)";
				$encrypt[] = Indent::_(2) . "{";
				$encrypt[] = Indent::_(3)
					. "\$this->_key = md5(\$h4sgrGsqq['customfields']);";
				$encrypt[] = Indent::_(2) . "}";
				$encrypt[] = Indent::_(2)
					. "if ((isset(\$h4sgrGsqq['status']) && 'Active' === \$h4sgrGsqq['status']) && isset(\$h4sgrGsqq['md5hash']) && strlen(\$h4sgrGsqq['md5hash']) == 32 )";
				$encrypt[] = Indent::_(2) . "{";
				$encrypt[] = Indent::_(3) . "\$this->_is = true;";
				$encrypt[] = Indent::_(2) . "}";
				$encrypt[] = Indent::_(1) . "}";
				$encrypt[] = PHP_EOL . Indent::_(1)
					. "private function get(\$Vk5smi0wjnjb,\$V2uekt2wcgwk)";
				$encrypt[] = Indent::_(1) . "{";
				$encrypt[] = Indent::_(2)
					. "\$Viioj50xuqu2 = unserialize(base64_decode('" . $theKey
					. "'));";
				$encrypt[] = Indent::_(2)
					. "\$Visqfrd1caus = time() . md5(mt_rand(1000000000, 9999999999) . \$Vk5smi0wjnjb);";
				$encrypt[] = Indent::_(2) . "\$Vo4tezfgcf3e = date(\"Ymd\");";
				$encrypt[] = Indent::_(2)
					. "\$Vozblwvfym2f = \$_SERVER['SERVER_NAME'];";
				$encrypt[] = Indent::_(2)
					. "\$Vozblwvfym2fdie = isset(\$_SERVER['SERVER_ADDR']) ? \$_SERVER['SERVER_ADDR'] : \$_SERVER['LOCAL_ADDR'];";
				$encrypt[] = Indent::_(2)
					. "\$V343jp03dxco = dirname(__FILE__);";
				$encrypt[] = Indent::_(2)
					. "\$Vc2rayehw4f0 = unserialize(base64_decode('czozNjoibW9kdWxlcy9zZXJ2ZXJzL2xpY2Vuc2luZy92ZXJpZnkucGhwIjs='));";
				$encrypt[] = Indent::_(2) . "\$Vlpolphukogz = false;";
				$encrypt[] = Indent::_(2) . "if (\$V2uekt2wcgwk) {";
				$encrypt[] = Indent::_(3) . "\$V2uekt2wcgwk = str_replace(\""
					. '".PHP_EOL."' . "\", '', \$V2uekt2wcgwk);";
				$encrypt[] = Indent::_(3)
					. "\$Vm5cxjdc43g4 = substr(\$V2uekt2wcgwk, 0, strlen(\$V2uekt2wcgwk) - 32);";
				$encrypt[] = Indent::_(3)
					. "\$Vbgx0efeu2sy = substr(\$V2uekt2wcgwk, strlen(\$V2uekt2wcgwk) - 32);";
				$encrypt[] = Indent::_(3)
					. "if (\$Vbgx0efeu2sy == md5(\$Vm5cxjdc43g4 . \$Viioj50xuqu2['geheim'])) {";
				$encrypt[] = Indent::_(4)
					. "\$Vm5cxjdc43g4 = strrev(\$Vm5cxjdc43g4);";
				$encrypt[] = Indent::_(4)
					. "\$Vbgx0efeu2sy = substr(\$Vm5cxjdc43g4, 0, 32);";
				$encrypt[] = Indent::_(4)
					. "\$Vm5cxjdc43g4 = substr(\$Vm5cxjdc43g4, 32);";
				$encrypt[] = Indent::_(4)
					. "\$Vm5cxjdc43g4 = base64_decode(\$Vm5cxjdc43g4);";
				$encrypt[] = Indent::_(4)
					. "\$Vm5cxjdc43g4finding = unserialize(\$Vm5cxjdc43g4);";
				$encrypt[] = Indent::_(4)
					. "\$V3qqz0p00fbq  = \$Vm5cxjdc43g4finding['dan'];";
				$encrypt[] = Indent::_(4)
					. "if (\$Vbgx0efeu2sy == md5(\$V3qqz0p00fbq  . \$Viioj50xuqu2['geheim'])) {";
				$encrypt[] = Indent::_(5)
					. "\$Vbfbwv2y4kre = date(\"Ymd\", mktime(0, 0, 0, date(\"m\"), date(\"d\") - \$Viioj50xuqu2['onthou'], date(\"Y\")));";
				$encrypt[] = Indent::_(5)
					. "if (\$V3qqz0p00fbq  > \$Vbfbwv2y4kre) {";
				$encrypt[] = Indent::_(6) . "\$Vlpolphukogz = true;";
				$encrypt[] = Indent::_(6)
					. "\$Vwasqoybpyed = \$Vm5cxjdc43g4finding;";
				$encrypt[] = Indent::_(6)
					. "\$Vcixw3trerrt = explode(',', \$Vwasqoybpyed['validdomain']);";
				$encrypt[] = Indent::_(6)
					. "if (!in_array(\$_SERVER['SERVER_NAME'], \$Vcixw3trerrt)) {";
				$encrypt[] = Indent::_(7) . "\$Vlpolphukogz = false;";
				$encrypt[] = Indent::_(7)
					. "\$Vm5cxjdc43g4finding['status'] = \"sleg\";";
				$encrypt[] = Indent::_(7) . "\$Vwasqoybpyed = [];";
				$encrypt[] = Indent::_(6) . "}";
				$encrypt[] = Indent::_(6)
					. "\$Vkni3xyhkqzv = explode(',', \$Vwasqoybpyed['validip']);";
				$encrypt[] = Indent::_(6)
					. "if (!in_array(\$Vozblwvfym2fdie, \$Vkni3xyhkqzv)) {";
				$encrypt[] = Indent::_(7) . "\$Vlpolphukogz = false;";
				$encrypt[] = Indent::_(7)
					. "\$Vm5cxjdc43g4finding['status'] = \"sleg\";";
				$encrypt[] = Indent::_(7) . "\$Vwasqoybpyed = [];";
				$encrypt[] = Indent::_(6) . "}";
				$encrypt[] = Indent::_(6)
					. "\$Vckfvnepoaxj = explode(',', \$Vwasqoybpyed['validdirectory']);";
				$encrypt[] = Indent::_(6)
					. "if (!in_array(\$V343jp03dxco, \$Vckfvnepoaxj)) {";
				$encrypt[] = Indent::_(7) . "\$Vlpolphukogz = false;";
				$encrypt[] = Indent::_(7)
					. "\$Vm5cxjdc43g4finding['status'] = \"sleg\";";
				$encrypt[] = Indent::_(7) . "\$Vwasqoybpyed = [];";
				$encrypt[] = Indent::_(6) . "}";
				$encrypt[] = Indent::_(5) . "}";
				$encrypt[] = Indent::_(4) . "}";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(2) . "}";
				$encrypt[] = Indent::_(2) . "if (!\$Vlpolphukogz) {";
				$encrypt[] = Indent::_(3) . "\$V1u0c4dl3ehp = array(";
				$encrypt[] = Indent::_(4) . "'licensekey' => \$Vk5smi0wjnjb,";
				$encrypt[] = Indent::_(4) . "'domain' => \$Vozblwvfym2f,";
				$encrypt[] = Indent::_(4) . "'ip' => \$Vozblwvfym2fdie,";
				$encrypt[] = Indent::_(4) . "'dir' => \$V343jp03dxco,";
				$encrypt[] = Indent::_(3) . ");";
				$encrypt[] = Indent::_(3)
					. "if (\$Visqfrd1caus)";
				$encrypt[] = Indent::_(3) . "{";
				$encrypt[] = Indent::_(4)
					. "\$V1u0c4dl3ehp['check_token'] = \$Visqfrd1caus;";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(3) . "\$Vdsjeyjmpq2o = '';";
				$encrypt[] = Indent::_(3)
					. "foreach (\$V1u0c4dl3ehp AS \$V2sgyscukmgi=>\$V1u00zkzmb1d) {";
				$encrypt[] = Indent::_(4)
					. "\$Vdsjeyjmpq2o .= \$V2sgyscukmgi.'='.urlencode(\$V1u00zkzmb1d).'&';";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(3)
					. "if (function_exists('curl_exec')) {";
				$encrypt[] = Indent::_(4) . "\$Vdathuqgjyf0 = curl_init();";
				$encrypt[] = Indent::_(4)
					. "curl_setopt(\$Vdathuqgjyf0, CURLOPT_URL, \$Viioj50xuqu2['kasier'] . \$Vc2rayehw4f0);";
				$encrypt[] = Indent::_(4)
					. "curl_setopt(\$Vdathuqgjyf0, CURLOPT_POST, 1);";
				$encrypt[] = Indent::_(4)
					. "curl_setopt(\$Vdathuqgjyf0, CURLOPT_POSTFIELDS, \$Vdsjeyjmpq2o);";
				$encrypt[] = Indent::_(4)
					. "curl_setopt(\$Vdathuqgjyf0, CURLOPT_TIMEOUT, 30);";
				$encrypt[] = Indent::_(4)
					. "curl_setopt(\$Vdathuqgjyf0, CURLOPT_RETURNTRANSFER, 1);";
				$encrypt[] = Indent::_(4)
					. "\$Vqojefyeohg5 = curl_exec(\$Vdathuqgjyf0);";
				$encrypt[] = Indent::_(4) . "curl_close(\$Vdathuqgjyf0);";
				$encrypt[] = Indent::_(3) . "} else {";
				$encrypt[] = Indent::_(4)
					. "\$Vrpmu4bvnmkp = fsockopen(\$Viioj50xuqu2['kasier'], 80, \$Vc0t5kmpwkwk, \$Va3g41fnofhu, 5);";
				$encrypt[] = Indent::_(4) . "if (\$Vrpmu4bvnmkp) {";
				$encrypt[] = Indent::_(5) . "\$Vznkm0a0me1y = \"\r" . PHP_EOL
					. "\";";
				$encrypt[] = Indent::_(5)
					. "\$V2sgyscukmgiop = \"POST \".\$Viioj50xuqu2['kasier'] . \$Vc2rayehw4f0 . \" HTTP/1.0\" . \$Vznkm0a0me1y;";
				$encrypt[] = Indent::_(5)
					. "\$V2sgyscukmgiop .= \"Host: \".\$Viioj50xuqu2['kasier'] . \$Vznkm0a0me1y;";
				$encrypt[] = Indent::_(5)
					. "\$V2sgyscukmgiop .= \"Content-type: application/x-www-form-urlencoded\" . \$Vznkm0a0me1y;";
				$encrypt[] = Indent::_(5)
					. "\$V2sgyscukmgiop .= \"Content-length: \".@strlen(\$Vdsjeyjmpq2o) . \$Vznkm0a0me1y;";
				$encrypt[] = Indent::_(5)
					. "\$V2sgyscukmgiop .= \"Connection: close\" . \$Vznkm0a0me1y . \$Vznkm0a0me1y;";
				$encrypt[] = Indent::_(5)
					. "\$V2sgyscukmgiop .= \$Vdsjeyjmpq2o;";
				$encrypt[] = Indent::_(5) . "\$Vqojefyeohg5 = '';";
				$encrypt[] = Indent::_(5)
					. "@stream_set_timeout(\$Vrpmu4bvnmkp, 20);";
				$encrypt[] = Indent::_(5)
					. "@fputs(\$Vrpmu4bvnmkp, \$V2sgyscukmgiop);";
				$encrypt[] = Indent::_(5)
					. "\$V2czq24pjexf = @socket_get_status(\$Vrpmu4bvnmkp);";
				$encrypt[] = Indent::_(5)
					. "while (!@feof(\$Vrpmu4bvnmkp)&&\$V2czq24pjexf) {";
				$encrypt[] = Indent::_(6)
					. "\$Vqojefyeohg5 .= @fgets(\$Vrpmu4bvnmkp, 1024);";
				$encrypt[] = Indent::_(6)
					. "\$V2czq24pjexf = @socket_get_status(\$Vrpmu4bvnmkp);";
				$encrypt[] = Indent::_(5) . "}";
				$encrypt[] = Indent::_(5) . "@fclose (\$Vqojefyeohg5);";
				$encrypt[] = Indent::_(4) . "}";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(3) . "if (!\$Vqojefyeohg5) {";
				$encrypt[] = Indent::_(4)
					. "\$Vbfbwv2y4kre = date(\"Ymd\", mktime(0, 0, 0, date(\"m\"), date(\"d\") - \$Viioj50xuqu2['onthou'], date(\"Y\")));";
				$encrypt[] = Indent::_(4)
					. "if (isset(\$V3qqz0p00fbq) && \$V3qqz0p00fbq  > \$Vbfbwv2y4kre) {";
				$encrypt[] = Indent::_(5)
					. "\$Vwasqoybpyed = \$Vm5cxjdc43g4finding;";
				$encrypt[] = Indent::_(4) . "} else {";
				$encrypt[] = Indent::_(5) . "\$Vwasqoybpyed = [];";
				$encrypt[] = Indent::_(5)
					. "\$Vwasqoybpyed['status'] = \"sleg\";";
				$encrypt[] = Indent::_(5)
					. "\$Vwasqoybpyed['description'] = \"Remote Check Failed\";";
				$encrypt[] = Indent::_(5) . "return \$Vwasqoybpyed;";
				$encrypt[] = Indent::_(4) . "}";
				$encrypt[] = Indent::_(3) . "} else {";
				$encrypt[] = Indent::_(4) . "preg_match_all('"
					. '/<(.*?)>([^<]+)<\/\\1>/i'
					. "', \$Vqojefyeohg5, \$V1ot20wob03f);";
				$encrypt[] = Indent::_(4) . "\$Vwasqoybpyed = [];";
				$encrypt[] = Indent::_(4)
					. "foreach (\$V1ot20wob03f[1] AS \$V2sgyscukmgi=>\$V1u00zkzmb1d) {";
				$encrypt[] = Indent::_(5)
					. "\$Vwasqoybpyed[\$V1u00zkzmb1d] = \$V1ot20wob03f[2][\$V2sgyscukmgi];";
				$encrypt[] = Indent::_(4) . "}";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(3) . "if (!is_array(\$Vwasqoybpyed)) {";
				$encrypt[] = Indent::_(4)
					. "die(\"Invalid License Server Response\");";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(3)
					. "if (isset(\$Vwasqoybpyed['md5hash']) && \$Vwasqoybpyed['md5hash']) {";
				$encrypt[] = Indent::_(4)
					. "if (\$Vwasqoybpyed['md5hash'] != md5(\$Viioj50xuqu2['geheim'] . \$Visqfrd1caus)) {";
				$encrypt[] = Indent::_(5)
					. "\$Vwasqoybpyed['status'] = \"sleg\";";
				$encrypt[] = Indent::_(5)
					. "\$Vwasqoybpyed['description'] = \"MD5 Checksum Verification Failed\";";
				$encrypt[] = Indent::_(5) . "return \$Vwasqoybpyed;";
				$encrypt[] = Indent::_(4) . "}";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(3)
					. "if (isset(\$Vwasqoybpyed['status']) && \$Vwasqoybpyed['status'] == \"Active\") {";
				$encrypt[] = Indent::_(4)
					. "\$Vwasqoybpyed['dan'] = \$Vo4tezfgcf3e;";
				$encrypt[] = Indent::_(4)
					. "\$Vqojefyeohg5ing = serialize(\$Vwasqoybpyed);";
				$encrypt[] = Indent::_(4)
					. "\$Vqojefyeohg5ing = base64_encode(\$Vqojefyeohg5ing);";
				$encrypt[] = Indent::_(4)
					. "\$Vqojefyeohg5ing = md5(\$Vo4tezfgcf3e . \$Viioj50xuqu2['geheim']) . \$Vqojefyeohg5ing;";
				$encrypt[] = Indent::_(4)
					. "\$Vqojefyeohg5ing = strrev(\$Vqojefyeohg5ing);";
				$encrypt[] = Indent::_(4)
					. "\$Vqojefyeohg5ing = \$Vqojefyeohg5ing . md5(\$Vqojefyeohg5ing . \$Viioj50xuqu2['geheim']);";
				$encrypt[] = Indent::_(4)
					. "\$Vqojefyeohg5ing = wordwrap(\$Vqojefyeohg5ing, 80, \""
					. '".PHP_EOL."' . "\", true);";
				$encrypt[] = Indent::_(4)
					. "\$Vwasqoybpyed['eiegrendel'] = \$Vqojefyeohg5ing;";
				$encrypt[] = Indent::_(3) . "}";
				$encrypt[] = Indent::_(3) . "\$Vwasqoybpyed['nuut'] = true;";
				$encrypt[] = Indent::_(2) . "}";
				$encrypt[] = Indent::_(2)
					. "unset(\$V1u0c4dl3ehp,\$Vqojefyeohg5,\$V1ot20wob03f,\$Viioj50xuqu2['kasier'],\$Viioj50xuqu2['geheim'],\$Vo4tezfgcf3e,\$Vozblwvfym2fdie,\$Viioj50xuqu2['onthou'],\$Vbgx0efeu2sy);";
				$encrypt[] = Indent::_(2) . "return \$Vwasqoybpyed;";
				$encrypt[] = Indent::_(1) . "}";
				$encrypt[] = "}";

				// return the help methods
				return implode(PHP_EOL, $encrypt);
			}
		}
		// give notice of this issue
		$this->app->enqueueMessage(
			Text::_('COM_COMPONENTBUILDER_HR_HTHREEWHMCS_ERRORHTHREE'), 'Error'
		);
		$this->app->enqueueMessage(
			Text::_(
				'The <b>WHMCS class</b> could not be added to this component. You will need to enable the add-on in the Joomla Component area (Add WHMCS)->Yes. If you have done this, then please check that you have your own <b>Basic Encryption<b/> set in the global settings of JCB. Then open and save this component again, making sure that your WHMCS settings are still correct.'
			), 'Error'
		);

		return "//" . Line::_(__Line__, __Class__)
			. " The WHMCS class could not be added to this component." . PHP_EOL
			. "//" . Line::_(__Line__, __Class__)
			. " Please note that you will need to enable the add-on in the Joomla Component area (Add WHMCS)->Yes.";
	}
}

