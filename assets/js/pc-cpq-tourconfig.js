const elementExists = ( selector ) => !! document.querySelector( selector );

const getElement = ( selector ) => document.querySelector( selector );

const getHref = ( selector ) => {
	const element = getElement( selector );
	return element ? element.getAttribute( 'href' ) : null;
};

const filterSteps = ( steps ) => steps.filter( ( step ) => {
	if ( typeof step.showOn === 'function' && ! step.showOn() ) {
		return false;
	}

	const selector = step.attachTo && step.attachTo.element ? step.attachTo.element : null;
	return ! selector || elementExists( selector );
} );

const createBackButton = ( action, text = 'Back' ) => ( {
	action,
	classes: 'shepherd-button-secondary btn btn-sm',
	text
} );

const createNextButton = ( action, text = 'Next', classes = 'btn btn-sm btn-primary' ) => ( {
	action,
	classes,
	text
} );

const getBaseConfig = ( steps ) => ( {
	useModalOverlay: true,
	defaultStepOptions: {
		cancelIcon: {
			enabled: true
		},
		classes: 'shadow-sm',
		scrollTo: {
			behavior: 'smooth',
			block: 'center'
		},
		canClickTarget: false
	},
	steps: filterSteps( steps )
} );

const expandSettingsNav = () => {
	const navItem = getElement( '#nav-item_settings' );
	if ( ! navItem ) {
		return Promise.resolve();
	}

	navItem.classList.add( 'menu-open' );

	const navLink = navItem.querySelector( ':scope > .nav-link' );
	if ( navLink ) {
		navLink.classList.add( 'active' );
	}

	const tree = navItem.querySelector( ':scope > .nav-treeview' );
	if ( tree ) {
		tree.style.display = 'block';
	}

	return Promise.resolve();
};

const withSettingsNav = ( step ) => ( {
	...step,
	beforeShowPromise() {
		return expandSettingsNav();
	}
} );

const getDashboardSteps = () => ( [
	{
		id: 'dashboard-welcome',
		attachTo: {
			element: '#manage-dashboard',
			on: 'top'
		},
		title: 'Dashboard',
		text: 'This page gives the team a quick pulse on activity and points people into the setup and quoting workflows.'
	},
	{
		id: 'dashboard-onboarding',
		attachTo: {
			element: '#onboarding-checklist .card',
			on: 'top'
		},
		title: 'Onboarding checklist',
		text: 'When a client site is still being set up, this checklist keeps the team oriented around the real settings screens that need attention.',
		showOn() {
			return elementExists( '#onboarding-checklist .card' );
		}
	},
	{
		id: 'dashboard-leads-nav',
		attachTo: {
			element: '#nav-item_leads',
			on: 'right'
		},
		title: 'Leads',
		text: 'Most day-to-day quoting work starts from the leads area.'
	},
	withSettingsNav( {
		id: 'dashboard-settings-nav',
		attachTo: {
			element: '#nav-item_settings',
			on: 'right'
		},
		title: 'Settings',
		text: 'Each client site keeps its own pricing defaults, process data, fees, and templates here.'
	} )
] );

const getLeadListSteps = () => ( [
	{
		id: 'lead-list-nav',
		attachTo: {
			element: '#nav-item_leads',
			on: 'right'
		},
		title: 'Lead list',
		text: 'This is the working queue for quoting. Search, review, and jump into the lead that needs attention next.'
	},
	{
		id: 'lead-list-table',
		attachTo: {
			element: '#lead-list-card',
			on: 'top'
		},
		title: 'Lead pipeline',
		text: 'Each row summarizes quote number, customer context, status, and a direct path into the full lead workspace.'
	},
	{
		id: 'lead-list-add',
		attachTo: {
			element: '#lead-list-add-button',
			on: 'left'
		},
		title: 'Add a new lead',
		text: 'Use this when a fresh quoting request comes in and the team needs to start a new lead record.'
	},
	{
		id: 'lead-list-open',
		attachTo: {
			element: '.js-tour-first-lead-button, .js-tour-first-lead',
			on: 'left'
		},
		title: 'Open a lead',
		text: 'Open a lead to work through contacts, parts, routing, pricing, snapshots, and quote delivery.',
		showOn() {
			return elementExists( '.js-tour-first-lead-button, .js-tour-first-lead' );
		}
	}
] );

