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
/**
 * Floating notification popup helper.
 *
 * These remain non-Joomla-dialog notifications so search results/errors
 * can stack without interrupting the workflow.
 *
 * @param {string} message
 * @param {string} type
 * @param {number} timeout
 *
 * @returns {void}
 */
const showAppNotification = (message, type = 'info', timeout = 3000) => {
	let notificationArea = document.getElementById('search-notification-area');

	if (!notificationArea) {
		notificationArea = document.createElement('div');
		notificationArea.id = 'search-notification-area';
		notificationArea.className = 'search-notification-area';
		notificationArea.setAttribute('aria-live', 'polite');
		notificationArea.setAttribute('aria-atomic', 'true');
		document.body.appendChild(notificationArea);
	}

	const alertTypeMap = {
		success: 'success',
		danger: 'danger',
		error: 'danger',
		warning: 'warning',
		info: 'info'
	};

	const alertClass = alertTypeMap[type] || 'info';

	const wrapper = document.createElement('div');
	wrapper.className = `alert alert-${alertClass} alert-dismissible fade show search-toast`;
	wrapper.setAttribute('role', 'alert');
	wrapper.innerHTML = `
		<div class="search-toast-message">${message}</div>
		<button type="button" class="btn-close" aria-label="Close"></button>
	`;

	const closeButton = wrapper.querySelector('.btn-close');

	if (closeButton) {
		closeButton.addEventListener('click', () => {
			if (wrapper.parentNode) {
				wrapper.remove();
			}
		});
	}

	notificationArea.appendChild(wrapper);

	if (timeout > 0) {
		window.setTimeout(() => {
			if (wrapper.parentNode) {
				wrapper.remove();
			}
		}, timeout);
	}
};

/**
 * Joomla native confirmation dialog.
 *
 * Used only for actual confirmations, not for search notifications.
 *
 * @param {string} message
 * @param {Function} onConfirm
 * @param {Function|null} onCancel
 * @param {Object} options
 *
 * @returns {void}
 */
const showAppConfirm = (message, onConfirm, onCancel = null) => {
	try {
		const dialog = customElements.get('joomla-dialog');

		if (!dialog) {
			throw new Error('joomla-dialog not available');
		}

		dialog.confirm(message).then(function(result) {
			if (result) {
				if (typeof onConfirm === 'function') {
					onConfirm();
				}
			} else {
				if (typeof onCancel === 'function') {
					onCancel();
				}
			}
		});
	} catch (error) {
		// fallback
		if (window.confirm(message.replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]+>/g, ''))) {
			if (typeof onConfirm === 'function') {
				onConfirm();
			}
		} else if (typeof onCancel === 'function') {
			onCancel();
		}
	}
};

/**
 * JS Function to execute the search
 */
