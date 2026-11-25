/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* JS Document */

const memoryinitialization = {};

/**
 * Check whether a value looks like a plain object.
 *
 * @param   {*}  value  The value to inspect.
 *
 * @returns {boolean} True if the value is a plain object.
 */
function isPlainObject(value) {
	return Object.prototype.toString.call(value) === '[object Object]';
}

/**
 * Check whether a value is valid JSON text.
 *
 * @param   {*}  value  The value to test.
 *
 * @returns {boolean} True if the value can be JSON parsed.
 */
function isJsonString(value) {
	if (typeof value !== 'string' || value === '') {
		return false;
	}

	try {
		JSON.parse(value);
		return true;
	} catch (error) {
		return false;
	}
}

/**
 * Convert a value to JSON text.
 *
 * @param   {*}  value  The value to encode.
 *
 * @returns {string|null} The JSON text, or null on failure.
 */
function encodeSessionValue(value) {
	try {
		return JSON.stringify(value);
	} catch (error) {
		console.error('Failed to encode session value:', error);
		return null;
	}
}

/**
 * Decode JSON text from session memory.
 *
 * @param   {*}  value         The stored JSON text.
 * @param   {*}  defaultValue  The fallback value.
 *
 * @returns {*} The decoded value or default.
 */
function decodeSessionValue(value, defaultValue = null) {
	if (!isJsonString(value)) {
		return defaultValue;
	}

	try {
		return JSON.parse(value);
	} catch (error) {
		console.error('Failed to decode session value:', error);
		return defaultValue;
	}
}

/**
 * Cached session storage availability flag.
 *
 * Evaluated once at load time to avoid repeated write-tests
 * on every get/set call.
 *
 * @type {boolean}
 */
const _sessionStorageAvailable = (() => {
	try {
		if (typeof window === 'undefined' || !window.sessionStorage) {
			return false;
		}

		const testKey = '__jcb_session_test__';
		window.sessionStorage.setItem(testKey, '1');
		window.sessionStorage.removeItem(testKey);

		return true;
	} catch (error) {
		return false;
	}
})();

/**
 * Determine whether session storage is usable.
 *
 * @returns {boolean} True when sessionStorage is available.
 */
function hasSessionStorage() {
	return _sessionStorageAvailable;
}

/**
 * Retrieve a parsed value from session storage.
 *
 * @param   {string}  key           The storage key.
 * @param   {*}       defaultValue  The fallback value.
 *
 * @returns {*} The stored value or default.
 */
function getSessionMemory(key, defaultValue = null) {
	if (typeof key !== 'string' || key === '') {
		return defaultValue;
	}

	if (hasSessionStorage()) {
		try {
			return decodeSessionValue(window.sessionStorage.getItem(key), defaultValue);
		} catch (error) {
			console.error('Failed to read session storage:', error);
			return defaultValue;
		}
	}

	if (typeof memoryinitialization[key] !== 'undefined') {
		return decodeSessionValue(memoryinitialization[key], defaultValue);
	}

	return defaultValue;
}

/**
 * Merge new values into an existing session storage entry.
 *
 * @param   {string}  key     The storage key.
 * @param   {*}       values  The values to merge.
 *
 * @returns {string|null} Encoded merged value.
 */
function mergeSessionMemory(key, values) {
	const oldValues = getSessionMemory(key, null);

	if (isPlainObject(oldValues) && isPlainObject(values)) {
		return encodeSessionValue({ ...oldValues, ...values });
	}

	return encodeSessionValue(values);
}

/**
 * Store a value in session storage.
 *
 * @param   {string}   key     The storage key.
 * @param   {*}        values  The values to store.
 * @param   {boolean}  merge   Whether to merge plain objects with existing data.
 *
 * @returns {boolean} True on success.
 */