const getLeadEditSteps = () => ( [
	{
		id: 'lead-details-card',
		attachTo: {
			element: '#lead-details-card',
			on: 'right'
		},
		title: 'Lead details',
		text: 'This card holds the operational context for the quote: status, service, finishing requirements, applied fees, and internal notes.'
	},
	{
		id: 'lead-contact-card',
		attachTo: {
			element: '#lead-contact-card',
			on: 'right'
		},
		title: 'Contact and customer',
		text: 'Keep the contact information and linked customer record clean here before the quote goes out.'
	},
	{
		id: 'lead-parts-card',
		attachTo: {
			element: '#lead-parts-card',
			on: 'left'
		},
		title: 'Parts',
		text: 'Each part drives the quote. This is where file data, geometry, routing, pricing inputs, and quantities come together.'
	},
	{
		id: 'lead-quote-card',
		attachTo: {
			element: '#lead-quote-card',
			on: 'left'
		},
		title: 'Quote controls',
		text: 'This card is where the team manages quote timing, pricing presentation, snapshot state, and final quote actions.'
	},
	{
		id: 'lead-pricing-mode',
		attachTo: {
			element: '#lead-quote-card [name="pricing_mode"]',
			on: 'left'
		},
		title: 'Pricing mode override',
		text: 'A lead can inherit the client site default pricing mode or override it when this job needs a different quoting approach.',
		showOn() {
			return elementExists( '#lead-quote-card [name="pricing_mode"]' );
		}
	},
	{
		id: 'lead-snapshot',
		attachTo: {
			element: '#quote-details .js-requote',
			on: 'left'
		},
		title: 'Locked snapshot',
		text: 'After a quote is sent, pricing, routing, fees, and terms stay frozen. Requote refreshes that snapshot from the current saved lead when you intentionally want a revised quote.',
		showOn() {
			return elementExists( '#quote-details .js-requote' );
		}
	},
	{
		id: 'lead-prepare-quote',
		attachTo: {
			element: '#lead-prepare-quote-button',
			on: 'left'
		},
		title: 'Prepare the quote',
		text: 'After saving changes, use this button to preview or send the quote with the current saved lead data.'
	}
] );

const getCustomerListSteps = () => ( [
	{
		id: 'customer-list-nav',
		attachTo: {
			element: '#nav-item_customers',
			on: 'right'
		},
		title: 'Customer list',
		text: 'This is the customer directory for the workspace. Use it to find existing customer records, review account details, and open the record that needs updating.'
	},
	{
		id: 'customer-list-table',
		attachTo: {
			element: '#customer-list-card',
			on: 'top'
		},
		title: 'Customer directory',
		text: 'Each row summarizes the customer account, basic contact details, and direct access to the full customer record.'
	},
	{
		id: 'customer-list-add',
		attachTo: {
			element: '#customer-list-add-button',
			on: 'left'
		},
		title: 'Add a new customer',
		text: 'Create a customer here when you need a reusable account record for future leads, contacts, or shipping locations.'
	},
	{
		id: 'customer-list-open',
		attachTo: {
			element: '.js-tour-first-customer-button, .js-tour-first-customer',
			on: 'left'
		},
		title: 'Open a customer',
		text: 'Open a customer to manage account details, billing information, contacts, and shipping addresses.',
		showOn() {
			return elementExists( '.js-tour-first-customer-button, .js-tour-first-customer' );
		}
	}
] );