const doSearch = async (signal, tables) => {
	try {
		// build form
		const formData = new FormData();
		
		// load the result table
		const resultsTable = new DataTable('#search_results_table');

		// get the search mode
		let typeSearch = modeObject.querySelector('input[type=\'radio\']:checked').value;
		
		// set some search values
		let searchValue = searchObject.value;
		let replaceValue = replaceObject.value;
		let matchValue = matchObject.checked ? 1 : 0;
		let wholeValue = wholeObject.checked ? 1 : 0;
		let regexValue = regexObject.checked ? 1 : 0;

		// add the form data
		formData.append('table_name', '');
		formData.append('type_search', typeSearch);
		formData.append('search_value', searchValue);
		formData.append('replace_value', replaceValue);
		formData.append('match_case', matchValue);
		formData.append('whole_word', wholeValue);
		formData.append('regex_search', regexValue);

		// update the URL
		updateUrlQuery(searchValue, replaceValue, matchValue, wholeValue, regexValue, typeSearch);

		let abort_this_search_values = false;

		// reset the progress bar
		searchProgressBarObject.style.width = '0%';
		searchProgressBarObject.innerHTML = '0%';
		searchProgressBarObject.setAttribute('aria-valuenow', '0');

		// show the progress bar
		searchProgressObject.style.display = '';

		// hidde the search button
		startSearchButton.style.display = 'none';

		// show the stop search button
		stopSearchButton.style.display = '';

		// start search timer
		startSearchTimer();

		// reset our global counters
		fieldCount = 0;
		lineCount = 0;

		// set our local counters
		let total = 0;
		let progress = tables.length;
		let index;

		for (index = 0; index < progress; index++) {
			let tableName = tables[index];

			// add the table name
			formData.set('table_name', tableName);

			let options = {
				signal: signal,
				method: 'POST', // *GET, POST, PUT, DELETE, etc.
				body: formData
			};

			if (abort_this_search_values) {
				break;
			}

			await fetch(UrlAjax + 'doSearch', options)
				.then(response => {
					total++;

					if (response.ok) {
						return response.json();
					}

					showAppNotification(
						Joomla.Text._('COM_COMPONENTBUILDER_THE_SEARCH_PROCESS_HAD_AN_ERROR_WITH_TABLE') + ' ' + tableName,
						'danger',
						4000
					);

					return null;
				})
				.then((data) => {
					if (!data) {
						return;
					}

					if (typeof data.success !== 'undefined') {
						showAppNotification(data.success, 'success', 1800);
					}

					if (typeof data.items !== 'undefined') {
						addTableItems(resultsTable, data.items, typeSearch);
					}

					if (typeof data.fields_count !== 'undefined') {
						fieldCount += data.fields_count;
					}

					if (typeof data.line_count !== 'undefined') {
						lineCount += data.line_count;
					}

					let percent = 100.0 * (total / progress);

					searchProgressObject.style.display = '';
					searchProgressBarObject.style.width = percent.toFixed(2) + '%';
					searchProgressBarObject.innerHTML = percent.toFixed(2) + '%';
					searchProgressBarObject.setAttribute('aria-valuenow', percent.toFixed(2));

					if (progress === total) {
						searchProgressBarObject.innerHTML = '100%';
						searchProgressBarObject.style.width = '100%';
						searchProgressBarObject.setAttribute('aria-valuenow', '100');

						startSearchButton.style.display = '';
						stopSearchButton.style.display = 'none';

						setTimeout(function () {
							searchProgressObject.style.display = 'none';
						}, 13000);
					}
				})
				.catch(error => {
					console.log(error);

					if (error.name === 'AbortError') {
						abort_this_search_values = true;
					}
				});
		}
	} catch (error) {
		console.log(error);
	} finally {
		// Executed regardless if we caught the error
	}
};

/**
 * JS Function to start search timer
 */
const startSearchTimer = () => {
	startSearchTime = new Date();
};

/**
 * JS Function to get search lenght
 */
const getSearchLenght = () => {
	// set ending time
	endSearchTime = new Date();

	// get diff in ms
	var timeDiff = endSearchTime - startSearchTime;

	// strip the ms
	timeDiff /= 1000;

	// get seconds
	return Math.round(timeDiff);
};

/**
 * JS Function to fetch selected item
 */
const getSelectedItem = async (table, row, field, line) => {
	try {
		// get the search mode
		let mode = modeObject.querySelector('input[type=\'radio\']:checked').value;

		// build form
		const formData = new FormData();

		formData.append('field_name', field);
		formData.append('row_id', row);
		formData.append('table_name', table);
		formData.append('search_value', searchObject.value);
		formData.append('replace_value', replaceObject.value);
		formData.append('match_case', matchObject.checked ? 1 : 0);
		formData.append('whole_word', wholeObject.checked ? 1 : 0);
		formData.append('regex_search', regexObject.checked ? 1 : 0);

		// get search value
		let postURL = '';

		if (mode == 2) {
			// add the line value
			formData.append('line_nr', line);
			// calling URL
			postURL = UrlAjax + 'getReplaceValue';
		} else {
			// calling URL
			postURL = UrlAjax + 'getSearchValue';
		}

		let options = {
			method: 'POST', // *GET, POST, PUT, DELETE, etc.
			body: formData
		};

		await fetch(postURL, options)
			.then(response => {
				if (response.ok) {
					return response.json();
				}
				return null;
			})
			.then((data) => {
				if (!data) {
					return;
				}

				if (typeof data.success !== 'undefined') {
					showAppNotification(data.success, 'success', 1500);
				}

				if (typeof data.value !== 'undefined') {
					addSelectedItem(data.value, table, row, field, line);
				}
			})
			.catch(error => {
				console.log(error);
			});
	} catch (error) {
		console.log(error);
	} finally {
		// Executed regardless if we caught the error
	}
};

/**
 * JS Function to check if we should save/update the all current found items
 */