function setSessionMemory(key, values, merge = true) {
	if (typeof key !== 'string' || key === '') {
		return false;
	}

	const payload = merge ? mergeSessionMemory(key, values) : encodeSessionValue(values);

	if (payload === null) {
		return false;
	}

	if (hasSessionStorage()) {
		try {
			window.sessionStorage.setItem(key, payload);
			return true;
		} catch (error) {
			console.error('Failed to write session storage:', error);
		}
	}

	memoryinitialization[key] = payload;

	return true;
}

/**
 * Ensure a value is returned as an array.
 *
 * @param   {*}  items  The value to normalize.
 *
 * @returns {Array} The normalized array.
 */
function getArrayFormat(items) {
	if (Array.isArray(items)) {
		return items;
	}

	if (isPlainObject(items)) {
		return Object.values(items);
	}

	return [];
}

/**
 * Get a translated string safely.
 *
 * @param   {string}  key           The translation key.
 * @param   {string}  fallbackText  The fallback text.
 *
 * @returns {string} The translated or fallback text.
 */
function translate(key, fallbackText = '') {
	if (
		typeof Joomla !== 'undefined'
		&& Joomla.Text
		&& typeof Joomla.Text._ === 'function'
	) {
		const translated = Joomla.Text._(key);

		if (typeof translated === 'string' && translated !== '') {
			return translated;
		}
	}

	return fallbackText || key;
}
class InitializationManager {
	/** @type {HTMLElement|null} */
	#repoArea = document.getElementById('select-repo-area');

	/** @type {HTMLElement|null} */
	#powersArea = document.getElementById('select-powers-area');

	/** @type {HTMLButtonElement|null} */
	#initButton = document.getElementById('init-selected-powers');

	/** @type {HTMLButtonElement|null} */
	#backButton = document.getElementById('back-to-select-repo');

	/** @type {HTMLElement|null} */
	#loadingDiv = window.loadingDiv || null;

	/** @type {Function|null} */
	#buildTable = typeof window.buildPowerSelectionTable === 'function'
		? window.buildPowerSelectionTable
		: null;

	/** @type {Function|null} */
	#drawTable = typeof window.drawPowerSelectionTable === 'function'
		? window.drawPowerSelectionTable
		: null;

	/** @type {AbortController|null} */
	#repoRequestController = null;

	/** @type {AbortController|null} */
	#initRequestController = null;

	/** @type {boolean} */
	#isLoadingRepo = false;

	/** @type {boolean} */
	#isInitializing = false;

	/**
	 * The current repository GUID.
	 *
	 * @type {string|null}
	 */
	currentRepo = null;

	/**
	 * The current area key.
	 *
	 * @type {string|null}
	 */
	currentArea = null;

	constructor() {
		this._bindRepoButtons();
		this._bindInitSelectedPowers();
		this._updateInitButtonState();
	}

	/**
	 * Get the shared selected items collection.
	 *
	 * @returns {Array<Object>} The selected items.
	 */
	get selectedItems() {
		if (!Array.isArray(window.selectedPowerItems)) {
			window.selectedPowerItems = [];
		}

		return window.selectedPowerItems;
	}

	/**
	 * Set the shared selected items collection.
	 *
	 * @param   {Array<Object>}  items  The selected items.
	 *
	 * @returns {void}
	 */
	set selectedItems(items) {
		window.selectedPowerItems = Array.isArray(items) ? items : [];
		this._updateInitButtonState();
	}