const getCustomerEditSteps = () => ( [
	{
		id: 'customer-details-card',
		attachTo: {
			element: '#customer-details-card',
			on: 'right'
		},
		title: 'Customer details',
		text: 'This card holds the core account information your team will reuse across quoting, shipping, and customer communication.'
	},
	{
		id: 'customer-billing-card',
		attachTo: {
			element: '#customer-billing-card',
			on: 'right'
		},
		title: 'Billing details',
		text: 'Keep the billing address accurate here so invoices and customer records stay aligned with the account.'
	},
	{
		id: 'customer-contacts-card',
		attachTo: {
			element: '#customer-contacts-card',
			on: 'left'
		},
		title: 'Contacts',
		text: 'Use contacts to store the people tied to this customer so lead follow-up and quoting can point to the right person.'
	},
	{
		id: 'customer-shipping-card',
		attachTo: {
			element: '#customer-shipping-card',
			on: 'left'
		},
		title: 'Shipping addresses',
		text: 'Shipping records help the team maintain destination details that can be reused across multiple jobs for the same customer.'
	},
	{
		id: 'customer-save-card',
		attachTo: {
			element: '#customer-save-card',
			on: 'left'
		},
		title: 'Save changes',
		text: 'Save after updating customer details so leads and future quoting work use the latest account information.'
	}
] );

const createSettingsSteps = ( config ) => ( [
	withSettingsNav( {
		id: config.key + '-nav',
		attachTo: {
			element: config.navSelector,
			on: 'right'
		},
		title: config.navTitle,
		text: config.navText
	} ),
	{
		id: config.key + '-focus',
		attachTo: {
			element: config.focusSelector,
			on: config.focusOn || 'top'
		},
		title: config.focusTitle,
		text: config.focusText
	},
	{
		id: config.key + '-save',
		attachTo: {
			element: config.saveSelector,
			on: 'left'
		},
		title: 'Save changes',
		text: 'Save before leaving the page so future pricing and quoting behavior reflects the latest client-specific values.'
	}
] );

