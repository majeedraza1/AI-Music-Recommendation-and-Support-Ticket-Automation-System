<template>
	<div class="mdl-textfield-container" :class="{'has-error':hasError}">
		<div :class="rootClasses">
			<div class="mdl-textfield__control">
			<textarea
				v-if="isTextarea"
				:id="id"
				:value="value"
				:required="required"
				:disabled="disabled"
				placeholder=""
				class="mdl-textfield__textarea"
				@input="handleInputEvent($event)"
				@focus="handleFocusEvent($event)"
				@blur="handleBlurEvent($event)"
			></textarea>
				<input
					v-else
					:type="type"
					:id="id"
					:value="value"
					:required="required"
					:disabled="disabled"
					:autocomplete="autocomplete"
					placeholder=""
					class="mdl-textfield__input"
					@input="handleInputEvent($event)"
					@focus="handleFocusEvent($event)"
					@blur="handleBlurEvent($event)"
				>
			</div>
			<div class="mdl-textfield__label" v-html="label"></div>
			<template v-if="hasError">
				<svg width="19" height="19" viewBox="0 0 19 19" version="1.1" xmlns="http://www.w3.org/2000/svg">
					<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
					   transform="translate(-128.000000, -71.000000)">
						<g transform="translate(128.000000, 71.000000)">
							<circle id="Oval-3-Copy" fill="#F44545" cx="9.5" cy="9.5" r="9.5"></circle>
							<path
								d="M10.458,4.074 L10.01,10.976 L8.652,10.976 L8.204,4.074 L10.458,4.074 Z M8.232,13.006 C8.232,12.39 8.722,11.886 9.324,11.886 C9.94,11.886 10.444,12.39 10.444,13.006 C10.444,13.622 9.94,14.112 9.324,14.112 C8.722,14.112 8.232,13.622 8.232,13.006 Z"
								id="!-copy" fill="#FFFFFF"></path>
						</g>
					</g>
				</svg>
			</template>
			<template v-if="hasSuccess">
				<svg width="19" height="19" viewBox="0 0 19 19" version="1.1" xmlns="http://www.w3.org/2000/svg">
					<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
					   transform="translate(-163.000000, -71.000000)">
						<g transform="translate(163.000000, 71.000000)">
							<circle fill="#12FFCD" cx="9.5" cy="9.5" r="9.5"></circle>
							<g id="v's-copy-3" transform="translate(5.000000, 7.000000)" stroke="#FFFFFF"
							   stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
								<polyline points="0 3.16268347 3.30322266 6 9 0"></polyline>
							</g>
						</g>
					</g>
				</svg>
			</template>
		</div>
		<div class="mdl-textfield__error" v-if="hasError" v-html="helptext"></div>
	</div>
</template>

<script>
	export default {
		name: "AnimatedInput",
		props: {
			type: {
				type: String, default: 'text', validator: function (value) {
					return (['text', 'email', 'search', 'password', 'tel', 'url', 'number', 'textarea'].indexOf(value) !== -1)
				},
			},
			id: {type: String},
			value: {type: [Number, String]},
			label: {type: String},
			autocomplete: {type: String},
			required: {type: Boolean, default: false},
			disabled: {type: Boolean, default: false},
			hasError: {type: Boolean, default: false},
			hasSuccess: {type: Boolean, default: false},
			helptext: {type: String},
			helptextValidation: {type: Boolean, default: true}
		},
		data() {
			return {
				isFocus: false
			}
		},
		computed: {
			rootClasses() {
				return {
					'mdl-textfield': true,
					'is-active': (this.hasValue || this.isFocus),
					'is-danger': this.hasError,
				}
			},
			hasValue() {
				return !!this.value.length;
			},
			text() {
				if (this.hasError) {
					return this.helptext;
				}

				return this.label;
			},
			isTextarea() {
				return (this.type === 'textarea');
			}
		},
		methods: {
			handleInputEvent(event) {
				this.$emit('input', event.target.value);
			},
			handleFocusEvent(event) {
				this.isFocus = true;
				this.$emit('focus', event.target.value);
			},
			handleBlurEvent(event) {
				this.$emit('blur', event.target.value);
				this.isFocus = false;
			}
		}
	}
</script>

<style lang="scss">
	.mdl-textfield {
		position: relative;
		padding: 20px 0 10px;
		width: 100%;
		background: #fff;
		margin: 0 !important;
		border-radius: 6px;
		border: 1px solid rgba(#000, 0.2);

		&.is-danger {
			border-color: red;
		}

		&-container {
			margin: 0 auto 20px !important;
		}

		.mdl-textfield__input,
		.mdl-textfield__textarea {
			box-sizing: border-box;
			height: 25px;
			background: none;
			border: none;
			box-shadow: none;
			outline: none;
			padding-right: 50px;
			padding-left: 20px;
			font-size: 16px;
			color: #383e42;
			width: 100%;
		}

		.mdl-textfield__textarea {
			height: auto;
		}

		.mdl-textfield__label {
			font-size: 16px;
			color: #383e42;
			line-height: 1.2;
			position: absolute;
			pointer-events: none;
			left: 20px;
			top: 18px;
			transition: all .3s ease;
		}

		&.is-active {
			.mdl-textfield__label {
				top: 7px;
				color: #bab5bc;
				font-size: 12px;
			}
		}

		&__error {
			color: red;
		}

		> svg {
			position: absolute;
			right: 20px;
			bottom: 18px;
		}
	}
</style>