	/**
	 * Add items not already selected.
	 *
	 * @param   {Array<Object>|Object}  data  The items to add.
	 *
	 * @returns {void}
	 */
	addSelectedItems(data) {
		const items = getArrayFormat(data);

		if (items.length === 0) {
			return;
		}

		const updated = [...this.selectedItems];

		for (const item of items) {
			if (!updated.some((existing) => this.#isSameItem(existing, item))) {
				updated.push(item);
			}
		}

		this.selectedItems = updated;
	}

	/**
	 * Remove items from the selection.
	 *
	 * @param   {Array<Object>|Object}  data  The items to remove.
	 *
	 * @returns {void}
	 */
	removeSelectedItems(data) {
		const items = getArrayFormat(data);

		if (items.length === 0) {
			return;
		}

		this.selectedItems = this.selectedItems.filter((existing) => {
			return !items.some((item) => this.#isSameItem(existing, item));
		});
	}

	/**
	 * Compare two items by GUID.
	 *
	 * @param   {Object}  a  First item.
	 * @param   {Object}  b  Second item.
	 *
	 * @returns {boolean} True if both items match.
	 */
	#isSameItem(a, b) {
		return Boolean(a?.guid && b?.guid && a.guid === b.guid);
	}

	/**
	 * Update action button state.
	 *
	 * @returns {void}
	 */
	_updateInitButtonState() {
		if (this.#initButton) {
			this.#initButton.disabled = (
				this.selectedItems.length === 0
				|| this.#isInitializing
				|| this.#isLoadingRepo
			);
		}

		if (this.#backButton) {
			this.#backButton.disabled = this.#isInitializing || this.#isLoadingRepo;
		}
	}

	/**
	 * Toggle repository buttons disabled state.
	 *
	 * @param   {boolean}  disabled  Whether buttons should be disabled.
	 *
	 * @returns {void}
	 */
	_setRepoButtonsDisabled(disabled) {
		document.querySelectorAll('.select-repo-to-load').forEach((button) => {
			button.disabled = disabled;
		});
	}

	/**
	 * Bind repository buttons.
	 *
	 * @returns {void}
	 */
	_bindRepoButtons() {
		document.querySelectorAll('.select-repo-to-load').forEach((button) => {
			button.addEventListener('click', (event) => this._handleRepoClick(event));
		});
	}

	/**
	 * Bind init and back buttons.
	 *
	 * @returns {void}
	 */
	_bindInitSelectedPowers() {
		if (this.#initButton) {
			this.#initButton.addEventListener('click', () => this._handleInitSelectedPowers());
		}

		if (this.#backButton) {
			this.#backButton.addEventListener('click', () => this._handleBackToRepos());
		}
	}

	/**
	 * Handle repository click.
	 *
	 * @param   {Event}  event  The click event.
	 *
	 * @returns {Promise<void>}
	 */
	async _handleRepoClick(event) {
		if (this.#isLoadingRepo || this.#isInitializing) {
			return;
		}

		const button = event.currentTarget;
		const repo = button?.dataset?.repo;
		const area = button?.dataset?.area;

		if (!repo || !area) {
			this._notify(
				translate('COM_COMPONENTBUILDER_MISSING_REPOSITORY_OR_AREA_DATA'),
				'danger'
			);
			return;
		}

		this.#isLoadingRepo = true;
		this._updateInitButtonState();
		this._setRepoButtonsDisabled(true);
		this._showLoading();
		this._abortRepoRequest();

		// Reset previous selection when switching repositories.
		this.selectedItems = [];

		if (typeof window.clearPowerSelectionTable === 'function') {
			window.clearPowerSelectionTable();
		}

		this.#repoRequestController = new AbortController();

		const url = `${UrlAjax}getRepoIndex&repo=${encodeURIComponent(repo)}&area=${encodeURIComponent(area)}`;

		try {
			const response = await fetch(url, {
				method: 'GET',
				signal: this.#repoRequestController.signal,
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json'
				}
			});

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const data = await response.json();

			if (!data?.success || !data?.index || !this.#buildTable) {
				this._notify(
					data?.message || translate('COM_COMPONENTBUILDER_FAILED_TO_RETRIEVE_REPOSITORY_INDEX'),
					'danger'
				);
				return;
			}

			const repoData = Array.isArray(data.index) ? data.index[0] : data.index;

			if (!repoData || typeof repoData !== 'object') {
				this._notify(
					translate('COM_COMPONENTBUILDER_FAILED_TO_RETRIEVE_REPOSITORY_INDEX'),
					'danger'
				);
				return;
			}

			const {
				path = 'joomla/super-powers',
				read_branch = 'master',
				target = '',
				base = 'https://git.vdm.dev',
				guid = null,
				index = []
			} = repoData;

			const repoBase = target === 'github' ? 'https://github.com' : base;
			const repoPath = target === 'github' ? 'tree' : 'src/branch';

			window.targetPowerRepoUrl = `${repoBase}/${path}/${repoPath}/${read_branch}/`;

			// The index may be an array or an object keyed by GUID.
			this.#buildTable(index);

			this.currentRepo = guid;
			this.currentArea = area;

			await this._transitionTo(this.#repoArea, this.#powersArea);

			if (this.#drawTable) {
				this.#drawTable();
			}
		} catch (error) {
			if (error.name !== 'AbortError') {
				console.error('Fetch error:', error);
				this._notify(
					translate('COM_COMPONENTBUILDER_NETWORK_OR_SERVER_ERROR_OCCURRED_WHILE_FETCHING_INDEX'),
					'danger'
				);
			}
		} finally {
			this.#isLoadingRepo = false;
			this._hideLoading();
			this._setRepoButtonsDisabled(false);
			this._updateInitButtonState();
		}
	}