const PAGE_DEFINITIONS = {
	dashboard: {
		key: 'dashboard',
		isCurrent: () => elementExists( '#manage-dashboard' ),
		getUrl: () => getHref( '#nav-item_dashboard a' ) || window.location.href,
		getSteps: getDashboardSteps
	},
	'lead-list': {
		key: 'lead-list',
		isCurrent: () => elementExists( '#lead-list-card' ),
		getUrl: () => getHref( '#nav-item_leads a' ) || window.location.href,
		getSteps: getLeadListSteps
	},
	'lead-edit': {
		key: 'lead-edit',
		isCurrent: () => elementExists( '#edit-lead' ),
		getUrl: () => {
			if ( elementExists( '#edit-lead' ) ) {
				return window.location.href;
			}

			return getHref( '.js-tour-first-lead-button, .js-tour-first-lead' );
		},
		getSteps: getLeadEditSteps
	},
	'customer-list': {
		key: 'customer-list',
		isCurrent: () => elementExists( '#customer-list-card' ),
		getUrl: () => getHref( '#nav-item_customers a' ) || window.location.href,
		getSteps: getCustomerListSteps
	},
	'customer-edit': {
		key: 'customer-edit',
		isCurrent: () => elementExists( '#edit-customer' ),
		getUrl: () => window.location.href,
		getSteps: getCustomerEditSteps
	},
	'settings-price': {
		key: 'settings-price',
		isCurrent: () => elementExists( '#edit-settings-parts' ),
		getUrl: () => getHref( '#nav-item_settings_price a' ),
		getSteps: () => createSettingsSteps( {
			key: 'settings-price',
			navSelector: '#nav-item_settings_price',
			navTitle: 'Price settings',
			navText: 'This page holds the pricing defaults that new work will inherit unless a lead is intentionally overridden.',
			focusSelector: '#edit-settings-parts [name="default_pricing_mode"]',
			focusOn: 'left',
			focusTitle: 'Default pricing mode',
			focusText: 'Choose the quoting model the client site should use by default, then tune the labor and pricing defaults that support it.',
			saveSelector: '#edit-settings-parts .js-edit-settings-parts-submit'
		} )
	},
	'settings-quotes': {
		key: 'settings-quotes',
		isCurrent: () => elementExists( '#edit-settings-quotes' ),
		getUrl: () => getHref( '#nav-item_settings_quotes a' ),
		getSteps: () => createSettingsSteps( {
			key: 'settings-quotes',
			navSelector: '#nav-item_settings_quotes',
			navTitle: 'Quote settings',
			navText: 'This page controls quote numbering, expiration timing, follow-up defaults, and basic access rules around quoting.',
			focusSelector: '#edit-settings-quotes .card-body',
			focusTitle: 'Quote defaults',
			focusText: 'Set how quote numbers start, how long quotes stay valid, and which domains or email addresses are allowed to interact with the flow.',
			saveSelector: '#edit-settings-quotes .js-edit-settings-quotes-submit'
		} )
	},
	'settings-plating': {
		key: 'settings-plating',
		isCurrent: () => elementExists( '#edit-settings-plating' ),
		getUrl: () => getHref( '#nav-item_settings_plating a' ),
		getSteps: () => createSettingsSteps( {
			key: 'settings-plating',
			navSelector: '#nav-item_settings_plating',
			navTitle: 'Plating settings',
			navText: 'This page holds the reference data for metals, plating metals, lines, barrels, and racks.',
			focusSelector: '#edit-settings-plating .nav-pills',
			focusTitle: 'Plating reference tabs',
			focusText: 'Use these tabs to maintain the physical and costing reference data the pricing and routing logic rely on.',
			saveSelector: '#edit-settings-plating .js-edit-settings-plating-submit'
		} )
	},
	'settings-processes': {
		key: 'settings-processes',
		isCurrent: () => elementExists( '#edit-settings-processes' ),
		getUrl: () => getHref( '#nav-item_settings_processes a' ),
		getSteps: () => createSettingsSteps( {
			key: 'settings-processes',
			navSelector: '#nav-item_settings_processes',
			navTitle: 'Process settings',
			navText: 'This page governs the operations library and post-operation ordering used in routing and pricing.',
			focusSelector: '#edit-settings-processes .nav-pills',
			focusTitle: 'Operations and post operations',
			focusText: 'Keep the operation library current here, and maintain the ordered post-operation list the app uses when building routing details.',
			saveSelector: '#edit-settings-processes .js-edit-settings-processes-submit'
		} )
	},
	'settings-fees': {
		key: 'settings-fees',
		isCurrent: () => elementExists( '#edit-settings-fees' ),
		getUrl: () => getHref( '#nav-item_settings_fees a' ),
		getSteps: () => createSettingsSteps( {
			key: 'settings-fees',
			navSelector: '#nav-item_settings_fees',
			navTitle: 'Fee settings',
			navText: 'This page defines the optional fees that can be applied to quotes for this client site.',
			focusSelector: '#edit-settings-fees .js-add-fee',
			focusOn: 'left',
			focusTitle: 'Fee library',
			focusText: 'Add and maintain the fee options that can be turned on during quoting, including defaults and units.',
			saveSelector: '#edit-settings-fees .js-edit-settings-fees-submit'
		} )
	},
	'settings-integrations': {
		key: 'settings-integrations',
		isCurrent: () => elementExists( '#edit-settings-integrations' ),
		getUrl: () => getHref( '#nav-item_settings_integrations a' ),
		getSteps: () => createSettingsSteps( {
			key: 'settings-integrations',
			navSelector: '#nav-item_settings_integrations',
			navTitle: 'Integrations',
			navText: 'This page controls which external systems your workspace uses and where their connection credentials are stored.',
			focusSelector: '#integrations-overview-card',
			focusTitle: 'Integration access',
			focusText: 'Turn integrations on here first. Only enabled integrations reveal their settings panels, which keeps the page focused on the tools your team actually uses.',
			saveSelector: '#edit-settings-integrations .js-edit-settings-integrations-submit'
		} ).concat( filterSteps( [
			{
				id: 'settings-integrations-nutshell',
				attachTo: {
					element: '#integrations-nutshell-card',
					on: 'top'
				},
				title: 'Nutshell connection',
				text: 'When Nutshell is enabled, this panel stores the account label and API credentials used for lead syncing and related CRM actions.',
				showOn() {
					return elementExists( '#integrations-nutshell-card:not(.d-none)' );
				}
			},
			{
				id: 'settings-integrations-empty',
				attachTo: {
					element: '#integrations-empty-state',
					on: 'top'
				},
				title: 'No integrations enabled',
				text: 'If nothing is enabled yet, this message confirms that your workspace is still self-contained until you turn on a connection.',
				showOn() {
					return elementExists( '#integrations-empty-state:not(.d-none)' );
				}
			}
		] ) )
	},
	'settings-templates': {
		key: 'settings-templates',
		isCurrent: () => elementExists( '#edit-settings-templates' ),
		getUrl: () => getHref( '#nav-item_settings_templates a' ),
		getSteps: () => createSettingsSteps( {
			key: 'settings-templates',
			navSelector: '#nav-item_settings_templates',
			navTitle: 'Templates',
			navText: 'This page holds the quote body content and reusable email templates the team sends to customers.',
			focusSelector: '#edit-settings-templates .js-add-email-template, #edit-settings-templates .card',
			focusTitle: 'Quote and email content',
			focusText: 'Keep quote headers, terms, and reusable email templates aligned with how this client wants quoting communication to look.',
			saveSelector: '#edit-settings-templates .js-edit-settings-templates-submit'
		} )
	},
	reports: {
		key: 'reports',
		isCurrent: () => elementExists( '#reports-page' ),
		getUrl: () => getHref( '#nav-item_reports a' ),
		getSteps: () => [
			{
				id: 'reports-overview',
				attachTo: {
					element: '#reports-page',
					on: 'top'
				},
				title: 'Reports',
				text: 'This page helps you review quoting activity over time, spot follow-up load, and export reporting data for the selected date range.'
			},
			{
				id: 'reports-filters',
				attachTo: {
					element: '#reports-filters-card',
					on: 'bottom'
				},
				title: 'Date filters',
				text: 'Start by narrowing the reporting window. The charts, summaries, tables, and CSV exports all follow this date range.'
			},
			{
				id: 'reports-trends',
				attachTo: {
					element: '#reports-activity-card',
					on: 'top'
				},
				title: 'Activity trends',
				text: 'Use these charts to understand how lead creation, quotes sent, follow-ups, and status volume are changing over time.'
			},
			{
				id: 'reports-summary',
				attachTo: {
					element: '#reports-summary-card',
					on: 'top'
				},
				title: 'Status summary',
				text: 'This section gives you a quick count of lead outcomes within the selected range so you can gauge pipeline health at a glance.'
			},
			{
				id: 'reports-follow-up',
				attachTo: {
					element: '#reports-follow-up-card',
					on: 'top'
				},
				title: 'Follow-up queue',
				text: 'Review which leads need attention, how many follow-ups are scheduled, and which records should be opened next.'
			},
			{
				id: 'reports-exports',
				attachTo: {
					element: '#reports-expiring-card .card-tools, #reports-summary-card .card-tools',
					on: 'left'
				},
				title: 'Export CSV',
				text: 'Each reporting module can be exported so you can share the current view or work with the data outside the app.',
				showOn() {
					return elementExists( '#reports-expiring-card .card-tools, #reports-summary-card .card-tools' );
				}
			}
		]
	}
};

