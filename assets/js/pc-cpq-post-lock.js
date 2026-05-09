const PC_CPQ_PostLock = {
	init() {
		this.teardown();
		this.root = document.querySelector( '.js-post-lock-root' );

		if ( ! this.root ) {
			return;
		}

		this.state = this.parseJson( this.root.dataset.postLock || '{}', {} );

		if ( ! this.state?.enabled || ! this.state?.postId ) {
			return;
		}

		this.alert = this.root.querySelector( '.js-post-lock-alert' );
		this.scope = this.root.closest( '.js-post-lock-scope' ) || document.querySelector( '.js-post-lock-scope' ) || document;

		this.updateUi( this.state );

		if ( window.wp?.heartbeat?.interval ) {
			window.wp.heartbeat.interval( 'fast' );
		}

		this.handleHeartbeatSend = ( event, data ) => {
			data.pcCpqPostLock = {
				postId: this.state.postId,
				nonce: this.state.nonce,
				label: this.state.label,
			};
		};

		this.handleHeartbeatTick = ( event, data ) => {
			const payload = data?.pcCpqPostLock?.[ this.state.postId ] || data?.pcCpqPostLock?.[ String( this.state.postId ) ];

			if ( ! payload ) {
				return;
			}

			this.state = {
				...this.state,
				...payload,
			};

			this.updateUi( this.state );
		};

		this.handleBeforeUnload = () => this.releaseLock();

		window.jQuery( document ).on( 'heartbeat-send.pcCpqPostLock', this.handleHeartbeatSend );
		window.jQuery( document ).on( 'heartbeat-tick.pcCpqPostLock', this.handleHeartbeatTick );
		window.addEventListener( 'beforeunload', this.handleBeforeUnload );
	},

	teardown() {
		if ( window.jQuery ) {
			window.jQuery( document ).off( '.pcCpqPostLock' );
		}

		if ( this.handleBeforeUnload ) {
			window.removeEventListener( 'beforeunload', this.handleBeforeUnload );
		}

		this.root = null;
		this.state = null;
		this.alert = null;
		this.scope = null;
		this.handleHeartbeatSend = null;
		this.handleHeartbeatTick = null;
		this.handleBeforeUnload = null;
	},

	updateUi( state ) {
		const isLocked = !! state?.locked;
		const message = String( state?.message || '' );

		if ( this.alert ) {
			this.alert.textContent = message;
			this.alert.classList.toggle( 'd-none', ! message );
		}

		this.scope.querySelectorAll( 'input, select, textarea, button' ).forEach( ( element ) => {
			if ( element.closest( '.js-post-lock-root' ) ) {
				return;
			}

			if ( isLocked ) {
				if ( ! Object.prototype.hasOwnProperty.call( element.dataset, 'lockPrevDisabled' ) ) {
					element.dataset.lockPrevDisabled = element.disabled ? '1' : '0';
				}

				element.disabled = true;
				return;
			}

			if ( Object.prototype.hasOwnProperty.call( element.dataset, 'lockPrevDisabled' ) ) {
				element.disabled = '1' === element.dataset.lockPrevDisabled;
				delete element.dataset.lockPrevDisabled;
			}
		} );
	},

	releaseLock() {
		if (
			! navigator.sendBeacon
			|| ! window.PC_CPQ_ManageConfig?.ajaxurl
			|| ! this.state?.postId
			|| ! this.state?.nonce
		) {
			return;
		}

		const payload = new FormData();
		payload.append( 'action', 'pc_cpq_release_post_lock' );
		payload.append( 'post_id', String( this.state.postId ) );
		payload.append( 'nonce', this.state.nonce );

		navigator.sendBeacon( window.PC_CPQ_ManageConfig.ajaxurl, payload );
	},

	parseJson( value, fallback ) {
		try {
			return value ? JSON.parse( value ) : fallback;
		} catch ( error ) {
			return fallback;
		}
	},
};

export default PC_CPQ_PostLock;