	/**
	 * Go back to repository selection.
	 *
	 * @returns {void}
	 */
	_handleBackToRepos() {
		if (this.#isLoadingRepo || this.#isInitializing) {
			return;
		}

		this._transitionTo(this.#powersArea, this.#repoArea)
			.then(() => {
				if (this.#drawTable) {
					this.#drawTable();
				}
			})
			.catch((error) => {
				console.error('Transition error:', error);
			});
	}

	/**
	 * Submit selected powers.
	 *
	 * @returns {Promise<void>}
	 */
	async _handleInitSelectedPowers() {
		if (this.#isInitializing || this.#isLoadingRepo) {
			return;
		}

		if (!Array.isArray(this.selectedItems) || this.selectedItems.length === 0) {
			this._notify(
				translate('COM_COMPONENTBUILDER_NO_ITEMS_SELECTED'),
				'warning'
			);
			return;
		}

		const area = this.currentArea;
		const repo = this.currentRepo;

		if (!area || !repo) {
			this._notify(
				translate('COM_COMPONENTBUILDER_MISSING_REPOSITORY_OR_AREA_DATA'),
				'danger'
			);
			return;
		}

		this.#isInitializing = true;
		this._updateInitButtonState();
		this._setRepoButtonsDisabled(true);
		this._showLoading();
		this._abortInitRequest();

		this.#initRequestController = new AbortController();

		try {
			const formData = new FormData();

			for (const item of this.selectedItems) {
				if (item?.guid) {
					formData.append('selected[]', item.guid);
				}
			}

			formData.append('area', area);
			formData.append('repo', repo);

			const response = await fetch(`${UrlAjax}initSelectedPackages`, {
				method: 'POST',
				body: formData,
				signal: this.#initRequestController.signal,
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json'
				}
			});

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const data = await response.json();

			if (!data?.success) {
				this._notify(
					data?.message || translate('COM_COMPONENTBUILDER_FAILED_TO_INITIALIZE_SELECTED_POWERS'),
					'danger'
				);
				return;
			}

			// Process result log before clearing selection,
			// since _getNamesFromGuids reads from selectedItems.
			this._handleResultLog(data.result_log || {});
			this.selectedItems = [];

			await this._transitionTo(this.#powersArea, this.#repoArea);
		} catch (error) {
			if (error.name !== 'AbortError') {
				console.error('Submission error:', error);
				this._notify(
					translate('COM_COMPONENTBUILDER_ERROR_OCCURRED_WHILE_INITIALIZING_POWERS'),
					'danger'
				);
			}
		} finally {
			this.#isInitializing = false;
			this._hideLoading();
			this._setRepoButtonsDisabled(false);
			this._updateInitButtonState();
		}
	}

	/**
	 * Process init result log.
	 *
	 * @param   {Object}  resultLog  The result log object.
	 *
	 * @returns {void}
	 */
	_handleResultLog(resultLog) {
		const localGuids = this._normalizeGuids(resultLog.local);
		const notFoundGuids = this._normalizeGuids(resultLog.not_found);
		const addedGuids = this._normalizeGuids(resultLog.added);

		if (localGuids.length > 0) {
			this._notifyList(
				translate('COM_COMPONENTBUILDER_THESE_ITEMS_WERE_ALREADY_PRESENT_LOCALLY_AND_WERE_NOT_INITIALIZED'),
				this._getNamesFromGuids(localGuids),
				'info'
			);
		}

		if (notFoundGuids.length > 0) {
			this._notifyList(
				translate('COM_COMPONENTBUILDER_THESE_ITEMS_COULD_NOT_BE_FOUND_IN_THE_REMOTE_REPOSITORY_AND_WERE_NOT_INITIALIZED'),
				this._getNamesFromGuids(notFoundGuids),
				'warning'
			);
		}

		if (addedGuids.length > 0) {
			this._notifyList(
				translate('COM_COMPONENTBUILDER_THESE_ITEMS_WERE_SUCCESSFULLY_INITIALIZED'),
				this._getNamesFromGuids(addedGuids),
				'success'
			);
		}
	}

	/**
	 * Normalize log values to GUID arrays.
	 *
	 * @param   {*}  value  The raw value.
	 *
	 * @returns {Array<string>} The GUID array.
	 */
	_normalizeGuids(value) {
		if (!value) {
			return [];
		}

		if (Array.isArray(value)) {
			return value.filter((item) => typeof item === 'string' && item !== '');
		}

		if (isPlainObject(value)) {
			return Object.keys(value);
		}

		return [];
	}

	/**
	 * Resolve item names from GUIDs.
	 *
	 * @param   {Array<string>}  guids  The GUIDs.
	 *
	 * @returns {Array<string>} The item names.
	 */
	_getNamesFromGuids(guids) {
		const names = [];

		for (const guid of guids) {
			const item = this.selectedItems.find((entry) => entry?.guid === guid);

			if (typeof item?.name === 'string' && item.name !== '') {
				names.push(item.name);
			}
		}

		return names;
	}

	/**
	 * Abort any running repository request.
	 *
	 * @returns {void}
	 */
	_abortRepoRequest() {
		if (this.#repoRequestController) {
			this.#repoRequestController.abort();
			this.#repoRequestController = null;
		}
	}

	/**
	 * Abort any running init request.
	 *
	 * @returns {void}
	 */
	_abortInitRequest() {
		if (this.#initRequestController) {
			this.#initRequestController.abort();
			this.#initRequestController = null;
		}
	}

	/**
	 * Transition between two elements.
	 *
	 * @param   {HTMLElement|null}  hideEl  The element to hide.
	 * @param   {HTMLElement|null}  showEl  The element to show.
	 *
	 * @returns {Promise<void>} Resolves after transition completes.
	 */
	_transitionTo(hideEl, showEl) {
		if (!hideEl || !showEl || hideEl === showEl) {
			return Promise.resolve();
		}

		const duration = 250;

		return new Promise((resolve) => {
			hideEl.style.transition = `opacity ${duration}ms ease`;
			showEl.style.transition = `opacity ${duration}ms ease`;

			hideEl.style.opacity = '1';
			showEl.style.display = 'none';
			showEl.style.opacity = '0';

			requestAnimationFrame(() => {
				hideEl.style.opacity = '0';

				window.setTimeout(() => {
					hideEl.style.display = 'none';
					showEl.style.display = '';
					showEl.style.opacity = '0';

					requestAnimationFrame(() => {
						showEl.style.opacity = '1';

						window.setTimeout(() => {
							hideEl.style.transition = '';
							hideEl.style.opacity = '';
							showEl.style.transition = '';
							showEl.style.opacity = '';
							resolve();
						}, duration);
					});
				}, duration);
			});
		});
	}

	/**
	 * Show the loading overlay.
	 *
	 * @returns {void}
	 */
	_showLoading() {
		if (this.#loadingDiv) {
			this.#loadingDiv.style.display = 'block';
		}
	}

	/**
	 * Hide the loading overlay.
	 *
	 * @returns {void}
	 */
	_hideLoading() {
		if (this.#loadingDiv) {
			this.#loadingDiv.style.display = 'none';
		}
	}

	/**
	 * Notify with a simple message.
	 *
	 * @param   {string}  message  The message.
	 * @param   {string}  type     The alert type.
	 *
	 * @returns {void}
	 */
	_notify(message, type = 'info') {
		if (typeof message !== 'string' || message.trim() === '') {
			return;
		}

		const body = document.createElement('div');
		body.textContent = message;

		this._renderAlert(body, type);
	}

	/**
	 * Notify with a heading and a list.
	 *
	 * @param   {string}        heading  The heading text.
	 * @param   {Array<string>} items    The item names.
	 * @param   {string}        type     The alert type.
	 *
	 * @returns {void}
	 */
	_notifyList(heading, items, type = 'info') {
		if (!heading && (!Array.isArray(items) || items.length === 0)) {
			return;
		}

		const fragment = document.createDocumentFragment();

		if (heading) {
			const headingNode = document.createElement('div');
			headingNode.textContent = heading;
			fragment.appendChild(headingNode);
		}

		if (Array.isArray(items) && items.length > 0) {
			const list = document.createElement('ul');
			list.className = 'mb-0 mt-2';

			for (const item of items) {
				const li = document.createElement('li');
				li.textContent = item;
				list.appendChild(li);
			}

			fragment.appendChild(list);
		}

		const wrapper = document.createElement('div');
		wrapper.appendChild(fragment);

		this._renderAlert(wrapper, type);
	}

	/**
	 * Render a Bootstrap alert.
	 *
	 * @param   {Node}    contentNode  The content node.
	 * @param   {string}  type         The alert type.
	 *
	 * @returns {void}
	 */
	_renderAlert(contentNode, type = 'info') {
		const allowedTypes = ['primary', 'info', 'success', 'warning', 'danger'];
		const alertType = allowedTypes.includes(type) ? type : 'primary';

		let container = document.getElementById('alert-container');

		if (!container) {
			container = document.createElement('div');
			container.id = 'alert-container';
			container.className = 'position-fixed top-0 start-50 translate-middle-x p-3';
			container.style.zIndex = '1060';
			container.style.maxWidth = 'min(92vw, 720px)';
			container.style.width = '100%';
			document.body.appendChild(container);
		}

		const alert = document.createElement('div');
		alert.className = `alert alert-${alertType} alert-dismissible fade show shadow`;
		alert.setAttribute('role', 'alert');

		const body = document.createElement('div');
		body.appendChild(contentNode);

		const closeButton = document.createElement('button');
		closeButton.type = 'button';
		closeButton.className = 'btn-close';
		closeButton.setAttribute('data-bs-dismiss', 'alert');
		closeButton.setAttribute('aria-label', 'Close');

		alert.appendChild(body);
		alert.appendChild(closeButton);
		container.appendChild(alert);

		if (
			typeof bootstrap !== 'undefined'
			&& bootstrap.Alert
			&& typeof bootstrap.Alert.getOrCreateInstance === 'function'
		) {
			const instance = bootstrap.Alert.getOrCreateInstance(alert);

			window.setTimeout(() => {
				try {
					instance.close();
				} catch (error) {
					if (alert.parentNode) {
						alert.remove();
					}
				}
			}, 5000);

			return;
		}

		window.setTimeout(() => {
			alert.remove();
		}, 5000);
	}
}