const FULL_TOUR_SEQUENCE = [
	'dashboard',
	'lead-list',
	'lead-edit',
	'customer-list',
	'customer-edit',
	'reports',
	'settings-price',
	'settings-quotes',
	'settings-integrations',
	'settings-plating',
	'settings-processes',
	'settings-fees',
	'settings-templates'
];

const getPageDefinition = ( pageKey ) => PAGE_DEFINITIONS[ pageKey ] || null;

const getCurrentTourPageKey = () => {
	const current = Object.values( PAGE_DEFINITIONS ).find( ( page ) => page.isCurrent() );
	return current ? current.key : null;
};

const getPageSteps = ( pageKey ) => {
	const page = getPageDefinition( pageKey );
	return page ? filterSteps( page.getSteps() ) : [];
};

const decoratePageTourSteps = ( steps ) => steps.map( ( step, index ) => ( {
	...step,
	buttons: [
		...( index > 0 ? [ createBackButton( function () {
			return this.back();
		} ) ] : [] ),
		...( index < steps.length - 1 ? [
			createNextButton( function () {
				return this.next();
			}, index === 0 ? 'Start' : 'Next' )
		] : [
			createNextButton( function () {
				return this.complete();
			}, 'Finish', 'btn btn-sm btn-success' )
		] )
	]
} ) );