const replaceAllCheck = () => {
	// get the current searc and replace values
	let searchValue = searchObject.value;
	let replaceValue = replaceObject.value;

	// load question
	let question = Joomla.Text._('COM_COMPONENTBUILDER_YOUR_ARE_ABOUT_TO_UPDATE_BALLB_VALUES_THAT_CAN_BE_FOUND_IN_THE_DATABASE') + '<br />' +
		Joomla.Text._('COM_COMPONENTBUILDER_YOU_WILL_REPLACE') + ': [<span class="found_code">' + htmlentities(searchValue) + '</span>] ' +
		Joomla.Text._('COM_COMPONENTBUILDER_WITH') + ': [<span class="found_code">' + htmlentities(replaceValue) + '</span>]<br />' +
		Joomla.Text._('COM_COMPONENTBUILDER_THIS_CAN_NOT_BE_UNDONE_BYOU_HAVE_BEEN_WARNEDB') + '<br /><br />' +
		Joomla.Text._('COM_COMPONENTBUILDER_ARE_YOU_THEREFORE_ABSOLUTELY_SURE_YOU_WANT_TO_CONTINUE');

	// do check
	showAppConfirm(
		question,
		function () {
			// show the search settings again
			showSearch();
			// Create new controller and issue new request
			controller_replace = new AbortController();

			let tables = [];
			let table = tableObject.value;

			if (table != -1) {
				tables.push(table);
				replaceAll(controller_replace.signal, tables);
			} else {
				replaceAll(controller_replace.signal, searchTables);
			}
		},
		null,
		{
			labels: {
				Ok: Joomla.Text._('COM_COMPONENTBUILDER_YES_UPDATE_ALL'),
				Cancel: Joomla.Text._('COM_COMPONENTBUILDER_NO')
			}
		}
	);
};

/**
 * JS Function to execute the replace all
 */
const replaceAll = async (signal, tables) => {
	try {
		// build form
		const formData = new FormData();

		// get the search mode
		let typeSearch = modeObject.querySelector('input[type=\'radio\']:checked').value;

		// set some search values
		let searchValue = searchObject.value;
		let replaceValue = replaceObject.value;
		let matchValue = matchObject.checked ? 1 : 0;
		let wholeValue = wholeObject.checked ? 1 : 0;
		let regexValue = regexObject.checked ? 1 : 0;

		// add the form data
		formData.append('table_name', '');
		formData.append('type_search', typeSearch);
		formData.append('search_value', searchValue);
		formData.append('replace_value', replaceValue);
		formData.append('match_case', matchValue);
		formData.append('whole_word', wholeValue);
		formData.append('regex_search', regexValue);

		// reset the progress bar
		replaceProgressBarObject.style.width = '0%';

		// show the progress bar
		replaceProgressBarObject.innerHTML = '';
		replaceProgressBarObject.setAttribute('aria-valuenow', '0');
		replaceProgressObject.style.display = '';

		let abort_this_replace_values = false;
		let total = 0;
		let progress = tables.length;
		let index;

		for (index = 0; index < progress; index++) {
			let tableName = tables[index];

			// add the table name
			formData.set('table_name', tableName);

			let options = {
				signal: signal,
				method: 'POST', // *GET, POST, PUT, DELETE, etc.
				body: formData
			};

			if (abort_this_replace_values) {
				break;
			}

			await fetch(UrlAjax + 'replaceAll', options)
				.then(response => {
					total++;

					if (response.ok) {
						return response.json();
					}

					showAppNotification(
						Joomla.Text._('COM_COMPONENTBUILDER_THE_REPLACE_PROCESS_HAD_AN_ERROR_WITH_TABLE') + ' ' + tableName,
						'danger',
						4000
					);

					return null;
				})
				.then((data) => {
					if (!data) {
						return;
					}

					if (typeof data.success !== 'undefined') {
						// silent during bulk replace
					} else if (typeof data.error !== 'undefined') {
						showAppNotification(data.error, 'warning', 2500);
					}

					let percent = 100.0 * (total / progress);
					replaceProgressBarObject.style.width = percent.toFixed(2) + '%';
					replaceProgressBarObject.innerHTML = percent.toFixed(2) + '%';
					replaceProgressBarObject.setAttribute('aria-valuenow', percent.toFixed(2));

					if (progress === total) {
						setTimeout(function () {
							replaceProgressObject.style.display = 'none';
							clearAll();

							if (regexValue == 0) {
								showAppConfirm(
									Joomla.Text._('COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_DO_A_REVERSE_SEARCH'),
									function () {
										startNewSearch(replaceValue, searchValue, matchValue, wholeValue, regexValue, 2);
									},
									function () {
										showAppConfirm(
											Joomla.Text._('COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_REPEAT_THE_SAME_SEARCH'),
											function () {
												startSearch();
											},
											function () {
												clearSearch();
											},
											{
												labels: {
													Ok: Joomla.Text._('COM_COMPONENTBUILDER_YES'),
													Cancel: Joomla.Text._('COM_COMPONENTBUILDER_NO')
												}
											}
										);
									},
									{
										labels: {
											Ok: Joomla.Text._('COM_COMPONENTBUILDER_YES'),
											Cancel: Joomla.Text._('COM_COMPONENTBUILDER_NO')
										}
									}
								);
							} else {
								showAppConfirm(
									Joomla.Text._('COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_REPEAT_THE_SAME_SEARCH'),
									function () {
										startSearch();
									},
									function () {
										clearSearch();
									},
									{
										labels: {
											Ok: Joomla.Text._('COM_COMPONENTBUILDER_YES'),
											Cancel: Joomla.Text._('COM_COMPONENTBUILDER_NO')
										}
									}
								);
							}
						}, 3000);
					}
				})
				.catch(error => {
					console.log(error);

					if (error.name === 'AbortError') {
						abort_this_replace_values = true;
					}
				});
		}
	} catch (error) {
		console.log(error);
	} finally {
		// Executed regardless if we caught the error
	}
};

