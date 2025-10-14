export let PC_CPQ_TourConfig = {
	useModalOverlay: true,
	defaultStepOptions: {
		buttons: [
			{
				action() {
					return this.back();
				},
				classes: 'shepherd-button-secondary btn btn-sm',
				text: 'Back'
			},
			{
				action() {
					return this.next();
				},
				classes: 'btn btn-sm btn-primary',
				text: 'Next'
			}
		],
		cancelIcon: {
			enabled: true
		}
	},
	steps: [
		{
			attachTo: {
				element: "#nav-item_dashboard",
				on: 'right'
			},
			title: "Welcome to your PolyCoatCPQ Dashboard",
			text: "Let us show you around.",
			buttons: [
				{
					action() {
						return this.next();
					},
					classes: 'btn btn-sm btn-primary',
					text: 'Next'
				}
			]
		},
		{
			attachTo: {
				element: "#nav-item_leads",
				on: 'right'
			},
			title: "Leads",
			text: "Manage your leads here."
		},
		{
			attachTo: {
				element: "#nav-item_customers",
				on: 'right'
			},
			title: "Customers",
			text: "Manage your customers here."
		},
		{
			attachTo: {
				element: "#nav-item_settings",
				on: 'right'
			},
			title: "Settings",
			text: "Manage your settings here.",
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'shepherd-button-secondary btn btn-sm',
					text: 'Back'
				},
				{
					action() {
						return this.complete();
					},
					classes: 'btn btn-sm btn-success',
					text: 'Finish'
				}
			]
		}
	]
};