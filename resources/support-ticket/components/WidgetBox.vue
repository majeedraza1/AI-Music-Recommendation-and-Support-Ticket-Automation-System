<template>
	<div class="support-ticket-widget-box">
		<div class="support-ticket-widget-box__heading">
			<div class="support-ticket-widget-box__title">{{ title }}</div>
			<div class="support-ticket-widget-box__spacer"></div>
			<icon-container size="medium" hoverable @click="$emit('edit')" v-if="showEditIcon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">
					<title>Edit {{ title }}</title>
					<use xlink:href="#icon-pen"/>
				</svg>
			</icon-container>
		</div>
		<div class="support-ticket-widget-box__content">
			<slot></slot>
		</div>
	</div>
</template>

<script>
import {iconContainer} from 'shapla-vue-components';

export default {
	name: "WidgetBox",
	components: {iconContainer},
	props: {
		title: {type: String},
		showEditIcon: {type: Boolean, default: false},
	}
}
</script>

<style lang="scss">
@import "shapla-css/src/colors";

.support-ticket-widget-box {
	background-color: white;
	border-radius: 4px;
	box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
	margin-bottom: 1.5rem;

	&__heading {
		align-items: center;
		background-color: $primary-alpha;
		border-top-left-radius: 4px;
		border-top-right-radius: 4px;
		color: $primary;
		display: flex;
		padding: 10px;

		svg {
			fill: currentColor;
		}
	}

	&__title {
		font-weight: bold;
	}

	&__spacer {
		flex-grow: 1;
	}

	&__content {
		padding: 10px;
	}
}
</style>