/**
 * JS Function to check if we should save/update the current selected item
 */
const setValueCheck = (row, field, table) => {
	// load question
	let question = Joomla.Text._('COM_COMPONENTBUILDER_YOUR_ARE_ABOUT_TO_UPDATE_ROW') + ' (' + row + ') -> (' + field + ') ' +
		Joomla.Text._('COM_COMPONENTBUILDER_FIELD_IN_THE') + ' (' + table + ') ' + Joomla.Text._('COM_COMPONENTBUILDER_TABLE') + '.<br /><br />' +
		Joomla.Text._('COM_COMPONENTBUILDER_THIS_CAN_NOT_BE_UNDONE_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE');

	// do check
	showAppConfirm(
		question,
		function () {
			setValue(row, field, table);
		},
		null,
		{
			labels: {
				Ok: Joomla.Text._('COM_COMPONENTBUILDER_YES'),
				Cancel: Joomla.Text._('COM_COMPONENTBUILDER_NO')
			}
		}
	);
};

/**
 * JS Function to set the current selected item
 */
const setValue = async (row, field, table) => {
	try {
		// get the value from the editor
		const editor = await GetEditor();
		let value = editor ? editor.getValue() : '';

		// build form
		const formData = new FormData();
		formData.append('value', value);
		formData.append('row_id', row);
		formData.append('field_name', field);
		formData.append('table_name', table);

		let options = {
			method: 'POST', // *GET, POST, PUT, DELETE, etc.
			body: formData
		};

		await fetch(UrlAjax + 'setValue', options)
			.then(response => {
				if (response.ok) {
					return response.json();
				}
				return null;
			})
			.then((data) => {
				if (!data) {
					return;
				}

				if (typeof data.success !== 'undefined') {
					showAppNotification(data.success, 'success', 2000);
					clearSelectedItem();
					tableActiveObject.remove().draw();
				}
			})
			.catch(error => {
				console.log(error);
			});
	} catch (error) {
		console.log(error);
	} finally {
		// Executed regardless if we caught the error
	}
};

/**
 * JS Function to add item to the editor
 */
const addSelectedItem = async (value, table, row, field, line) => {
	// display area
	if (value.length > 1) {
		// add value to editor
		const editor = await GetEditor();

		if (editor) {
			editor.setValue(value);
		}

		// set item details notice area
		itemNoticeObject.style.display = '';
		itemEditButtonObject.innerHTML = editButtonSelected;
		itemTableNameObject.innerHTML = table;
		itemRowIdObject.innerHTML = row;
		itemFieldNameObject.innerHTML = field;
		itemLineNumberObject.innerHTML = line;

		// set button and editor line if we have a line number
		if (typeof line == 'number') {
			// show and set the save button
			buttonUpdateItemObject.style.display = '';
			buttonUpdateItemObject.setAttribute('onclick', "setValueCheck(" + row + ", '" + field + "', '" + table + "');");
			// Get line info from current state.
			const selectedEditor = await GetEditor();

			if (selectedEditor) {
				const lineInfo = selectedEditor.instance.state.doc.line(line);
				selectedEditor.instance.dispatch({
					selection: { head: lineInfo.from, anchor: lineInfo.to },
					scrollIntoView: true
				});
			}
		} else {
			// no line so no data we can't save this data
			buttonUpdateItemObject.setAttribute('onclick', '');
			buttonUpdateItemObject.style.display = 'none';
		}
	}
};