const decorateFullTourSteps = ( steps, context ) => steps.map( ( step, index ) => {
	const buttons = [];
	const isFirstStep = index === 0;
	const isLastStep = index === steps.length - 1;

	if ( ! isFirstStep ) {
		buttons.push( createBackButton( function () {
			context.setStepIndex( index - 1 );
			return this.back();
		} ) );
	} else if ( context.hasPrevPage ) {
		buttons.push( createBackButton( context.goPrevPage, 'Previous page' ) );
	}

	if ( ! isLastStep ) {
		buttons.push( createNextButton( function () {
			context.setStepIndex( index + 1 );
			return this.next();
		}, context.pageIndex === 0 && index === 0 ? 'Start' : 'Next' ) );
	} else if ( context.hasNextPage ) {
		buttons.push( createNextButton( context.goNextPage, 'Next page' ) );
	} else {
		buttons.push( createNextButton( function () {
			context.finishTour();
			return this.complete();
		}, 'Finish', 'btn btn-sm btn-success' ) );
	}

	return {
		...step,
		buttons
	};
} );

const getPageTarget = ( pageKey, options = {} ) => {
	const page = getPageDefinition( pageKey );
	if ( ! page ) {
		return null;
	}

	let url = page.getUrl();
	if ( ! url && pageKey === 'lead-edit' && options.leadEditUrl ) {
		url = options.leadEditUrl;
	}
	if ( ! url ) {
		return null;
	}

	return {
		pageKey,
		url
	};
};

export function getPC_CPQ_CurrentTourPageKey() {
	return getCurrentTourPageKey();
}

export function getPC_CPQ_FullTourSequence() {
	return [ ...FULL_TOUR_SEQUENCE ];
}

export function getPC_CPQ_FullTourStartTarget() {
	for ( const pageKey of FULL_TOUR_SEQUENCE ) {
		const target = getPageTarget( pageKey );
		if ( target ) {
			return target;
		}
	}

	return null;
}

export function getPC_CPQ_FullTourTarget( pageKey, direction = 'next', options = {} ) {
	const currentIndex = FULL_TOUR_SEQUENCE.indexOf( pageKey );
	if ( currentIndex === -1 ) {
		return null;
	}

	const increment = direction === 'prev' ? -1 : 1;
	for ( let index = currentIndex + increment; index >= 0 && index < FULL_TOUR_SEQUENCE.length; index += increment ) {
		const target = getPageTarget( FULL_TOUR_SEQUENCE[ index ], options );
		if ( target ) {
			return target;
		}
	}

	return null;
}

export function getPC_CPQ_PageTourConfig( pageKey = getCurrentTourPageKey() ) {
	const steps = decoratePageTourSteps( getPageSteps( pageKey ) );
	return getBaseConfig( steps );
}

export function getPC_CPQ_FullTourConfig( options = {} ) {
	const pageKey = options.pageKey || getCurrentTourPageKey();
	const steps = getPageSteps( pageKey );
	const pageIndex = FULL_TOUR_SEQUENCE.indexOf( pageKey );
	const hasPrevPage = !! getPC_CPQ_FullTourTarget( pageKey, 'prev', options );
	const hasNextPage = !! getPC_CPQ_FullTourTarget( pageKey, 'next', options );

	return getBaseConfig( decorateFullTourSteps( steps, {
		pageIndex,
		hasPrevPage,
		hasNextPage,
		setStepIndex: options.setStepIndex || ( () => {} ),
		goPrevPage: options.goPrevPage || ( () => {} ),
		goNextPage: options.goNextPage || ( () => {} ),
		finishTour: options.finishTour || ( () => {} )
	} ) );
}
