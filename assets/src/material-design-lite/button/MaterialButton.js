/**
 * @license
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class MaterialButton {

	/**
	 * Class constructor for Button MDL component.
	 * Implements MDL component design pattern defined at:
	 * https://github.com/jasonmayes/mdl-component-design-pattern
	 *
	 * @param {HTMLElement} element The element that will be upgraded.
	 */
	constructor(element) {

		this.element_ = element;

		/**
		 * Store constants in one place so they can be updated easily.
		 *
		 * @enum {string | number}
		 * @private
		 */
		this.Constant_ = {
			// None for now.
		};

		/**
		 * Store strings for class names defined by this component that are used in
		 * JavaScript. This allows us to simply change it in one place should we
		 * decide to modify at a later date.
		 *
		 * @enum {string}
		 * @private
		 */
		this.CssClasses_ = {
			RIPPLE_EFFECT: 'mdl-js-ripple-effect',
			RIPPLE_CONTAINER: 'mdl-button__ripple-container',
			RIPPLE: 'mdl-ripple'
		};

		// Initialize instance.
		this.init();
	}

	/**
	 * Handle blur of element.
	 *
	 * @param {Event} event The event that fired.
	 * @private
	 */
	blurHandler_(event) {
		if (event) {
			this.element_.blur();
		}
	};

	// Public methods.

	/**
	 * Disable button.
	 *
	 * @public
	 */
	disable() {
		this.element_.disabled = true;
	};

	/**
	 * Enable button.
	 *
	 * @public
	 */
	enable() {
		this.element_.disabled = false;
	};

	/**
	 * Initialize element.
	 */
	init() {
		if (this.element_) {
			if (this.element_.classList.contains(this.CssClasses_.RIPPLE_EFFECT)) {
				let rippleContainer = document.createElement('span');
				rippleContainer.classList.add(this.CssClasses_.RIPPLE_CONTAINER);
				this.rippleElement_ = document.createElement('span');
				this.rippleElement_.classList.add(this.CssClasses_.RIPPLE);
				rippleContainer.appendChild(this.rippleElement_);
				this.boundRippleBlurHandler = this.blurHandler_.bind(this);
				this.rippleElement_.addEventListener('mouseup', this.boundRippleBlurHandler);
				this.element_.appendChild(rippleContainer);
			}
			this.boundButtonBlurHandler = this.blurHandler_.bind(this);
			this.element_.addEventListener('mouseup', this.boundButtonBlurHandler);
			this.element_.addEventListener('mouseleave', this.boundButtonBlurHandler);
		}
	};
}

export {MaterialButton}