/**
 * JS Function to clear item from the editor and hide it
 */
const clearSelectedItem = async () => {
	// display area
	const editor = await GetEditor();

	if (editor) {
		editor.setValue('');
	}
	// clear notice area
	itemNoticeObject.style.display = 'none';
	itemEditButtonObject.innerHTML = '...';
	itemTableNameObject.innerHTML = '...';
	itemRowIdObject.innerHTML = '...';
	itemFieldNameObject.innerHTML = '...';
	itemLineNumberObject.innerHTML = '...';
	// clear update button
	buttonUpdateItemObject.setAttribute('onclick', '');
};

/**
 * JS Function to clear table items
 */
const clearTableItems = async () => {
	let table = new DataTable('#search_results_table');
	// clear search
	table.search('').columns().search('');
	// clear items
	table.clear().draw(true);
	// hide the update all items
	buttonUpdateAllStyleDisplay('none');
};

/**
 * JS Function to clear all details of the search
 */
const clearAll = async () => {
	// clear all details
	clearTableItems();
	clearSelectedItem();
	searchedObject.innerHTML = '....';
};

/**
 * JS Function to clear the search and replace values
 */
const clearSearch = async () => {
	// clear the search and replace values
	searchObject.value = '';
	replaceObject.value = '';
};

/**
 * JS Function to set the search and replace values
 */
const startNewSearch = async (search, replace = '', match = 0, whole = 0, regex = 0, mode = 1) => {
	// redirect to a new search
	window.location.href = getSearchURL(search, replace, match, whole, regex, mode);
};

/**
 * JS Function to update the URL of the browser with the search query
 */
const updateUrlQuery = async (search, replace = '', match = 0, whole = 0, regex = 0, mode = 1) => {
	// update the url query
	window.history.pushState({}, '', getSearchURL(search, replace, match, whole, regex, mode));
};

/**
 * JS Function to get the current search URL
 */
const getSearchURL = (search, replace = '', match = 0, whole = 0, regex = 0, mode = 1) => {
	// check if its a single table search
	let table = tableObject.value;
	let table_name = '';

	if (table != -1) {
		table_name = '&table_name=' + urlencode(table);
	}

	// update the type of search
	if (mode == 1) {
		return UrlSearch + table_name +
			'&search_value=' + urlencode(search) +
			'&type_search=1&match_case=' + match +
			'&whole_word=' + whole +
			'&regex_search=' + regex;
	} else if (mode == 2) {
		return UrlSearch + table_name +
			'&search_value=' + urlencode(search) +
			'&replace_value=' + urlencode(replace) +
			'&type_search=2&match_case=' + match +
			'&whole_word=' + whole +
			'&regex_search=' + regex;
	}

	return UrlSearch + table_name;
};

/**
 * JS Function to check if a element has a class
 */
const hasClass = (elementObject, classNaam) => {
	return !!elementObject.className.match(new RegExp('(\\s|^)' + classNaam + '(\\s|$)'));
};

/**
 * JS Function add a class from an element
 */
const addClass = (elementObject, classNaam) => {
	if (!hasClass(elementObject, classNaam)) {
		elementObject.className += ' ' + classNaam;
	}
};

/**
 * JS Function remove a class from an element
 */
const removeClass = (elementObject, classNaam) => {
	if (hasClass(elementObject, classNaam)) {
		var reg = new RegExp('(\\s|^)' + classNaam + '(\\s|$)');
		elementObject.className = elementObject.className.replace(reg, ' ');
	}
};

/**
 * JS Function to add items to the table
 */
const addTableItems = async (table, items, typeSearch) => {
	table.rows.add(items).draw(false);

	if (typeSearch == 2) {
		buttonUpdateAllStyleDisplay(''); // TODO should only show once all items are loaded
	} else {
		buttonUpdateAllStyleDisplay('none'); // TODO should only show once all items are loaded
	}
};

