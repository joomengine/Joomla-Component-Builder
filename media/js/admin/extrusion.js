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
 * The extrusion page: harvest a source, pair every candidate with what the
 * target component already has, and import only what was approved.
 *
 * Language note: every user-facing string comes through the text map the
 * template prints (window.JCBExtrusion.text), where each one is a natural
 * string inside Text::_() -- never a language constant. JCB manages those
 * strings itself when this code is imported, so none may be added to the
 * language files by hand.
 */
(function () {
	'use strict';

	// This file loads in the document head, before the template's inline
	// bootstrap defines window.JCBExtrusion -- so the bootstrap is read
	// lazily, once the page stands, never at evaluation time.
	let E = { url: '', canImport: false, text: {} };
	let T = E.text;

	/**
	 * The page state: the harvest payload, the current catalogue of the
	 * target component, the explicit decisions of the person, and the
	 * rows ticked for bulk work.
	 */
	const state = {
		busy: false,
		data: null,
		plan: null,
		reviewPending: true,
		catalogueRun: 0,
		catalogue: null,
		catalogueFailed: '',
		decisions: {},
		selected: new Set(),
		candidateIndex: new Map(),
		confirmation: null,
		modal: null,
		modalPowers: null,
		modalPowersTruncated: false,
		picker: null,
		// what each row would change, weighed under the decisions as they
		// stand; the diffs of the rows a person has open, and nothing else --
		// a closed diff is dropped; and the rows whose weight is being read
		// again because a decision just moved it
		changes: {},
		diffs: {},
		weighing: new Set(),
		weighingFailed: '',
		// which disclosures a person has open, so a re-draw leaves the board
		// exactly where they were reading it
		opened: new Set()
	};

	// the weighing that is on its way, if any: decisions made in a burst are
	// weighed once, and only the latest answer is allowed to land
	let weighingTimer = null;
	let weighingRun = 0;
	let powerSearchTimer = null;
	let powerSearchRun = 0;
	let batchingDecisions = false;

	const $ = (id) => document.getElementById(id);
	const $$ = (selector, root) => Array.from((root || document).querySelectorAll(selector));

	const esc = (value) => String(value ?? '').replace(/[&<>"']/g,
		(c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

	/**
	 * One POST to the ajax gateway.
	 */
	async function post(task, params) {
		const body = new FormData();
		Object.entries(params || {}).forEach(([key, value]) => body.append(key, value));
		let response;
		try {
			response = await fetch(E.url + task, { method: 'POST', body: body });
		} catch (error) {
			throw new Error(T.networkFailed);
		}
		const status = ' (HTTP ' + response.status + ')';
		const reference = response.headers.get('X-Request-ID') || '';
		const referenceText = /^[a-zA-Z0-9._:-]{1,100}$/.test(reference)
			? ' ' + T.failureReference + ' ' + reference : '';
		if (!response.ok) {
			// Never expose a proxy/server HTML response or its status text.
			throw new Error(T.httpFailed + status + referenceText);
		}
		let payload;
		try {
			payload = await response.json();
		} catch (error) {
			throw new Error(T.invalidResponse + status + referenceText);
		}
		if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
			throw new Error(T.invalidResponse + status + referenceText);
		}
		if (payload.error) {
			const failure = payload.failure || {};
			const safeReference = /^[a-f0-9]{16}$/.test(failure.reference || '')
				? ' ' + T.failureReference + ' ' + failure.reference : '';
			payload.error = (typeof payload.error === 'string' ? payload.error : T.operationFailed)
				+ safeReference;
		}
		return payload;
	}

	/** Lock the run controls until the single requested operation answers. */
	function beginRun() {
		state.busy = true;
		const controls = $$('#extrusion-tabs .nav-link, #extrusion-harvest-button, #extrusion-repair-namespaces-button, #extrusion-import-button');
		const disabled = controls.map((control) => control.disabled);
		controls.forEach((control) => { control.disabled = true; });
		return () => {
			state.busy = false;
			controls.forEach((control, index) => { control.disabled = disabled[index]; });
			renderReview();
		};
	}

	/**
	 * Show one pane and mirror it in the tab strip.
	 */
	function showPane(name) {
		$$('.extrusion-pane').forEach((pane) => {
			pane.style.display = pane.dataset.extrusionPane === name ? 'block' : 'none';
		});
		$$('#extrusion-tabs .nav-link').forEach((tab) => {
			tab.classList.toggle('active', tab.dataset.extrusionTab === name);
		});
	}

	function enableTab(name) {
		const tab = document.querySelector('#extrusion-tabs [data-extrusion-tab="' + name + '"]');
		if (tab) {
			tab.disabled = false;
		}
	}

	/** The dedicated repair action remains the same operation through review and apply. */
	function isNamespaceRepair() {
		return Number((state.config || {}).repair_namespaces) === 1;
	}

	/** Make the namespace-only operation visible without changing ordinary import labels. */
	function renderRunMode() {
		const repair = isNamespaceRepair();
		$('extrusion-pairing-title').textContent = repair ? T.repairPairingTitle : T.pairingTitle;
		$('extrusion-namespace-repair-notice').hidden = !repair;
		$('extrusion-import-label').textContent = repair ? T.repairLabel : T.importLabel;
		$('extrusion-results-title').textContent = repair ? T.repairReportTitle : T.reportTitle;
		$$('#extrusion-bulk-bar [data-extrusion-bulk="create"]').forEach((button) => {
			button.hidden = repair;
		});
	}

	/**
	 * Read the whole setup form into the run configuration.
	 */
	function readConfig() {
		const form = $('adminForm');
		const value = (name, fallback) => {
			const field = form.elements[name];
			return field ? field.value : fallback;
		};
		const radio = (name, fallback) => {
			const field = form.elements[name];
			if (!field) {
				return fallback;
			}
			return field.value !== undefined && field.value !== ''
				? field.value : fallback;
		};
		const componentField = value('component_id', '');
		const config = {
			admin_path: value('admin_path', '').trim(),
			site_path: value('site_path', '').trim(),
			libraries: value('libraries', '').split(/\r?\n/)
				.map((line) => line.trim()).filter((line) => line !== ''),
			component: componentField === '' ? 0 : parseInt(componentField, 10) || 0,
			detect: componentField === '',
			component_code: value('component_code', '').trim(),
			mode: radio('mode', 'create'),
			on_existing: radio('on_existing', 'update'),
			layout: value('layout', 'auto'),
			language_tag: value('language_tag', 'en-GB'),
			table_class: radio('table_class', 'auto'),
			dry_run: radio('dry_run', '0'),
			strict: radio('strict', '0'),
			depth: parseInt(value('depth', '12'), 10) || 12,
			max_files: parseInt(value('max_files', '20000'), 10) || 20000
		};
		['admin', 'site', 'site_views', 'tabs', 'conditions', 'language',
			'translations', 'relations', 'component_details'].forEach((scope) => {
			config['scope_' + scope] = radio('scope_' + scope, '1');
		});
		// radios render as a set, so read the checked one directly
		$$('#adminForm input[type="radio"]:checked').forEach((input) => {
			if (input.name in config || input.name.indexOf('scope_') === 0) {
				config[input.name] = input.value;
			}
		});
		return config;
	}

	/**
	 * Run the harvest and land on the pairing board.
	 */
	async function harvest(repairNamespaces = false) {
		if (state.busy) {
			return;
		}
		const notice = $('extrusion-setup-notice');
		notice.style.display = 'none';
		const config = readConfig();
		config.repair_namespaces = repairNamespaces ? 1 : 0;
		if (repairNamespaces && (config.mode !== 'update' || config.component <= 0)) {
			notice.textContent = T.repairNeedTarget;
			notice.style.display = 'block';
			return;
		}
		if (repairNamespaces && config.libraries.length === 0) {
			notice.textContent = T.repairNeedLibraries;
			notice.style.display = 'block';
			return;
		}
		if (repairNamespaces) {
			config.on_existing = 'update';
			config.detect = false;
		}
		if (config.admin_path === '' && config.site_path === ''
			&& config.libraries.length === 0) {
			notice.textContent = T.needSource;
			notice.style.display = 'block';
			return;
		}
		state.config = config;
		renderRunMode();
		window.clearTimeout(weighingTimer);
		invalidateReview();
		const finishRun = beginRun();
		$('extrusion-running-title').textContent = config.admin_path || T.theSource;
		$('extrusion-running-verb').textContent = T.harvesting;
		showPane('running');
		let payload;
		try {
			payload = await post('extrusionHarvest', { config: JSON.stringify(config) });
		} catch (error) {
			payload = { error: error.message || T.requestFailed };
		}
		// only an answer that says success is one: a missing or malformed
		// payload must land back on setup with a message, never on an empty
		// pairing board
		if (!payload || payload.error || !payload.success) {
			finishRun();
			showPane('setup');
			notice.textContent = (payload && payload.error) ? payload.error : T.harvestFailed;
			notice.style.display = 'block';
			return;
		}
		// the powers arrive raw from the harvest, so they take their board
		// identity here, once -- before anything renders or looks them up
		if (payload.powers && payload.powers.classes) {
			payload.powers.classes.forEach((candidate) => {
				candidate.kind = 'power';
				candidate.key = candidate.source_key;
				candidate.label = candidate.class;
				candidate.detail = candidate.fqn;
			});
		}
		state.config.source_component = payload.source_component ?? payload.component ?? 0;
		state.data = payload;
		state.decisions = {};
		state.selected.clear();
		state.changes = payload.changes || {};
		state.diffs = {};
		state.weighing.clear();
		state.weighingFailed = payload.weighing || '';
		window.clearTimeout(weighingTimer);
		weighingRun++;
		fillComponentSelect(payload);
		acceptReview(payload);
		await loadCatalogue(payload.component || 0);
		finishRun();
		enableTab('setup');
		enableTab('pairing');
		renderBoard();
		showPane('pairing');
	}

	/**
	 * Offer every published component as a pairing target.
	 */
	function fillComponentSelect(payload) {
		const select = $('extrusion-component-select');
		select.innerHTML = '';
		const none = document.createElement('option');
		none.value = '0';
		none.textContent = T.noTarget;
		none.disabled = isNamespaceRepair();
		select.appendChild(none);
		(payload.components || []).forEach((component) => {
			const option = document.createElement('option');
			option.value = String(component.id);
			option.textContent = component.name + ' [' + component.code + ']';
			select.appendChild(option);
		});
		select.value = String(payload.component || 0);
		$$('.extrusion-detected', select.parentNode).forEach((note) => note.remove());
		if (payload.detected && payload.detected.name) {
			const detected = document.createElement('div');
			detected.className = 'alert alert-info extrusion-detected';
			detected.textContent = T.detected + ' ' + payload.detected.name
				+ ' [' + payload.detected.code + ']';
			select.parentNode.appendChild(detected);
		}
	}

	/**
	 * Fetch the catalogue of one component and re-pair everything by name,
	 * exactly the way the server pairs -- lowercase, name then system name.
	 */
	async function loadCatalogue(componentId) {
		const run = ++state.catalogueRun;
		let catalogue;
		try {
			catalogue = await post('extrusionCatalogue', { component_id: componentId });
		} catch (error) {
			catalogue = { error: error.message || T.requestFailed };
		}
		if (run !== state.catalogueRun) {
			return false;
		}
		state.catalogue = (catalogue && !catalogue.error) ? catalogue : null;
		// a failed catalogue must be said, never quietly rendered as a board
		// where nothing happens to match
		state.catalogueFailed = state.catalogue === null
			? ((catalogue && catalogue.error) || T.catalogueFailed) : '';
		rematch();
		return true;
	}

	function matchByGuid(guid, pool) {
		const wanted = String(guid || '').trim().toLowerCase();
		if (wanted === '') {
			return null;
		}
		for (const row of pool || []) {
			if (String(row.guid || '').trim().toLowerCase() === wanted) {
				return { guid: row.guid, label: row.name || row.system, by: 'guid' };
			}
		}
		return null;
	}

	function matchByName(names, pool) {
		const wanted = names.map((name) => String(name || '').trim().toLowerCase())
			.filter((name) => name !== '');
		for (const row of pool || []) {
			for (const field of ['name', 'system']) {
				const value = String(row[field] || '').trim().toLowerCase();
				if (value !== '' && wanted.indexOf(value) !== -1) {
					return { guid: row.guid, label: row.name || row.system, by: 'name' };
				}
			}
		}
		return null;
	}

	function match(candidate, names, pool) {
		return matchByGuid(candidate.guid, pool) || matchByName(names, pool);
	}

	/**
	 * Re-draw the proposed pairings from the current catalogue. Explicit
	 * decisions are cleared, because they pointed into another component.
	 */
	/**
	 * A name answering inside what the component itself links is that record
	 * rediscovered, exactly as the server pairs it -- so the board proposes
	 * the update the import will perform, never a creation it will not.
	 */
	function scopedLabel(candidate) {
		if (candidate.match && candidate.match.by === 'name') {
			candidate.match.by = 'scoped';
		}
	}

	function rematch() {
		if (!state.data || !state.data.candidates) {
			return;
		}
		const candidates = state.data.candidates;
		const catalogue = state.catalogue || {};
		(candidates.admin_view || []).forEach((view) => {
			view.match = match(view, [view.label, view.detail], catalogue.admin_views);
			scopedLabel(view);
			// the fields the paired view already links are its own wiring: a
			// column answering to one of them is that wiring rediscovered, so
			// it weighs like an identity rather than a mere resemblance
			const scoped = view.match
				? (catalogue.fields || []).filter((row) => row.view === view.match.guid)
				: [];
			(view.fields || []).forEach((field) => {
				// a shared member is decided on its owner's row: the group is
				// one field, so this row takes no pairing of its own unless
				// the person detaches it first
				if (field.shared) {
					field.match = null;
					field.detached = false;
					return;
				}
				let found = matchByGuid(field.guid, catalogue.fields);
				if (!found) {
					found = matchByName([field.label, field.detail], scoped);
					if (found) {
						found.by = 'scoped';
					}
				}
				field.match = found
					|| matchByName([field.label, field.detail], catalogue.fields);
			});
		});
		(candidates.site_view || []).forEach((candidate) => {
			candidate.match = match(candidate, [candidate.label], catalogue.site_views);
			scopedLabel(candidate);
		});
		(candidates.custom_admin_view || []).forEach((candidate) => {
			candidate.match = match(candidate, [candidate.label], catalogue.custom_admin_views);
			scopedLabel(candidate);
		});
		(candidates.layout || []).forEach((candidate) => {
			candidate.match = match(candidate, [candidate.label], catalogue.layouts);
		});
		(candidates.template || []).forEach((candidate) => {
			candidate.match = match(candidate, [candidate.label], catalogue.templates);
		});
		state.decisions = {};
		state.selected.clear();
	}

	/**
	 * The proposal one candidate arrives with, before any explicit decision.
	 *
	 * Only a guid in common is an identity, so only a guid match proposes an
	 * update. A shared name is a resemblance: it stays on offer as the Update
	 * action's suggested target, but the default is a fresh creation -- the
	 * person decides whether the lookalike is really the same thing.
	 */
	function proposal(candidate) {
		if (candidate.kind === 'power') {
			if (candidate.status === 'matched') {
				const target = candidate.target || {};
				return { action: candidate.action === 'skip' ? 'skip' : 'update',
					target: candidate.matched_guid, label: target.system_name || target.name || candidate.matched_guid };
			}
			return { action: candidate.status === 'new' ? 'create'
				: (candidate.status === 'ignored' || candidate.status === 'filtered' ? 'ignore' : 'blocked') };
		}
		return candidate.match
			&& (candidate.match.by === 'guid' || candidate.match.by === 'scoped')
			? { action: 'update', target: candidate.match.guid, label: candidate.match.label }
			: { action: 'create' };
	}

	function decision(candidate) {
		const kind = state.decisions[candidate.kind] || {};
		return kind[candidate.key] || proposal(candidate);
	}

	function decide(candidate, verdict) {
		// a decision moves what this row would write, and what the rows tied
		// to it would write: the weights are read again under the pairing
		// they have now, rather than left showing a number that has moved
		unweigh(candidate);
		state.decisions[candidate.kind] = state.decisions[candidate.kind] || {};
		if (verdict === null) {
			delete state.decisions[candidate.kind][candidate.key];
		} else {
			state.decisions[candidate.kind][candidate.key] = verdict;
		}
	}

	function explicit(candidate) {
		const kind = state.decisions[candidate.kind] || {};
		return Object.prototype.hasOwnProperty.call(kind, candidate.key);
	}

	/**
	 * The configuration a run on the board uses: the setup as harvested,
	 * aimed at the component the board pairs against -- detected or chosen --
	 * so a weighing, a diff and the import are all the same run.
	 */
	function runConfig(importing = false, scopes = []) {
		const config = Object.assign({}, state.config);
		const select = $('extrusion-component-select');
		config.component = parseInt(select.value, 10) || 0;
		config.detect = false;
		delete config.approved_plan;
		delete config.acknowledged_scopes;
		if (importing && state.plan) {
			config.approved_plan = state.plan.fingerprint;
			config.acknowledged_scopes = {};
			scopes.forEach((scope) => { config.acknowledged_scopes[scope] = true; });
		}
		return config;
	}

	/** Invalidate immediately, including an older request still in flight. */
	function invalidateReview() {
		weighingRun++;
		state.reviewPending = true;
		state.plan = null;
		closeConfirmation();
		renderReview();
	}

	/** Keep source keys, never guessed target GUIDs, as the board identity. */
	function acceptReview(payload) {
		if (payload.powers && Array.isArray(payload.powers.classes)) {
			payload.powers.classes.forEach((candidate) => {
				candidate.kind = 'power';
				candidate.key = candidate.source_key;
				candidate.label = candidate.class;
				candidate.detail = candidate.fqn;
			});
			state.data.powers = payload.powers;
		}
		const plan = payload.plan;
		const same = state.plan && plan && state.plan.fingerprint === plan.fingerprint;
		state.plan = plan && /^[a-f0-9]{64}$/.test(plan.fingerprint || '') ? plan : null;
		state.reviewPending = false;
		if (!same) {
			closeConfirmation();
		}
	}

	function reviewReady() {
		if (state.busy || !E.canImport || state.catalogueFailed || state.reviewPending || state.weighingFailed || !state.plan
			|| state.plan.status !== 'preview' || Object.keys(state.plan.blockers || {}).length) {
			return false;
		}
		return true;
	}

	/** Public plans carry blocker records; display their explanations. */
	function planBlockers(plan) {
		return Object.values((plan || {}).blockers || {}).map((blocker) =>
			blocker && typeof blocker === 'object' ? String(blocker.reason || T.operationFailed) : String(blocker));
	}

	function renderReview() {
		const notice = $('extrusion-review-notice');
		if (!notice) {
			return;
		}
		const blockers = planBlockers(state.plan);
		const pending = state.reviewPending || !state.plan;
		notice.className = 'alert ' + (pending ? 'alert-info' : (blockers.length ? 'alert-warning' : 'alert-success'));
		const ambiguous = pending ? [] : (((state.data || {}).powers || {}).classes || []);
		$('extrusion-ambiguity-notice').hidden = !ambiguous.some((candidate) => candidate.status === 'ambiguous');
		const ambiguousKeys = new Set(ambiguous.filter((candidate) => candidate.status === 'ambiguous')
			.map((candidate) => candidate.source_key));
		const reasons = [...new Set(Object.values((state.plan || {}).blockers || {})
			.filter((entry) => !entry || typeof entry !== 'object'
				|| !ambiguousKeys.has(String(entry.key || '').split('.')[1]))
			.map((entry) => entry && typeof entry === 'object' ? entry.reason : String(entry)))];
		notice.innerHTML = esc(state.weighingFailed || (pending ? T.reviewPending
			: (blockers.length ? T.reviewBlocked : T.reviewReady)))
			+ (!pending && reasons.length ? '<details class="extrusion-review-details"><summary>'
				+ esc(T.blockerDetails) + '</summary><ul>'
				+ reasons.map((reason) => '<li>' + esc(reason) + '</li>').join('') + '</ul></details>' : '');
		const button = $('extrusion-import-button');
		if (button) {
			button.disabled = !reviewReady();
		}
	}

	/** Render the server's actual target and bounded evidence, independently of the source. */
	function powerDetails(candidate) {
		if (state.reviewPending) {
			return '<span class="extrusion-power-evidence">' + esc(T.reviewPending) + '</span>';
		}
		const target = candidate.target;
		let html = '<span class="extrusion-power-evidence">';
		if (candidate.action === 'skip') {
			html += '<span>' + esc(T.skippedExisting) + '</span> ';
		}
		if (target) {
			html += '<span>' + esc(T.actualTarget) + ': <strong class="extrusion-target-name">'
				+ esc(target.system_name || target.name) + '</strong></span>'
				+ '<code class="extrusion-target-guid">' + esc(target.guid) + '</code>'
				+ '<code class="extrusion-target-namespace">' + esc(target.namespace) + '</code>';
		} else if (candidate.status === 'new') {
			html += '<span>' + esc(T.newIdentity) + ': <code>' + esc(candidate.write_guid) + '</code></span>';
		}
		(candidate.status === 'ambiguous' ? [T.ambiguousHint] : (candidate.blockers || [])).forEach((reason) => {
			html += '<span class="text-danger">' + esc(reason) + '</span>';
		});
		const alternatives = (candidate.alternatives || []).filter((entry) => !target || entry.guid !== target.guid);
		if (alternatives.length) {
			html += '<details><summary>' + esc(T.otherCandidates) + ' (' + alternatives.length + ')</summary>';
			alternatives.forEach((entry) => {
				html += '<span><code>' + esc(entry.guid) + '</code> ' + esc(entry.system_name)
					+ '</span><code>' + esc(entry.namespace) + '</code>';
			});
			html += '</details>';
		}
		const namespace = candidate.namespace_proposal;
		if (namespace && (namespace.relocation || namespace.provenance === 'namespace-repair')) {
			html += '<span>' + esc(namespace.provenance === 'namespace-repair' ? T.repairProposal : T.relocation)
				+ ': <code>' + esc(namespace.value) + '</code></span>';
		}
		return html + '</span>';
	}

	/**
	 * What one row of the board would change, as it was last weighed.
	 */
	function weight(candidate) {
		return state.changes[candidate.kind + '|' + candidate.key] || null;
	}

	/**
	 * Mark a row, and the rows tied to it, as owed a fresh weighing.
	 *
	 * A view's fields are written under the view's pairing, and a field's
	 * links are written on its view's row, so a decision on either moves the
	 * other. The whole board is weighed again; these are the rows that say
	 * so while the answer is on its way.
	 */
	function unweigh(candidate) {
		state.weighing.add(candidate.kind + '|' + candidate.key);
		if (candidate.kind === 'admin_view') {
			(candidate.fields || []).forEach((field) => {
				state.weighing.add('field|' + field.key);
			});
		} else if (candidate.kind === 'field') {
			state.weighing.add('admin_view|' + candidate.key.split('.')[0]);
		}
		scheduleWeighing();
	}

	function scheduleWeighing() {
		if (batchingDecisions) {
			return;
		}
		invalidateReview();
		window.clearTimeout(weighingTimer);
		weighingTimer = window.setTimeout(reweigh, 400);
	}

	/**
	 * Read every row's weight again under the decisions as they stand.
	 *
	 * Only the latest answer counts: a decision made while one is on its way
	 * starts another, and the earlier answer is dropped when it lands. A diff
	 * left open on a row whose weight moved is read again too, so nothing on
	 * the board answers for a pairing that is gone.
	 */
	async function reweigh() {
		const run = ++weighingRun;
		let payload;
		try {
			payload = await post('extrusionWeigh', {
				config: JSON.stringify(runConfig()),
				decisions: JSON.stringify(buildDecisions())
			});
		} catch (error) {
			payload = { error: error.message || T.requestFailed };
		}
		if (run !== weighingRun) {
			return;
		}
		const owed = new Set(state.weighing);
		state.weighing.clear();
		if (!payload || payload.error || !payload.changes) {
			state.weighingFailed = (payload && payload.error) || T.weighingFailed;
			renderBoard();
			return;
		}
		acceptReview(payload);
		const before = state.changes;
		state.changes = payload.changes;
		state.weighingFailed = '';
		Object.keys(state.diffs).forEach((id) => {
			if (!state.changes[id]) {
				closeDiff(id);
			} else if (owed.has(id) || moved(before[id], state.changes[id])) {
				fetchDiff(id);
			}
		});
		renderBoard();
	}

	function moved(was, now) {
		return !was || was.changed !== now.changed
			|| was.additions !== now.additions || was.deletions !== now.deletions;
	}

	/**
	 * The badge that says what a row would change, and opens the diff.
	 */
	function changeBadge(candidate) {
		const id = candidate.kind + '|' + candidate.key;
		if (state.weighing.has(id)) {
			return '<span class="extrusion-change extrusion-change-weighing" title="'
				+ esc(T.weighingHint) + '">' + esc(T.weighing) + '</span>';
		}
		const owed = weight(candidate);
		if (!owed) {
			return '';
		}
		// a record that would change nothing is not written -- the engine
		// settles that itself. The row is never set to ignore for it: ignoring
		// takes the row out of the run altogether, and the view would lose the
		// links and conditions that still have to be written for it
		if (!owed.changed) {
			return '<span class="extrusion-change extrusion-change-none" title="'
				+ esc(T.noChangeHint) + '">' + esc(T.noChange) + '</span>';
		}
		const open = Object.prototype.hasOwnProperty.call(state.diffs, id);
		return '<button type="button" class="extrusion-change' + (open ? ' open' : '')
			+ '" data-extrusion-act="diff" aria-expanded="' + (open ? 'true' : 'false')
			+ '" title="' + esc(T.diffHint) + '">'
			+ '<span class="extrusion-add">+' + owed.additions + '</span>'
			+ '<span class="extrusion-del">&minus;' + owed.deletions + '</span></button>';
	}

	/**
	 * Open or close one row's diff. Only what is open is held.
	 */
	async function toggleDiff(candidate) {
		const id = candidate.kind + '|' + candidate.key;
		if (Object.prototype.hasOwnProperty.call(state.diffs, id)) {
			closeDiff(id);
			renderBoard();
			return;
		}
		if (candidate.kind === 'field') {
			state.opened.add('fields:admin_view|' + candidate.key.split('.')[0]);
		}
		await fetchDiff(id);
	}

	/**
	 * Read one row's diff under the decisions as they stand, and hold it.
	 */
	async function fetchDiff(id) {
		const run = weighingRun;
		state.diffs[id] = { loading: true };
		renderBoard();
		let payload;
		try {
			payload = await post('extrusionDiff', {
				config: JSON.stringify(runConfig()),
				decisions: JSON.stringify(buildDecisions()),
				row: id
			});
		} catch (error) {
			payload = { error: error.message || T.requestFailed };
		}
		if (run !== weighingRun) {
			return;
		}
		if (payload && payload.plan && state.plan
			&& payload.plan.fingerprint !== state.plan.fingerprint) {
			scheduleWeighing();
		}
		// the person may have closed it while it was on its way
		if (Object.prototype.hasOwnProperty.call(state.diffs, id)) {
			state.diffs[id] = payload && payload.error
				? { error: payload.error }
				: { records: (payload && payload.records) || [] };
		}
		renderBoard();
	}

	function closeDiff(id) {
		delete state.diffs[id];
	}

	/**
	 * One row's diff, read only, side by side.
	 */
	function renderDiff(id) {
		const held = state.diffs[id];
		if (!held) {
			return '';
		}
		if (held.loading) {
			return '<div class="extrusion-diff"><p class="extrusion-diff-note">'
				+ esc(T.diffLoading) + '</p></div>';
		}
		if (held.error) {
			return '<div class="extrusion-diff"><p class="extrusion-diff-note">'
				+ esc(held.error) + '</p></div>';
		}
		if (!held.records.length) {
			return '<div class="extrusion-diff"><p class="extrusion-diff-note">'
				+ esc(T.noChange) + '</p></div>';
		}
		let html = '<div class="extrusion-diff">';
		held.records.forEach((record) => {
			html += '<div class="extrusion-diff-record"><h4>' + esc(record.table)
				+ ' <small>' + esc(record.action === 'create' ? T.diffCreates : T.diffUpdates)
				+ '</small></h4>';
			record.columns.forEach((column) => {
				html += '<div class="extrusion-diff-column"><h5>' + esc(column.name)
					+ ' <span class="extrusion-add">+' + column.additions + '</span>'
					+ ' <span class="extrusion-del">&minus;' + column.deletions + '</span></h5>'
					+ '<table class="extrusion-diff-table">';
				column.hunks.forEach((hunk, index) => {
					if (index > 0) {
						html += '<tr class="extrusion-diff-gap"><td colspan="4">&hellip;</td></tr>';
					}
					html += diffRows(hunk.lines);
				});
				html += '</table></div>';
			});
			html += '</div>';
		});
		return html + '</div>';
	}

	/**
	 * The lines of one hunk, the removals beside the additions that replace
	 * them -- which is how a person reads a change, one side against the other.
	 */
	function diffRows(lines) {
		let html = '';
		let removed = [];
		let added = [];
		const flush = () => {
			const rows = Math.max(removed.length, added.length);
			for (let index = 0; index < rows; index++) {
				html += diffRow(removed[index] || null, added[index] || null);
			}
			removed = [];
			added = [];
		};
		lines.forEach((line) => {
			if (line.op === 'remove') {
				removed.push(line);
			} else if (line.op === 'add') {
				added.push(line);
			} else {
				flush();
				html += diffRow(line, line);
			}
		});
		flush();
		return html;
	}

	function diffRow(left, right) {
		const side = (line, kind) => {
			if (!line) {
				return '<td class="extrusion-diff-num"></td><td class="extrusion-diff-line empty"></td>';
			}
			const number = kind === 'old' ? line.old : line.new;
			return '<td class="extrusion-diff-num">' + (number === null ? '' : number)
				+ '</td><td class="extrusion-diff-line ' + esc(line.op) + '">'
				+ esc(line.text) + '</td>';
		};
		return '<tr>' + side(left, 'old') + side(right, 'new') + '</tr>';
	}

	/**
	 * Every candidate on the board, flat, for bulk work and the import.
	 */
	function allCandidates() {
		const rows = [];
		const data = state.data || {};
		const candidates = data.candidates || {};
		(candidates.admin_view || []).forEach((view) => {
			rows.push(view);
			(view.fields || []).forEach((field) => rows.push(field));
		});
		['site_view', 'custom_admin_view', 'layout', 'template'].forEach((kind) => {
			(candidates[kind] || []).forEach((candidate) => rows.push(candidate));
		});
		if (data.powers && data.powers.classes) {
			data.powers.classes.forEach((candidate) => rows.push(candidate));
		}
		return rows;
	}

	/**
	 * Render the whole pairing board.
	 */
	function renderBoard() {
		const board = $('extrusion-board');
		state.candidateIndex = new Map(allCandidates().map((candidate) =>
			[candidate.kind + '|' + candidate.key, candidate]));
		const data = state.data || {};
		const candidates = data.candidates || {};
		let html = '';
		if (state.catalogueFailed) {
			html += '<div class="alert alert-warning" data-extrusion-warning="catalogue">'
				+ esc(T.catalogueFailed) + ' ' + esc(state.catalogueFailed) + '</div>';
		}
		if (state.weighingFailed) {
			html += '<div class="alert alert-warning" data-extrusion-warning="weighing">'
				+ esc(state.weighingFailed) + '</div>';
		}
		if ((candidates.admin_view || []).length) {
			html += kindSection('admin_view', T.adminViews, candidates.admin_view, true);
		}
		['site_view', 'custom_admin_view', 'layout', 'template'].forEach((kind) => {
			const label = {
				site_view: T.siteViews,
				custom_admin_view: T.customAdminViews,
				layout: T.layouts,
				template: T.templates
			}[kind];
			if ((candidates[kind] || []).length) {
				html += kindSection(kind, label, candidates[kind], false);
			}
		});
		if (data.powers && (data.powers.classes || []).length) {
			html += powersSection(data.powers);
		}
		board.innerHTML = html;
		renderReview();
		applyFilter($('extrusion-filter').value);
		$$('#extrusion-board [data-extrusion-open]').forEach((node) => {
			node.addEventListener('toggle', () => {
				const key = node.dataset.extrusionOpen;
				if (node.open) {
					state.opened.add(key);
				} else {
					state.opened.delete(key);
				}
			});
		});
	}

	/** One compact status, independent of the effective change underneath it. */
	function matchingStatus(candidate) {
		if (candidate.kind === 'power') {
			return candidate.status || 'unresolved';
		}
		if (candidate.shared && !candidate.detached) {
			return 'shared';
		}
		const current = decision(candidate);
		if (current.action === 'ignore') {
			return 'ignored';
		}
		if (current.action === 'update' && current.target) {
			return 'matched';
		}
		return candidate.match ? 'similar' : 'unmatched';
	}

	/** Classify only the current validated proposal, never an old update label. */
	function plannedChange(candidate) {
		if (state.reviewPending) {
			return 'pending';
		}
		const current = decision(candidate);
		if (['ignore', 'ignored', 'filtered'].includes(current.action)) {
			return 'ignore';
		}
		if (current.action === 'blocked' || ['ambiguous', 'conflict', 'unresolved'].includes(candidate.status)) {
			return 'blocked';
		}
		if (current.action === 'skip' || (candidate.shared && !candidate.detached)) {
			return 'nochange';
		}
		const proposal = weight(candidate);
		return proposal ? (proposal.changed ? proposal.action : 'nochange') : 'pending';
	}

	function rowHeading(candidate) {
		const status = matchingStatus(candidate);
		return '<span class="extrusion-row-heading"><b>' + esc(candidate.label) + '</b>'
			+ ' <span class="badge bg-secondary extrusion-match-status" data-extrusion-status="'
			+ esc(status) + '">' + esc(T['status_' + status] || status) + '</span>'
			+ (candidate.detail && candidate.detail !== candidate.label
				? ' <small>' + esc(candidate.detail) + '</small>' : '') + '</span>';
	}

	function groupCheck(label) {
		return '<input type="checkbox" class="extrusion-group-check" data-extrusion-group'
			+ ' aria-label="' + esc(T.selectGroup + ' ' + label) + '" /> ';
	}

	function counts(list) {
		let matched = 0;
		let similar = 0;
		let shared = 0;
		let unresolved = 0;
		(list || []).forEach((candidate) => {
			if (candidate.kind === 'power' && !['matched', 'new'].includes(candidate.status)) {
				unresolved++;
			} else if (candidate.shared && !candidate.detached) {
				shared++;
			} else if ((candidate.match
				&& (candidate.match.by === 'guid' || candidate.match.by === 'scoped'))
				|| (candidate.kind === 'power' && candidate.status === 'matched')) {
				matched++;
			} else if (candidate.match) {
				similar++;
			}
		});
		return ' <small>(' + list.length + ' ' + esc(T.items) + ', ' + matched + ' '
			+ esc(T.matched) + ', ' + similar + ' ' + esc(T.similar) + ', '
			+ (shared ? shared + ' ' + esc(T.shared) + ', ' : '')
			+ (unresolved ? unresolved + ' ' + esc(T.unresolved) + ', ' : '')
			+ (list.length - matched - shared - unresolved) + ' ' + esc(T.newItem) + ')</small>';
	}

	function kindSection(kind, label, list, withFields) {
		let html = '<details class="extrusion-kind" open data-extrusion-group-container data-extrusion-kind="' + kind + '">'
			+ '<summary>' + groupCheck(label) + esc(label) + counts(list) + '</summary>'
			+ '<div class="extrusion-rows">';
		list.forEach((candidate) => {
			html += row(candidate);
			if (withFields && (candidate.fields || []).length) {
				const fieldsKey = 'fields:' + candidate.kind + '|' + candidate.key;
				html += '<details class="extrusion-fields" data-extrusion-group-container data-extrusion-open="'
					+ esc(fieldsKey) + '"' + (state.opened.has(fieldsKey) ? ' open' : '')
					+ '><summary>'
					+ groupCheck(T.fields + ': ' + candidate.label) + esc(T.fields) + counts(candidate.fields) + '</summary>'
					+ '<div class="extrusion-rows">';
				candidate.fields.forEach((field) => {
					html += row(field);
				});
				html += '</div></details>';
			}
		});
		return html + '</div></details>';
	}

	function powersSection(powers) {
		const byLibrary = {};
		(powers.classes || []).forEach((candidate) => {
			byLibrary[candidate.library] = byLibrary[candidate.library] || {};
			const bundle = candidate.bundle || '.';
			byLibrary[candidate.library][bundle] = byLibrary[candidate.library][bundle] || [];
			byLibrary[candidate.library][bundle].push(candidate);
		});
		let html = '<details class="extrusion-kind" open data-extrusion-group-container data-extrusion-kind="power">'
			+ '<summary>' + groupCheck(T.powers) + esc(T.powers) + counts(powers.classes) + '</summary>'
			+ '<div class="extrusion-rows">';
		Object.keys(byLibrary).sort().forEach((library) => {
			html += '<details class="extrusion-library" open data-extrusion-group-container><summary>'
				+ groupCheck(library) + esc(library) + '</summary>';
			Object.keys(byLibrary[library]).sort().forEach((bundle) => {
				const list = byLibrary[library][bundle];
				html += '<details class="extrusion-bundle" open data-extrusion-group-container><summary>'
					+ groupCheck(bundle) + esc(bundle)
					+ counts(list) + '</summary><div class="extrusion-rows">';
				list.forEach((candidate) => {
					html += row(candidate);
				});
				html += '</div></details>';
			});
			html += '</details>';
		});
		return html + '</div></details>';
	}

	/**
	 * One candidate row: tick, identity, and the three decisions --
	 * create new first, then update with a chosen target, then ignore.
	 *
	 * A shared member renders as its group's row instead: one field, owned by
	 * the view that states it first, this view linking it. It takes no
	 * decision of its own unless the person detaches it -- a detached member
	 * becomes an ordinary row whose verdict speaks for this one view only.
	 */
	function row(candidate) {
		const id = candidate.kind + '|' + candidate.key;
		if (candidate.shared && !candidate.detached) {
			return '<div class="extrusion-row extrusion-shared" data-extrusion-row="'
				+ esc(id) + '">'
				+ '<span class="extrusion-tick"></span>'
				+ '<span class="extrusion-identity">' + rowHeading(candidate)
				+ ' <span class="badge bg-info extrusion-shared-note">'
				+ esc(T.sharedWith) + ' ' + esc(candidate.shared.owner) + '</span>'
				+ '</span>'
				+ '<span class="extrusion-actions">'
				+ '<button type="button" class="btn btn-sm extrusion-act" '
				+ 'data-extrusion-act="detach" title="' + esc(T.detachHint) + '">'
				+ esc(T.detach) + '</button>'
			+ '</span></div>';
		}
		const current = decision(candidate);
		const isExplicit = explicit(candidate);
		const isGroup = Boolean(candidate.shared_by && candidate.shared_by.length);
		const active = (action) => current.action === action ? ' active' : '';
		const targetLabel = current.action === 'update' && current.label
			? esc(current.label) : esc(T.update);
		return '<div class="extrusion-row' + (isExplicit ? ' explicit' : '')
			+ '" data-extrusion-row="' + esc(id) + '">'
			+ '<label class="extrusion-tick"><input type="checkbox" data-extrusion-check="'
			+ esc(id) + '" aria-label="' + esc(T.selectItem + ' ' + candidate.label) + '"'
			+ (state.selected.has(id) ? ' checked' : '') + ' /></label>'
			+ '<span class="extrusion-identity">' + rowHeading(candidate)
			+ (isGroup
				? ' <span class="badge bg-success extrusion-shared-note">'
					+ esc(T.oneField) + ' ' + (candidate.shared_by.length + 1) + ' '
					+ esc(T.views) + '</span>'
				: '')
			+ (candidate.shared && candidate.detached
				? ' <span class="badge bg-warning extrusion-shared-note">'
					+ esc(T.detached) + '</span>'
				: '')
			+ (candidate.kind === 'power' ? powerDetails(candidate) : '')
			+ '</span>'
			+ '<span class="extrusion-actions">'
			+ changeBadge(candidate)
			+ (isNamespaceRepair() ? '' : '<button type="button" class="btn btn-sm extrusion-act' + active('create')
				+ '" data-extrusion-act="create">' + esc(T.createNew) + '</button>')
			+ '<button type="button" class="btn btn-sm extrusion-act extrusion-act-update' + active('update')
			+ '" data-extrusion-act="update" title="' + esc(T.chooseTarget) + '">'
			+ targetLabel + '</button>'
			+ '<button type="button" class="btn btn-sm extrusion-act' + active('ignore')
			+ '" data-extrusion-act="ignore">' + esc(T.ignore) + '</button>'
			+ (isExplicit || (candidate.shared && candidate.detached)
				? '<button type="button" class="btn btn-sm extrusion-act extrusion-act-reset" '
					+ 'data-extrusion-act="reset" title="' + esc(T.proposed) + '">&#8634;</button>'
				: '')
			+ '</span>' + renderDiff(id) + '</div>';
	}

	function findCandidate(id) {
		return state.candidateIndex.get(id) || null;
	}

	function wireBoard() {
		$('extrusion-board').addEventListener('click', (event) => {
			const group = event.target.closest('[data-extrusion-group]');
			if (group) {
				event.stopPropagation();
				selectableRows(group.closest('[data-extrusion-group-container]')).forEach((rowElement) => {
					const id = rowElement.dataset.extrusionRow;
					if (group.checked) {
						state.selected.add(id);
					} else {
						state.selected.delete(id);
					}
					rowElement.querySelector('[data-extrusion-check]').checked = group.checked;
				});
				refreshBulkBar();
				return;
			}
			const button = event.target.closest('[data-extrusion-act]');
			if (button) {
				const rowElement = button.closest('[data-extrusion-row]');
				const candidate = findCandidate(rowElement.dataset.extrusionRow);
				if (!candidate) {
					return;
				}
				const action = button.dataset.extrusionAct;
				if (action === 'diff') {
					toggleDiff(candidate);
					return;
				}
				if (action === 'create') {
					decide(candidate, { action: 'create' });
				} else if (action === 'ignore') {
					decide(candidate, { action: 'ignore' });
				} else if (action === 'detach') {
					// the person takes this one view out of its group: the row
					// becomes an ordinary candidate whose verdict is its own --
					// and detaching IS a verdict, or the import would quietly
					// return the view to its group
					candidate.detached = true;
					decide(candidate, { action: 'create' });
				} else if (action === 'reset') {
					decide(candidate, null);
					if (candidate.shared) {
						candidate.detached = false;
						state.selected.delete(candidate.kind + '|' + candidate.key);
					}
				} else if (action === 'update') {
					openModal(candidate);
					return;
				}
				renderBoard();
				return;
			}
			const check = event.target.closest('[data-extrusion-check]');
			if (check) {
				const id = check.dataset.extrusionCheck;
				if (check.checked) {
					state.selected.add(id);
				} else {
					state.selected.delete(id);
				}
				refreshBulkBar();
			}
		});
	}

	/**
	 * The one shared searchable picker. A candidate kind draws its choices
	 * from the catalogue pool of that kind, so even eight hundred powers
	 * stay one filtered list instead of eight hundred select fields.
	 */
	function modalPool(kind) {
		const catalogue = state.catalogue || {};
		const pools = {
			admin_view: catalogue.admin_views,
			field: catalogue.fields,
			site_view: catalogue.site_views,
			custom_admin_view: catalogue.custom_admin_views,
			layout: catalogue.layouts,
			template: catalogue.templates,
			power: state.modalPowers === null ? catalogue.powers : state.modalPowers
		};
		return (pools[kind] || []).map((row) => ({
			guid: row.guid,
			label: row.name || row.system || row.guid,
			detail: row.system && row.system !== row.name
				? row.system : (row.namespace || '')
		}));
	}

	function openModal(candidate) {
		state.modal = candidate;
		state.modalPowers = null;
		state.modalPowersTruncated = false;
		powerSearchRun++;
		window.clearTimeout(powerSearchTimer);
		$('extrusion-power-search-hint').hidden = candidate.kind !== 'power';
		const search = $('extrusion-modal-search');
		// a name lookalike is never acted on by itself, but it is the first
		// thing the person deciding an update should see
		search.value = candidate.match && candidate.match.by === 'name'
			? String(candidate.match.label || '') : '';
		renderModalList(search.value);
		$('extrusion-modal').style.display = 'flex';
		search.focus();
	}

	function renderModalList(filter) {
		const list = $('extrusion-modal-list');
		const wanted = filter.trim().toLowerCase();
		const pool = state.modal ? modalPool(state.modal.kind) : [];
		const rows = pool.filter((row) => wanted === ''
			|| row.label.toLowerCase().indexOf(wanted) !== -1
			|| row.guid.toLowerCase().indexOf(wanted) !== -1
			|| String(row.detail).toLowerCase().indexOf(wanted) !== -1).slice(0, 100);
		if (!rows.length) {
			list.innerHTML = '<div class="extrusion-modal-empty">' + esc(T.noMatches) + '</div>';
			return;
		}
		list.innerHTML = rows.map((row) => '<button type="button" class="extrusion-modal-row" '
			+ 'data-extrusion-target="' + esc(row.guid) + '" data-extrusion-label="' + esc(row.label) + '">'
			+ '<b>' + esc(row.label) + '</b>'
			+ (row.detail ? ' <small>' + esc(row.detail) + '</small>' : '')
			+ '</button>').join('')
			+ (state.modalPowersTruncated
				? '<div class="extrusion-modal-empty">' + esc(T.powerSearchTruncated) + '</div>' : '');
	}

	function closeModal() {
		state.modal = null;
		state.modalPowers = null;
		state.modalPowersTruncated = false;
		powerSearchRun++;
		window.clearTimeout(powerSearchTimer);
		$('extrusion-modal').style.display = 'none';
	}

	/** Search outside the linked Power set only when the person supplies a value. */
	function searchModal(filter) {
		const run = ++powerSearchRun;
		window.clearTimeout(powerSearchTimer);
		state.modalPowers = null;
		state.modalPowersTruncated = false;
		renderModalList(filter);
		if (!state.modal || state.modal.kind !== 'power' || filter.trim() === '') {
			return;
		}
		powerSearchTimer = window.setTimeout(async () => {
			let payload;
			try {
				payload = await post('extrusionCatalogue', {
					component_id: $('extrusion-component-select').value,
					power_search: filter.trim()
				});
			} catch (error) {
				payload = { error: error.message || T.requestFailed };
			}
			if (run !== powerSearchRun || !state.modal || state.modal.kind !== 'power') {
				return;
			}
			if (payload.error || !Array.isArray(payload.powers)) {
				$('extrusion-modal-list').innerHTML = '<div class="extrusion-modal-empty">'
					+ esc(payload.error || T.catalogueFailed) + '</div>';
				return;
			}
			state.modalPowers = payload.powers;
			state.modalPowersTruncated = payload.power_search && payload.power_search.truncated === true;
			renderModalList(filter);
		}, 300);
	}

	/**
	 * Bulk work over the ticked rows.
	 */
	function bulk(action) {
		const visible = new Set(selectableRows($('extrusion-board')).map((rowElement) => rowElement.dataset.extrusionRow));
		batchingDecisions = true;
		state.selected.forEach((id) => {
			if (!visible.has(id)) {
				return;
			}
			const candidate = findCandidate(id);
			if (!candidate) {
				return;
			}
			// a shared member still standing in its group has no decision to
			// bulk: it is decided on its owner's row, or detached one by one
			if (candidate.shared && !candidate.detached) {
				state.selected.delete(id);
				return;
			}
			if (action === 'reset') {
				decide(candidate, null);
				if (candidate.shared) {
					candidate.detached = false;
					state.selected.delete(id);
				}
			} else {
				decide(candidate, { action: action });
			}
		});
		batchingDecisions = false;
		scheduleWeighing();
		renderBoard();
	}

	function refreshBulkBar() {
		$('extrusion-selected-count').textContent = String(state.selected.size);
		$$('#extrusion-bulk-bar [data-extrusion-bulk]').forEach((button) => {
			button.disabled = state.selected.size === 0 || state.busy;
		});
		$$('#extrusion-board [data-extrusion-group-container]').forEach((container) => {
			const checkbox = container.querySelector(':scope > summary > [data-extrusion-group]');
			const rows = selectableRows(container);
			const count = rows.filter((rowElement) => state.selected.has(rowElement.dataset.extrusionRow)).length;
			checkbox.checked = rows.length > 0 && count === rows.length;
			checkbox.indeterminate = count > 0 && count < rows.length;
			checkbox.disabled = rows.length === 0;
		});
	}

	/** Collapsed descendants remain selectable; filtered or shared members do not. */
	function selectableRows(container) {
		return $$('.extrusion-row[data-extrusion-visible="true"]', container)
			.filter((rowElement) => rowElement.querySelector('[data-extrusion-check]'));
	}

	/**
	 * Hide rows the filter does not name, and open every group that still
	 * holds a match -- a hit inside a collapsed group is no hit at all.
	 */
	function applyFilter(value) {
		const wanted = value.trim().toLowerCase();
		const kind = $('extrusion-filter-type').value;
		const status = $('extrusion-filter-status').value;
		const change = $('extrusion-filter-change').value;
		const active = wanted !== '' || kind !== '' || status !== '' || change !== '';
		let visible = 0;
		$$('#extrusion-board .extrusion-row').forEach((rowElement) => {
			const candidate = findCandidate(rowElement.dataset.extrusionRow);
			const match = candidate && (wanted === '' || rowElement.textContent.toLowerCase().includes(wanted))
				&& (kind === '' || candidate.kind === kind)
				&& (status === '' || matchingStatus(candidate) === status)
				&& (change === '' || plannedChange(candidate) === change);
			rowElement.dataset.extrusionVisible = match ? 'true' : 'false';
			rowElement.style.display = match ? '' : 'none';
			if (match) {
				visible++;
			} else {
				state.selected.delete(rowElement.dataset.extrusionRow);
				const check = rowElement.querySelector('[data-extrusion-check]');
				if (check) {
					check.checked = false;
				}
			}
			if (match && active) {
				let group = rowElement.closest('details');
				while (group) {
					group.open = true;
					group = group.parentElement
						? group.parentElement.closest('details') : null;
				}
			}
		});
		$$('#extrusion-board [data-extrusion-group-container]').forEach((group) => {
			group.hidden = !group.querySelector('.extrusion-row[data-extrusion-visible="true"]');
		});
		$('extrusion-visible-count').textContent = String(visible);
		$('extrusion-total-count').textContent = String(state.candidateIndex.size);
		$('extrusion-filter-empty').hidden = visible > 0;
		refreshBulkBar();
	}

	/**
	 * Only what a person explicitly decided travels as a verdict.
	 *
	 * An untouched row ships nothing: the engine settles it -- shared groups
	 * keep one field, matched candidates update what stands, and what is
	 * genuinely new takes the engine's own identity, so a re-run stays
	 * deterministic. A proposal shipped back as if a person had chosen it
	 * would detach shared members and force fresh copies of fields that
	 * merely resemble something -- the exact duplication this board exists
	 * to prevent.
	 *
	 * A decision on a group's owner row speaks for the whole group, so it
	 * travels under its own kind; a decision on a detached member speaks for
	 * that one view only.
	 */
	function buildDecisions() {
		const payload = {};
		allCandidates().forEach((candidate) => {
			if (!explicit(candidate)) {
				return;
			}
			const current = decision(candidate);
			const verdict = current.action === 'update'
				? { action: 'update', target: current.target }
				: { action: current.action };
			const kind = candidate.kind === 'field'
				&& candidate.shared_by && candidate.shared_by.length
				? 'field_group' : candidate.kind;
			payload[kind] = payload[kind] || {};
			payload[kind][candidate.key] = verdict;
		});
		return payload;
	}

	/**
	 * Approval exists only for the exact plan shown when this dialog opened.
	 */
	function openConfirmation() {
		state.confirmation = {
			fingerprint: state.plan.fingerprint,
			scopes: [...(state.plan.required_approvals || [])]
		};
		const repair = isNamespaceRepair();
		$('extrusion-confirm-title').textContent = repair ? T.repairConfirmTitle : T.confirmTitle;
		$('extrusion-confirm-description').textContent = repair ? T.repairConfirmDescription : T.confirmDescription;
		$('extrusion-confirm-import').textContent = repair ? T.repairConfirmLabel : T.confirmLabel;
		$('extrusion-confirm-dry-run').hidden = String((state.config || {}).dry_run) !== '1';
		$('extrusion-confirm-modal').style.display = 'flex';
		$('extrusion-confirm-cancel').focus();
	}

	function closeConfirmation() {
		const opened = state.confirmation !== null;
		state.confirmation = null;
		const dialog = $('extrusion-confirm-modal');
		if (dialog) {
			dialog.style.display = 'none';
		}
		if (opened && $('extrusion-import-button')) {
			$('extrusion-import-button').focus();
		}
	}

	function confirmImport() {
		const approval = state.confirmation;
		closeConfirmation();
		if (!approval || !reviewReady() || approval.fingerprint !== state.plan.fingerprint
			|| JSON.stringify(approval.scopes) !== JSON.stringify(state.plan.required_approvals || [])) {
			return;
		}
		runImport(approval);
	}

	/**
	 * Run the import under the decisions of the board.
	 */
	async function runImport(approval = null) {
		if (!reviewReady()) {
			renderReview();
			return;
		}
		const scopes = state.plan.required_approvals || [];
		if (scopes.length && (!approval || approval.fingerprint !== state.plan.fingerprint
			|| JSON.stringify(approval.scopes) !== JSON.stringify(scopes))) {
			openConfirmation();
			return;
		}
		const config = runConfig(true, approval ? approval.scopes : []);
		const decisions = buildDecisions();
		const finishRun = beginRun();
		$('extrusion-running-title').textContent = config.admin_path || T.theSource;
		$('extrusion-running-verb').textContent = isNamespaceRepair() ? T.repairing : T.importing;
		showPane('running');
		let payload;
		try {
			payload = await post('extrusionImport', {
				config: JSON.stringify(config),
				decisions: JSON.stringify(decisions)
			});
		} catch (error) {
			payload = { error: error.message || T.requestFailed };
		} finally {
			// A disconnected request may already have written. Never retain
			// its approval or retry it; another import needs a fresh review.
			invalidateReview();
			finishRun();
		}
		if (!payload || (!payload.error && !payload.success)) {
			payload = { error: T.importFailed };
		}
		enableTab('results');
		renderResults(payload || { error: T.importFailed });
		showPane('results');
	}

	/**
	 * The live report: what was written, skipped, and failed, and every
	 * message the engines had to say.
	 */
	function renderResults(payload) {
		const container = $('extrusion-results');
		let html = '';
		if (payload.error) {
			html += '<div class="alert alert-danger">' + esc(payload.error) + '</div>';
		} else {
			html += '<div class="alert alert-success">' + esc(payload.success || T.importDone) + '</div>';
		}
		const report = payload.report || {};
		planBlockers(payload.plan || report.plan).forEach((reason) => {
			html += '<div class="alert alert-warning">' + esc(reason) + '</div>';
		});
		if (report.dry_run) {
			html += '<div class="alert alert-info">' + esc(T.dryRun) + '</div>';
		}
		const levels = { success: 'success', info: 'info', warning: 'warning', error: 'danger' };
		const messages = payload.messages || {};
		let messageHtml = '';
		Object.keys(levels).forEach((level) => {
			(messages[level] || []).forEach((entry) => {
				messageHtml += '<div class="alert alert-' + levels[level] + '">'
					+ esc(entry.message)
					+ (entry.subject ? ' <small>' + esc(entry.subject) + '</small>' : '')
					+ '</div>';
			});
		});
		if (messageHtml) {
			html += '<h4>' + esc(T.messages) + '</h4>' + messageHtml;
		}
		['written', 'shared', 'adopted', 'consolidated', 'reuse', 'kept',
			'skipped', 'failed'].forEach((section) => {
			const tree = report[section];
			if (!tree || typeof tree !== 'object') {
				return;
			}
			const rows = flatten(tree, section);
			if (!rows.length) {
				return;
			}
			const label = {
				written: T.written,
				shared: T.sharedSection,
				adopted: T.adopted,
				consolidated: T.consolidated,
				reuse: T.reused,
				kept: T.kept,
				skipped: T.skipped,
				failed: T.failed
			}[section];
			html += '<details' + (section === 'failed' ? ' open' : '') + '><summary>'
				+ esc(label) + ' <small>(' + rows.length + ')</small></summary>'
				+ '<table class="table table-sm"><tbody>'
				+ rows.map((entry) => '<tr><td>' + esc(entry.path) + '</td><td>'
					+ esc(entry.value) + '</td></tr>').join('')
				+ '</tbody></table></details>';
		});
		container.innerHTML = html;
	}

	function flatten(tree, prefix) {
		const rows = [];
		Object.entries(tree).forEach(([key, value]) => {
			const path = prefix + '.' + key;
			if (value && typeof value === 'object') {
				rows.push(...flatten(value, path));
			} else {
				rows.push({ path: path, value: String(value) });
			}
		});
		return rows;
	}

	/**
	 * The folder picker: walk the site from its root and select, never type.
	 *
	 * The server only ever answers with folders below the site root, so what
	 * is chosen is always a real folder of this site; the full path is
	 * composed from the base the server reports.
	 */
	async function openFolders(relative) {
		const picker = state.picker;
		const pathLine = $('extrusion-folder-path');
		const list = $('extrusion-folder-list');
		let payload;
		try {
			payload = await post('extrusionFolders', { path: relative });
		} catch (error) {
			payload = { error: error.message || T.folderFailed };
		}
		if (!picker || state.picker !== picker) {
			return;
		}
		if (!payload || payload.error) {
			list.innerHTML = '<div class="extrusion-modal-empty">'
				+ esc((payload && payload.error) || T.folderFailed) + '</div>';
			return;
		}
		state.picker.base = payload.base;
		state.picker.path = payload.path;
		pathLine.textContent = payload.path === ''
			? T.siteRoot : payload.path;
		let html = '';
		if (payload.parent !== null) {
			html += '<button type="button" class="extrusion-modal-row" data-extrusion-folder="'
				+ esc(payload.parent) + '" data-extrusion-up="1"><b>..</b> <small>'
				+ esc(T.upOneFolder) + '</small></button>';
		}
		(payload.folders || []).forEach((folder) => {
			const next = payload.path === '' ? folder : payload.path + '/' + folder;
			html += '<button type="button" class="extrusion-modal-row" data-extrusion-folder="'
				+ esc(next) + '"><b>' + esc(folder) + '</b></button>';
		});
		if (html === '') {
			html = '<div class="extrusion-modal-empty">' + esc(T.emptyFolder) + '</div>';
		}
		list.innerHTML = html;
	}

	function openFolderPicker(target) {
		state.picker = { target: target, base: '', path: '' };
		$('extrusion-folder-modal').style.display = 'flex';
		openFolders('');
	}

	function closeFolderPicker() {
		state.picker = null;
		$('extrusion-folder-modal').style.display = 'none';
	}

	function chooseFolder() {
		if (!state.picker) {
			return;
		}
		const full = state.picker.base
			+ (state.picker.path === '' ? '' : '/' + state.picker.path);
		const field = document.querySelector('[name="' + state.picker.target + '"]');
		if (field) {
			if (state.picker.target === 'libraries') {
				field.value = (field.value.trim() === ''
					? full : field.value.trim() + '\n' + full);
			} else {
				field.value = full;
			}
		}
		closeFolderPicker();
	}

	/**
	 * Put a select button beside every folder field, so the paths are walked
	 * and chosen rather than typed.
	 */
	function decorateFolderFields() {
		[['admin_path', T.selectFolder], ['site_path', T.selectFolder],
			['libraries', T.addLibrary]].forEach(([name, label]) => {
			const field = document.querySelector('[name="' + name + '"]');
			if (!field) {
				return;
			}
			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'btn btn-sm btn-outline-primary extrusion-folder-select';
			button.setAttribute('data-extrusion-pick', name);
			button.textContent = label;
			button.addEventListener('click', () => openFolderPicker(name));
			field.insertAdjacentElement('afterend', button);
		});
	}

	/**
	 * Wire the page once it stands.
	 */
	document.addEventListener('DOMContentLoaded', () => {
		if (!$('extrusion-page')) {
			return;
		}
		E = window.JCBExtrusion || E;
		T = E.text || {};
		decorateFolderFields();
		// Keep the dedicated repair action with the existing Advanced disclosure.
		const advanced = $('adminForm').elements['show_advanced_options'];
		const toggleRepairOptions = () => {
			$('extrusion-namespace-repair-options').hidden = !advanced || advanced.value !== '1';
		};
		$$('#adminForm [name="show_advanced_options"]').forEach((field) => {
			field.addEventListener('change', toggleRepairOptions);
		});
		toggleRepairOptions();
		// the component name input stands only when everything is created
		// new: with a target selected or detection on, the target answers
		const componentSelect = document.querySelector('[name="component_id"]');
		const componentCode = document.querySelector('[name="component_code"]');
		if (componentSelect && componentCode) {
			const nameRow = componentCode.closest('.control-group') || componentCode;
			const toggleName = () => {
				nameRow.style.display = componentSelect.value === '0' ? '' : 'none';
			};
			componentSelect.addEventListener('change', toggleName);
			toggleName();
		}
		$('extrusion-folder-close').addEventListener('click', closeFolderPicker);
		$('extrusion-folder-choose').addEventListener('click', chooseFolder);
		$('extrusion-folder-list').addEventListener('click', (event) => {
			const folder = event.target.closest('[data-extrusion-folder]');
			if (folder) {
				openFolders(folder.dataset.extrusionFolder);
			}
		});
		$('extrusion-folder-modal').addEventListener('click', (event) => {
			if (event.target === $('extrusion-folder-modal')) {
				closeFolderPicker();
			}
		});
		$('extrusion-harvest-button').addEventListener('click', () => harvest(false));
		$('extrusion-repair-namespaces-button').addEventListener('click', () => harvest(true));
		wireBoard();
		$('extrusion-back-button').addEventListener('click', () => showPane('setup'));
		const importButton = $('extrusion-import-button');
		if (importButton) {
			importButton.addEventListener('click', () => runImport());
		}
		$$('#extrusion-tabs .nav-link').forEach((tab) => {
			tab.addEventListener('click', () => {
				if (!tab.disabled) {
					showPane(tab.dataset.extrusionTab);
				}
			});
		});
		$('extrusion-confirm-import').addEventListener('click', confirmImport);
		$('extrusion-confirm-cancel').addEventListener('click', closeConfirmation);
		$('extrusion-confirm-modal').addEventListener('click', (event) => {
			if (event.target === $('extrusion-confirm-modal')) {
				closeConfirmation();
			}
		});
		$('extrusion-confirm-modal').addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				event.preventDefault();
				closeConfirmation();
			} else if (event.key === 'Tab') {
				const first = $('extrusion-confirm-import');
				const last = $('extrusion-confirm-cancel');
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			}
		});
		$('extrusion-component-select').addEventListener('change', async (event) => {
			invalidateReview();
			state.decisions = {};
			state.selected.clear();
			state.diffs = {};
			if (!await loadCatalogue(parseInt(event.target.value, 10) || 0)) {
				return;
			}
			// the weights were read against the component paired before, so
			// every row is weighed again against the one chosen now
			allCandidates().forEach((candidate) => {
				state.weighing.add(candidate.kind + '|' + candidate.key);
			});
			state.diffs = {};
			scheduleWeighing();
			renderBoard();
		});
		$$('#extrusion-bulk-bar [data-extrusion-bulk]').forEach((button) => {
			button.addEventListener('click', () => bulk(button.dataset.extrusionBulk));
		});
		$('extrusion-filter').addEventListener('input', (event) => applyFilter(event.target.value));
		['type', 'status', 'change'].forEach((filter) => {
			$('extrusion-filter-' + filter).addEventListener('change', () => applyFilter($('extrusion-filter').value));
		});
		$('extrusion-show-ambiguous').addEventListener('click', () => {
			$('extrusion-filter-type').value = 'power';
			$('extrusion-filter-status').value = 'ambiguous';
			$('extrusion-filter-change').value = '';
			$('extrusion-filter').value = '';
			applyFilter('');
			$('extrusion-filter-status').focus();
		});
		$('extrusion-modal-close').addEventListener('click', closeModal);
		$('extrusion-modal-search').addEventListener('input',
			(event) => searchModal(event.target.value));
		$('extrusion-modal-list').addEventListener('click', (event) => {
			const target = event.target.closest('[data-extrusion-target]');
			if (target && state.modal) {
				decide(state.modal, {
					action: 'update',
					target: target.dataset.extrusionTarget,
					label: target.dataset.extrusionLabel
				});
				closeModal();
				renderBoard();
			}
		});
		$('extrusion-modal').addEventListener('click', (event) => {
			if (event.target === $('extrusion-modal')) {
				closeModal();
			}
		});
		// the loading dots of the running pane
		$$('.loading-dots').forEach((dots) => {
			let x = 0;
			setInterval(() => {
				dots.textContent = '.'.repeat((x++ % 8) || 1);
			}, 500);
		});
	});
})();