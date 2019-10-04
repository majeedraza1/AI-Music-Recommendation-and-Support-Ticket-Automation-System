<template>
	<label class="mdl-checkbox mdl-js-checkbox" :class="labelClasses">
		<input type="checkbox" class="mdl-checkbox__input"
			   :checked="shouldBeChecked"
			   :value="value"
			   :disabled="disabled"
			   @change="updateInput"
		>
		<span class="mdl-checkbox__label"><slot>{{ label }}</slot></span>
	</label>
</template>

<script>
	import {MaterialCheckbox} from './MaterialCheckbox.js';
	import {MaterialRipple} from '../ripple/MaterialRipple.js';

	export default {
		name: "mdlCheckbox",
		mounted() {
			new MaterialCheckbox(this.$el);
			new MaterialRipple(this.$el.querySelector('.mdl-js-ripple-effect'));
		},
		model: {
			prop: 'modelValue',
			event: 'change'
		},
		props: {
			modelValue: {default: false},
			value: {default: 'on'},
			trueValue: {default: true},
			falseValue: {default: false},
			disabled: {type: Boolean, default: false},
			ripple: {type: Boolean, default: true},
			label: {type: String, default: ''},
		},
		data() {
			return {}
		},
		computed: {
			shouldBeChecked() {
				if (this.modelValue instanceof Array) {
					return this.modelValue.includes(this.value)
				}
				// Note that `true-value` and `false-value` are camelCase in the JS
				return this.modelValue === this.trueValue
			},
			labelClasses() {
				return {
					'mdl-js-ripple-effect': this.ripple,
					'is-checked': this.shouldBeChecked,
				}
			}
		},
		methods: {
			updateInput(event) {
				let isChecked = event.target.checked;

				if (this.modelValue instanceof Array) {
					let newValue = [...this.modelValue];

					if (isChecked) {
						newValue.push(this.value)
					} else {
						newValue.splice(newValue.indexOf(this.value), 1)
					}

					this.$emit('change', newValue)
				} else {
					this.$emit('change', isChecked ? this.trueValue : this.falseValue)
				}
			}
		}
	}
</script>

<style lang="scss">
	@import "checkbox";
</style>