/**
 * JS Function to update the update all button
 */
const buttonUpdateAllStyleDisplay = async (value) => {
	buttonUpdateAllObject.forEach((buttonObject) => {
		buttonObject.style.display = value;
	});
};

/**
 * JS Function to execute (A) on search/replace text change , (B) on search options changes
 */
const startSearch = (field, forced = false) => {
	// check mode
	let mode = modeObject.querySelector('input[type=\'radio\']:checked').value;

	if (mode == 0) {
		// reset the search area
		window.location.href = UrlSearch;
		return;
	}

	// check if we have an Enter click
	if (field && typeof field.code !== 'undefined' && field.code === 'Enter') {
		forced = true;
	}

	// get replace value if set
	const replaceValue = replaceObject.value;

	if (replaceValue.length > 0) {
		// set the searched value
		replacedObject.innerHTML = htmlentities(replaceValue);
	} else {
		replacedObject.innerHTML = '';
	}

	// get search value
	const searchValue = searchObject.value;

	if (searchValue.length > 2 || (searchValue.length > 0 && forced)) {
		// Cancel any ongoing requests
		if (controller) {
			controller.abort();
		}

		// we clear the table again
		clearAll();

		// set the searched value
		searchedObject.innerHTML = htmlentities(searchValue);

		// Create new controller and issue new request
		controller = new AbortController();

		// check if any specific table was set
		let tables = [];
		let table = tableObject.value;

		if (table != -1) {
			tables.push(table);
			doSearch(controller.signal, tables);
		} else {
			doSearch(controller.signal, searchTables);
		}
	} else {
		// Clear the table
		clearAll();
	}
};

/**
 * JS Function to stop a search
 */
const stopSearch = () => {
	// Cancel any ongoing requests
	if (controller) {
		controller.abort();
	}
	// show the search button
	startSearchButton.style.display = '';
	// hidde the stop search button
	stopSearchButton.style.display = 'none';

	// remove the progress bar at some point
	setTimeout(function () {
		// hide the progress bar again
		searchProgressObject.style.display = 'none';
	}, 13000);
};

/**
 * JS Function to hide search settings and show table search
 */
const showSearch = () => {
	searchSettingsObject.style.display = '';
	searchDetailsObject.style.display = 'none';
	replaceDetailsObject.style.display = 'none';

	if (tableSearchObject) {
		tableSearchObject.style.display = 'none';
	}

	if (tableLengthObject) {
		tableLengthObject.style.display = 'none';
	}
};

/**
 * JS Function to show search settings and hide table search
 */
const hideSearch = () => {
	searchSettingsObject.style.display = 'none';
	searchDetailsObject.style.display = '';

	if (tableSearchObject) {
		tableSearchObject.style.display = '';
	}

	if (tableLengthObject) {
		tableLengthObject.style.display = '';
	}

	// check if we are in replace mode
	let mode = modeObject.querySelector('input[type=\'radio\']:checked').value;

	if (mode == 2) {
		replaceDetailsObject.style.display = '';
	}
};


function htmlentities(string, quoteStyle, charset, doubleEncode) {
  //  discuss at: https://locutus.io/php/htmlentities/
  // original by: Kevin van Zonneveld (https://kvz.io)
  //  revised by: Kevin van Zonneveld (https://kvz.io)
  //  revised by: Kevin van Zonneveld (https://kvz.io)
  // improved by: nobbler
  // improved by: Jack
  // improved by: Rafał Kukawski (https://blog.kukawski.pl)
  // improved by: Dj (https://locutus.io/php/htmlentities:425#comment_134018)
  // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  //    input by: Ratheous
  //      note 1: function is compatible with PHP 5.2 and older
  //   example 1: htmlentities('Kevin & van Zonneveld')
  //   returns 1: 'Kevin &amp; van Zonneveld'
  //   example 2: htmlentities("foo'bar","ENT_QUOTES")
  //   returns 2: 'foo&#039;bar'
  const hashMap = getHtmlTranslationTable('HTML_ENTITIES', quoteStyle)
  string = string === null ? '' : string + ''
  if (!hashMap) {
    return false
  }
  if (quoteStyle && quoteStyle === 'ENT_QUOTES') {
    hashMap["'"] = '&#039;'
  }
  doubleEncode = doubleEncode === null || !!doubleEncode
  const regex = new RegExp('&(?:#\\d+|#x[\\da-f]+|[a-zA-Z][\\da-z]*);|[' +
    Object.keys(hashMap)
      .join('')
    // replace regexp special chars
      .replace(/([()[\]{}\-.*+?^$|/\\])/g, '\\$1') + ']',
  'g')
  return string.replace(regex, function (ent) {
    if (ent.length > 1) {
      return doubleEncode ? hashMap['&'] + ent.substr(1) : ent
    }
    return hashMap[ent]
  })
}

