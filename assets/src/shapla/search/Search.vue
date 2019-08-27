<template>
	<div class="shapla-search shapla-product-search has-cat-list">
		<div role="search" class="shapla-search-form">
			<div class="nav-left">

				<div class="nav-search-facade">
					<span class="nav-search-label">{{dropdownLabel}}</span>
					<i class="fa fa-angle-down"></i>
				</div>

				<select name="product_cat" id="product_cat" class="shapla-cat-list" @change="changeCat($event)">
					<option :value="item.value" v-for="item in dropdownItems">{{item.label}}</option>
				</select>
			</div>

			<div class="nav-right">
				<button type="submit" @click="submitSearch"><i class="fa fa-search"></i></button>
			</div>

			<div class="nav-fill">
				<label>
					<span class="screen-reader-text">Search for:</span>
					<input type="search" class="search-field" placeholder="Search …" v-model="search">
				</label>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "Search",
		props: {
			dropdownItems: {type: Array, default: () => []},
		},
		data() {
			return {
				dropdownLabel: 'All',
				dropdownValue: '',
				search: '',
			}
		},
		methods: {
			changeCat(event) {
				let val = event.target.value;
				let el = this.dropdownItems.find(element => {
					return element.value == val;
				});
				if (typeof el === "undefined") {
					this.dropdownLabel = '';
					this.dropdownValue = '';
				} else {
					this.dropdownLabel = el.label;
					this.dropdownValue = el.value;
				}
			},
			submitSearch() {
				this.$emit('search', {cat: this.dropdownValue, query: this.search});
			}
		},
		mounted() {
			let productSearch = this.$el;

			let catList = productSearch.querySelector('.shapla-cat-list');
			if (!catList) return;

			let searchLabel = productSearch.querySelector('.nav-search-label'),
				defaultLabel = searchLabel.getAttribute('data-default'),
				defaultVal = catList.value;


			// console.log(searchLabel, defaultLabel, defaultVal);

			if (defaultVal === '') {
				// searchLabel.textContent = defaultLabel;
			} else {
				// searchLabel.textContent = defaultVal;
			}

			catList.addEventListener('change', function () {
				let selectText = this.value;
				if (selectText === '') {
					// searchLabel.textContent = defaultLabel;
				} else {
					// searchLabel.textContent = selectText;
				}

				productSearch.querySelector('input[type="search"]').focus();
			});
		}
	}
</script>

<style lang="scss">
	.shapla-search {
		max-width: 300px;
		padding: 0;
		position: relative;
		z-index: 1;

		&-form {
			margin: 0;
			padding: 0;
			position: relative;
		}

		input[type="search"] {
			border: 1px solid #dbdbdb;
			border-right: none;
			padding: 8px;
		}

		&.has-cat-list {
			input[type="search"] {
				border-radius: 0;
				border-left-width: 0;

				.shapla-custom-menu-item-contents & {
					border-left-width: 1px;
					border-top-left-radius: 3px;
					border-bottom-left-radius: 3px;
				}
			}
		}

		button[type="submit"] {
			width: 50px;
			text-align: center;
			border-radius: 0 3px 3px 0;
			border: 1px solid #dbdbdb;
			padding: 8px;
		}

		.nav-left {
			position: relative;
			float: left;
			width: auto;

			.shapla-custom-menu-item-contents & {
				display: none;
			}
		}

		.nav-fill {
			position: relative;
			overflow: hidden;
			width: auto;
		}

		.nav-right {
			position: relative;
			float: right;
		}

		.nav-search-facade {
			border: 1px solid #dbdbdb;
			border-radius: 3px 0 0 3px;
			padding: 7px;
			text-transform: capitalize;
			background-color: white;
			color: #363636;

			i {
				margin-left: 5px;
			}
		}

		select.shapla-cat-list {
			padding: 8px;
			position: absolute;
			top: 1px;
			left: 1px;
			opacity: 0;
		}
	}
</style>
