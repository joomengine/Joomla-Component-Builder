/**
 * DKIM DNS preview for the component email configuration.
 *
 * @package Joomla.Component.Builder
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

function vdm_dkim() {
	const domain = document.querySelector('#jform_dkim_domain');
	const domainLabel = document.querySelector('#a_dkim_domain');
	const selector = document.querySelector('#jform_dkim_selector');
	const key = document.querySelector('#jform_dkim_key');
	const publicKey = document.querySelector('#jform_dkim_public_key');
	const value = document.querySelector('#jform_dkim_value');

	if (!domain || !domainLabel || !selector || !key || !publicKey || !value) {
		return;
	}

	domainLabel.textContent = domain.value;
	key.value = selector.value + '._domainkey';
	value.value = 'v=DKIM1;k=rsa;g=*;s=email;h=sha1;t=s;p=' + (publicKey.value || 'PUBLICKEY');
}

(function () {
	const initialise = function () {
		const domain = document.querySelector('#jform_dkim_domain');
		if (!domain) {
			return;
		}
		if (!domain.value) {
			domain.value = window.location.hostname;
		}
		['#jform_dkim_key', '#jform_dkim_value'].forEach(function (selector) {
			const input = document.querySelector(selector);
			if (input) {
				input.addEventListener('click', function () { this.select(); });
			}
		});
		vdm_dkim();
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialise, {once: true});
	} else {
		initialise();
	}
})();