function getHtmlTranslationTable(table, quoteStyle) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/get_html_translation_table/
  // original by: Philip Peterson
  //  revised by: Kevin van Zonneveld (https://kvz.io)
  // bugfixed by: noname
  // bugfixed by: Alex
  // bugfixed by: Marco
  // bugfixed by: madipta
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  // bugfixed by: T.Wild
  // improved by: KELAN
  // improved by: Brett Zamir (https://brett-zamir.me)
  //    input by: Frank Forte
  //    input by: Ratheous
  //      note 1: It has been decided that we're not going to add global
  //      note 1: dependencies to Locutus, meaning the constants are not
  //      note 1: real constants, but strings instead. Integers are also supported if someone
  //      note 1: chooses to create the constants themselves.
  //   example 1: get_html_translation_table('HTML_SPECIALCHARS')
  //   returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

  const entities = {}
  const hashMap = {}
  let decimal
  const constMappingTable = {}
  const constMappingQuoteStyle = {}
  let useTable = {}
  let useQuoteStyle = {}

  // Translate arguments
  constMappingTable[0] = 'HTML_SPECIALCHARS'
  constMappingTable[1] = 'HTML_ENTITIES'
  constMappingQuoteStyle[0] = 'ENT_NOQUOTES'
  constMappingQuoteStyle[2] = 'ENT_COMPAT'
  constMappingQuoteStyle[3] = 'ENT_QUOTES'

  useTable = !isNaN(table)
    ? constMappingTable[table]
    : table
      ? table.toUpperCase()
      : 'HTML_SPECIALCHARS'

  useQuoteStyle = !isNaN(quoteStyle)
    ? constMappingQuoteStyle[quoteStyle]
    : quoteStyle
      ? quoteStyle.toUpperCase()
      : 'ENT_COMPAT'

  if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
    throw new Error('Table: ' + useTable + ' not supported')
  }

  entities['38'] = '&amp;'
  if (useTable === 'HTML_ENTITIES') {
    entities['160'] = '&nbsp;'
    entities['161'] = '&iexcl;'
    entities['162'] = '&cent;'
    entities['163'] = '&pound;'
    entities['164'] = '&curren;'
    entities['165'] = '&yen;'
    entities['166'] = '&brvbar;'
    entities['167'] = '&sect;'
    entities['168'] = '&uml;'
    entities['169'] = '&copy;'
    entities['170'] = '&ordf;'
    entities['171'] = '&laquo;'
    entities['172'] = '&not;'
    entities['173'] = '&shy;'
    entities['174'] = '&reg;'
    entities['175'] = '&macr;'
    entities['176'] = '&deg;'
    entities['177'] = '&plusmn;'
    entities['178'] = '&sup2;'
    entities['179'] = '&sup3;'
    entities['180'] = '&acute;'
    entities['181'] = '&micro;'
    entities['182'] = '&para;'
    entities['183'] = '&middot;'
    entities['184'] = '&cedil;'
    entities['185'] = '&sup1;'
    entities['186'] = '&ordm;'
    entities['187'] = '&raquo;'
    entities['188'] = '&frac14;'
    entities['189'] = '&frac12;'
    entities['190'] = '&frac34;'
    entities['191'] = '&iquest;'
    entities['192'] = '&Agrave;'
    entities['193'] = '&Aacute;'
    entities['194'] = '&Acirc;'
    entities['195'] = '&Atilde;'
    entities['196'] = '&Auml;'
    entities['197'] = '&Aring;'
    entities['198'] = '&AElig;'
    entities['199'] = '&Ccedil;'
    entities['200'] = '&Egrave;'
    entities['201'] = '&Eacute;'
    entities['202'] = '&Ecirc;'
    entities['203'] = '&Euml;'
    entities['204'] = '&Igrave;'
    entities['205'] = '&Iacute;'
    entities['206'] = '&Icirc;'
    entities['207'] = '&Iuml;'
    entities['208'] = '&ETH;'
    entities['209'] = '&Ntilde;'
    entities['210'] = '&Ograve;'
    entities['211'] = '&Oacute;'
    entities['212'] = '&Ocirc;'
    entities['213'] = '&Otilde;'
    entities['214'] = '&Ouml;'
    entities['215'] = '&times;'
    entities['216'] = '&Oslash;'
    entities['217'] = '&Ugrave;'
    entities['218'] = '&Uacute;'
    entities['219'] = '&Ucirc;'
    entities['220'] = '&Uuml;'
    entities['221'] = '&Yacute;'
    entities['222'] = '&THORN;'
    entities['223'] = '&szlig;'
    entities['224'] = '&agrave;'
    entities['225'] = '&aacute;'
    entities['226'] = '&acirc;'
    entities['227'] = '&atilde;'
    entities['228'] = '&auml;'
    entities['229'] = '&aring;'
    entities['230'] = '&aelig;'
    entities['231'] = '&ccedil;'
    entities['232'] = '&egrave;'
    entities['233'] = '&eacute;'
    entities['234'] = '&ecirc;'
    entities['235'] = '&euml;'
    entities['236'] = '&igrave;'
    entities['237'] = '&iacute;'
    entities['238'] = '&icirc;'
    entities['239'] = '&iuml;'
    entities['240'] = '&eth;'
    entities['241'] = '&ntilde;'
    entities['242'] = '&ograve;'
    entities['243'] = '&oacute;'
    entities['244'] = '&ocirc;'
    entities['245'] = '&otilde;'
    entities['246'] = '&ouml;'
    entities['247'] = '&divide;'
    entities['248'] = '&oslash;'
    entities['249'] = '&ugrave;'
    entities['250'] = '&uacute;'
    entities['251'] = '&ucirc;'
    entities['252'] = '&uuml;'
    entities['253'] = '&yacute;'
    entities['254'] = '&thorn;'
    entities['255'] = '&yuml;'
  }

  if (useQuoteStyle !== 'ENT_NOQUOTES') {
    entities['34'] = '&quot;'
  }
  if (useQuoteStyle === 'ENT_QUOTES') {
    entities['39'] = '&#39;'
  }
  entities['60'] = '&lt;'
  entities['62'] = '&gt;'

  // ascii decimals to real symbols
  for (decimal in entities) {
    if (entities.hasOwnProperty(decimal)) {
      hashMap[String.fromCharCode(decimal)] = entities[decimal]
    }
  }

  return hashMap
}


function urlencode (str) {
  //       discuss at: https://locutus.io/php/urlencode/
  //      original by: Philip Peterson
  //      improved by: Kevin van Zonneveld (https://kvz.io)
  //      improved by: Kevin van Zonneveld (https://kvz.io)
  //      improved by: Brett Zamir (https://brett-zamir.me)
  //      improved by: Lars Fischer
  //      improved by: Waldo Malqui Silva (https://fayr.us/waldo/)
  //         input by: AJ
  //         input by: travc
  //         input by: Brett Zamir (https://brett-zamir.me)
  //         input by: Ratheous
  //      bugfixed by: Kevin van Zonneveld (https://kvz.io)
  //      bugfixed by: Kevin van Zonneveld (https://kvz.io)
  //      bugfixed by: Joris
  // reimplemented by: Brett Zamir (https://brett-zamir.me)
  // reimplemented by: Brett Zamir (https://brett-zamir.me)
  //           note 1: This reflects PHP 5.3/6.0+ behavior
  //           note 1: Please be aware that this function
  //           note 1: expects to encode into UTF-8 encoded strings, as found on
  //           note 1: pages served as UTF-8
  //        example 1: urlencode('Kevin van Zonneveld!')
  //        returns 1: 'Kevin+van+Zonneveld%21'
  //        example 2: urlencode('https://kvz.io/')
  //        returns 2: 'https%3A%2F%2Fkvz.io%2F'
  //        example 3: urlencode('https://www.google.nl/search?q=Locutus&ie=utf-8')
  //        returns 3: 'https%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3DLocutus%26ie%3Dutf-8'
  str = (str + '')
  return encodeURIComponent(str)
    .replace(/!/g, '%21')
    .replace(/'/g, '%27')
    .replace(/\(/g, '%28')
    .replace(/\)/g, '%29')
    .replace(/\*/g, '%2A')
    .replace(/~/g, '%7E')
    .replace(/%20/g, '+')
}